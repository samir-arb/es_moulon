<?php
require_once __DIR__ . '/../../../includes/config.php';

/* ================================
   Helper de redirection SÛRE
================================ */
function redirect_club_structure() {
    // URL absolue vers la section (évite les problèmes de chemin relatifs)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // /es_moulon/BO
    $url    = $scheme . '://' . $host . $base . '/admin.php?section=club_structure';

    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        echo "<script>window.location.href=" . json_encode($url) . ";</script>";
    }
    exit;
}

/* ================================
   Protection
================================ */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    redirect_club_structure();
}

$allowed_roles = ['ROLE_ADMIN', 'ROLE_EDITOR'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    redirect_club_structure();
}

/* ================================
   SUPPRESSION
================================ */
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM club_structure WHERE id_structure = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Poste supprimé avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }
    $stmt->close();
    redirect_club_structure();
}

/* ================================
   AJOUT / MODIFICATION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_node'])) {
    $id_structure    = (!empty($_POST['id_structure']) && ctype_digit($_POST['id_structure'])) ? (int)$_POST['id_structure'] : 0;

    // ⚠️ Les NOMS DOIVENT correspondre à ceux du <form>
    $structure_type  = trim($_POST['type_structure'] ?? '');
    $id_club_function= !empty($_POST['id_club_function']) ? (int)$_POST['id_club_function'] : 0;
    $id_user         = !empty($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
    $parent_id       = (isset($_POST['parent_id']) && $_POST['parent_id'] !== '') ? (int)$_POST['parent_id'] : null;
    $position_number = isset($_POST['position_number']) ? (int)$_POST['position_number'] : 0;
    $is_active       = isset($_POST['is_active']) ? 1 : 0;

    if ($structure_type === '' || $id_club_function === 0 || $id_user === 0) {
        $_SESSION['flash']['danger'] = "Tous les champs obligatoires doivent être remplis.";
        redirect_club_structure();
    }

    if ($id_structure > 0) {
        // UPDATE
        $sql = "UPDATE club_structure 
                   SET type_structure = ?, 
                       id_club_function = ?, 
                       id_user = ?, 
                       parent_id = ?, 
                       position_number = ?, 
                       is_active = ?, 
                       updated_at = NOW(), 
                       updated_by = ?
                 WHERE id_structure = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'siiiiiii',
            $structure_type,
            $id_club_function,
            $id_user,
            $parent_id,
            $position_number,
            $is_active,
            $_SESSION['user_id'],
            $id_structure
        );
        $msg = "Poste modifié avec succès.";
    } else {
        // INSERT
        $sql = "INSERT INTO club_structure 
                    (type_structure, id_club_function, id_user, parent_id, position_number, is_active, created_at, created_by)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'siiiiii',
            $structure_type,
            $id_club_function,
            $id_user,
            $parent_id,
            $position_number,
            $is_active,
            $_SESSION['user_id']
        );
        $msg = "Poste ajouté avec succès.";
    }

    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = $msg;
    } else {
        $_SESSION['flash']['danger'] = "Erreur SQL : " . $stmt->error;
    }
    $stmt->close();

    redirect_club_structure();
}

/* ================================
   RÉCUP DES DONNÉES POUR AFFICHAGE
   (à partir d'ici, on peut produire du HTML)
================================ */

// Pour le formulaire d’édition
$edit_node = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM club_structure WHERE id_structure = ?");
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $edit_node = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fonctions & Users pour les <select>
$functions = [];
$resF = $conn->query("SELECT id_club_function, function_name FROM club_functions ORDER BY function_name ASC");
while ($row = $resF->fetch_assoc()) $functions[] = $row;

$users = [];
$resU = $conn->query("SELECT id_user, first_name, name FROM users ORDER BY name ASC, first_name ASC");
while ($row = $resU->fetch_assoc()) $users[] = $row;

// Liste organigramme
$sql = "
    SELECT 
        cs.*,
        u.first_name, u.name AS last_name,
        cf.function_name,
        p.function_name AS parent_function,
        CONCAT(up.first_name, ' ', up.name) AS parent_user
    FROM club_structure cs
    LEFT JOIN users u            ON cs.id_user = u.id_user
    LEFT JOIN club_functions cf  ON cs.id_club_function = cf.id_club_function
    LEFT JOIN club_structure cs2 ON cs.parent_id = cs2.id_structure
    LEFT JOIN users up           ON cs2.id_user = up.id_user
    LEFT JOIN club_functions p   ON cs2.id_club_function = p.id_club_function
    ORDER BY cs.type_structure, cs.position_number ASC, cf.function_name ASC
