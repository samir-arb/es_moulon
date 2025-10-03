<?php
require_once __DIR__ . '/../../../includes/config.php';

// Protection de la page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: login.php');
    exit;
}

// Vérification des permissions
$allowed_roles = ['ROLE_ADMIN', 'ROLE_SPORT_MANAGER'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header('Location: dashboard.php');
    exit;
}

$user_role = $_SESSION['role'];

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $user_role === 'ROLE_ADMIN') {
    $id = (int)$_GET['delete'];

    // Vérifier s'il y a des joueurs liés à l'équipe
    $check = $conn->prepare("SELECT COUNT(*) as nb FROM users_club_functions WHERE id_team = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    $check->close();

    if ($res['nb'] > 0) {
        $_SESSION['flash']['warning'] = "Impossible de supprimer : cette équipe contient " . $res['nb'] . " joueur(s).";
    } else {
        $sql = "DELETE FROM teams WHERE id_team = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = "Équipe supprimée avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
        }
        $stmt->close();
    }

    header('Location: equipes.php');
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_team'])) {
    $id = isset($_POST['id_team']) ? (int)$_POST['id_team'] : 0;
    $name = trim($_POST['team_name']);
    $id_category = (int)$_POST['id_category'];
    $level = trim($_POST['level']);

    if (empty($name) || $id_category === 0) {
        $_SESSION['flash']['danger'] = "Le nom et la catégorie sont obligatoires.";
    } else {
        if ($id > 0) {
            $sql = "UPDATE teams SET name = ?, id_category = ?, level = ? WHERE id_team = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sisi', $name, $id_category, $level, $id);
            $message = "Équipe modifiée avec succès.";
        } else {
            $sql = "INSERT INTO teams (name, id_category, level) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sis', $name, $id_category, $level);
            $message = "Équipe ajoutée avec succès.";
        }

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $message;
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement.";
        }
        $stmt->close();
    }

    header('Location: equipes.php');
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
$edit_team = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "SELECT * FROM teams WHERE id_team = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_team = $result->fetch_assoc();
    $stmt->close();
}

// --- LISTE DES ÉQUIPES ---
$sql = "
    SELECT t.id_team, t.name, t.level, c.name AS category, COUNT(ucf.id_user) as nb_players
    FROM teams t
    JOIN categories c ON t.id_category = c.id_category
    LEFT JOIN users_club_functions ucf ON ucf.id_team = t.id_team
    GROUP BY t.id_team
    ORDER BY c.name, t.name
";
$result = $conn->query($sql);
$teams = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }
}

// Récupérer les catégories existantes
$cat_result = $conn->query("SELECT id_category, name FROM categories ORDER BY name");
$categories = [];
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Équipes - ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_back.css/equipes.css') ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>👥 Gestion des Équipes</h1>
                <p><a href="dashboard.php">← Retour au dashboard</a></p>
            </div>
            <?php if (!$edit_team): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouvelle équipe
                </button>
            <?php endif; ?>
        </div>

        <?php
        if (isset($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $type => $message) {
                echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
            }
            unset($_SESSION['flash']);
        }
        ?>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit_team ? '' : 'display:none;' ?>">
            <h2><?= $edit_team ? '✏️ Modifier l\'équipe' : '➕ Nouvelle équipe' ?></h2>
            
            <form method="POST" action="equipes.php">
                <?php if ($edit_team): ?>
               <input type="hidden" name="id_team" value="<?= $edit_team['id_team'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="team_name">Nom de l'équipe *</label>
                    <input type="text" id="team_name" name="team_name" value="<?= $edit_team ? htmlspecialchars($edit_team['name']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="id_category">Catégorie *</label>
                    <select id="id_category" name="id_category" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id_category'] ?>" <?= ($edit_team && $edit_team['id_category'] == $cat['id_category']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="level">Niveau</label>
                    <input type="text" id="level" name="level" value="<?= $edit_team ? htmlspecialchars($edit_team['level']) : '' ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_team" class="btn btn-success">💾 Enregistrer</button>
                    <a href="equipes.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- LISTE -->
        <div class="card">
            <h2>Nos équipes (<?= count($teams) ?>)</h2>
            
            <?php if (empty($teams)): ?>
                <p>Aucune équipe pour le moment.</p>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($teams as $team): ?>
                        <div class="team-card">
                            <div class="team-header">
                                <div class="team-name"><?= htmlspecialchars($team['name']) ?></div>
                                <div class="team-category"><?= htmlspecialchars($team['category']) ?></div>
                            </div>
                            
                            <div class="team-stats">
                                <strong><?= $team['nb_players'] ?></strong> joueur(s)
                            </div>
                            
                            <div class="actions">
                                <a href="equipes.php?edit=<?= $team['id_team'] ?>" class="btn btn-warning">✏️ Modifier</a>
                                <a href="joueurs.php?team=<?= $team['id_team'] ?>" class="btn btn-primary">👀 Voir joueurs</a>
                                <?php if ($user_role === 'ROLE_ADMIN'): ?>
                                    <a href="equipes.php?delete=<?= $team['id_team'] ?>" class="btn btn-danger" onclick="return confirm('Confirmer la suppression de <?= htmlspecialchars($team['name']) ?> ?')">🗑️ Supprimer</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