";
$res = $conn->query($sql);
$structures = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$total  = count($structures);
$actifs = count(array_filter($structures, fn($s) => (int)$s['is_active'] === 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de l’Organigramme - ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_back.css/club_structure.css') ?>">
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>🏗️ Organigramme du Club</h1>
            <p style="color:#6b7280;margin-top:4px;">
                <a href="dashboard.php" style="color:#1e40af;text-decoration:none;">← Retour au dashboard</a>
            </p>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo({top:0, behavior:'smooth'});">
            ➕ Ajouter un poste
        </button>
    </div>

    <?php
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
        }
        unset($_SESSION['flash']);
    }
    ?>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#10b981;"><?= $actifs ?></div>
            <div class="stat-label">Actifs</div>
        </div>
    </div>

    <!-- FORMULAIRE -->
    <div class="card" id="formSection" style="<?= $edit_node ? '' : 'display:none;' ?>">
        <h2><?= $edit_node ? '✏️ Modifier un poste' : '➕ Nouveau poste' ?></h2>

        <form method="POST" action="admin.php?section=club_structure">
            <input type="hidden" name="save_node" value="1">
            <?php if ($edit_node): ?>
                <input type="hidden" name="id_structure" value="<?= (int)$edit_node['id_structure'] ?>">
            <?php endif; ?>

            <div class="form-grid">

                <!-- TYPE DE STRUCTURE -->
                <div class="form-group">
                    <label for="type_structure">Type de structure *</label>
                    <select id="type_structure" name="type_structure" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="Administratif" <?= ($edit_node && $edit_node['type_structure'] === 'Administratif') ? 'selected' : '' ?>>Administrative</option>
                        <option value="Sportif" <?= ($edit_node && $edit_node['type_structure'] === 'Sportif') ? 'selected' : '' ?>>Sportive</option>
                    </select>
                </div>

                <!-- FONCTION -->
                <div class="form-group">
                    <label for="id_club_function">Fonction *</label>
                    <select id="id_club_function" name="id_club_function" required>
                        <option value="">-- Sélectionner une fonction --</option>
                        <?php foreach ($functions as $f): ?>
                            <option value="<?= $f['id_club_function'] ?>"
                                <?= ($edit_node && (int)$edit_node['id_club_function'] === (int)$f['id_club_function']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['function_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- MEMBRE -->
                <div class="form-group">
                    <label for="id_user">Membre *</label>
                    <select id="id_user" name="id_user" required>
                        <option value="">-- Sélectionner un membre --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id_user'] ?>"
                                <?= ($edit_node && (int)$edit_node['id_user'] === (int)$u['id_user']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['first_name'] . ' ' . $u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- SUPÉRIEUR HIÉRARCHIQUE -->
                <div class="form-group">
                    <label for="parent_id">Supérieur hiérarchique</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">Aucun (top niveau)</option>

                        <?php
                        // Détection du type de structure courant (édition ou ajout)
                        $typeActuel = $edit_node['type_structure'] ?? ($_POST['type_structure'] ?? null);
                        foreach ($structures as $s):
                            if ($typeActuel && $typeActuel !== $s['type_structure']) continue;
                        ?>
                            <option value="<?= $s['id_structure'] ?>"
                                <?= ($edit_node && $edit_node['parent_id'] == $s['id_structure']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(($s['function_name'] ?? 'Poste') . ' - ' . ($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ORDRE D’AFFICHAGE -->
                <div class="form-group">
                    <label for="position_number">Ordre d’affichage</label>
                    <input type="number" id="position_number" name="position_number" min="0"
                        value="<?= $edit_node['position_number'] ?? 0 ?>">
                </div>

                <!-- VISIBILITÉ -->
                <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:26px;">
                    <input type="checkbox" id="is_active" name="is_active"
                        <?= (!isset($edit_node) || (int)$edit_node['is_active'] === 1) ? 'checked' : '' ?>>
                    <label for="is_active">Visible sur le site</label>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="form-actions">
                <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                <a href="admin.php?section=club_structure" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
       
    </div>

    <!-- LISTE -->
    <div class="card">
        <h2>Organigramme (<?= $total ?>)</h2>

        <?php if (empty($structures)): ?>
            <p class="no-data">Aucun poste enregistré.</p>
        <?php else: ?>
            <table class="structure-table">
                <thead>
                    <tr>
                        <th>Ordre</th>
                        <th>Type</th>
                        <th>Fonction</th>
                        <th>Membre</th>
                        <th>Rattaché à</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($structures as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['position_number'] ?? 0) ?></td>
                        <td>
                            <span class="badge-type <?= ($s['type_structure']==='Sportif') ? 'badge-sport' : 'badge-admin' ?>">
                                <?= htmlspecialchars($s['type_structure']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($s['function_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?: '-') ?></td>
                        <td>
                            <?= htmlspecialchars(
                                ($s['parent_function'] ? $s['parent_function'] : '—')
                                . (($s['parent_user']) ? (' · ' . $s['parent_user']) : '')
                            ) ?>
                        </td>
                        <td><?= ((int)$s['is_active'] === 1) ? '✅ Actif' : '🚫 Inactif' ?></td>
                        <td class="actions">
                            <a class="btn btn-warning" href="admin.php?section=club_structure&edit=<?= (int)$s['id_structure'] ?>">✏️</a>
                            <a class="btn btn-danger"  href="admin.php?section=club_structure&delete=<?= (int)$s['id_structure'] ?>" onclick="return confirm('Supprimer ce poste ?');">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
