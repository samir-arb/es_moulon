<?php

// Protection
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: login.php');
    exit;
}

$user_role = $_SESSION['role'] ?? '';
$allowed_roles = ['ROLE_ADMIN', 'ROLE_EDITOR'];
if (!in_array($user_role, $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Accès refusé.";
    header('Location: dashboard.php');
    exit;
}

// Initialiser edit
$edit = null;

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id_user=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Arbitre supprimé.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }
    $stmt->close();

    header("Location: arbitres.php");
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "
        SELECT u.id_user, u.first_name, u.name, m.file_path AS photo
        FROM users u
        INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
        INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
        LEFT JOIN medias m ON u.id_user = m.id_user
        WHERE cf.function_name = 'Arbitre' AND u.id_user = ?
        ORDER BY u.name
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit = $res->fetch_assoc();
    $stmt->close();
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_arbitre'])) {
    $id = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
    $prenom = trim($_POST['prenom']);
    $nom    = trim($_POST['nom']);
    $photo  = trim($_POST['photo']);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE users SET first_name=?, name=?, photo=? WHERE id_user=?");
        $stmt->bind_param('sssi', $prenom, $nom, $photo, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash']['success'] = "Arbitre modifié.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (first_name, name, photo, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('sss', $prenom, $nom, $photo);
        $stmt->execute();
        $new_user_id = $stmt->insert_id;
        $stmt->close();

        // Associer fonction arbitre
        $stmt = $conn->prepare("INSERT INTO users_club_functions (id_user, id_club_function) 
            VALUES (?, (SELECT id_club_function FROM club_functions WHERE function_name='Arbitre'))");
        $stmt->bind_param('i', $new_user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash']['success'] = "Nouvel arbitre ajouté.";
    }

    header("Location: arbitres.php");
    exit;
}

// --- LISTE DES ARBITRES ---
$sql = "
    SELECT u.id_user, u.first_name, u.name, m.file_path AS photo
    FROM users u
    INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
    INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
    LEFT JOIN medias m ON u.id_user = m.id_user
    WHERE cf.function_name = 'Arbitre'
    ORDER BY u.name

";
$res = $conn->query($sql);
$arbitres = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="stylesheet" href="<?= asset('_back.css/arbitres.css') ?>">
    <meta charset="utf-8">
    <title>Gestion Partenaires</title>

<body>
    <h1>👨‍⚖️ Gestion des Partenaires</h1>

    <?php if (isset($_SESSION['flash'])): ?>
        <?php foreach ($_SESSION['flash'] as $t => $m): ?>
            <div class="alert alert-<?= $t ?>"><?= htmlspecialchars($m) ?></div>
        <?php endforeach;
        unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- FORMULAIRE -->
    <div class="card">
        <h2><?= $edit ? "✏️ Modifier un Partenaire" : "➕ Ajouter un Partenaire" ?></h2>
        <form method="post" action="partenaires.php">
            <?php if ($edit): ?>
                <input type="hidden" name="id_user" value="<?= $edit['id_user'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" value="<?= $edit ? htmlspecialchars($edit['first_name']) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="photo">Photo (URL)</label>
                <input type="text" id="photo" name="photo" value="<?= $edit ? htmlspecialchars($edit['photo']) : '' ?>">
            </div>

            <div class="form-actions">
                <button type="submit" name="save_arbitre" class="btn btn-success">💾 Enregistrer</button>
                <?php if ($edit): ?>
                    <a href="arbitres.php" class="btn btn-warning">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- LISTE -->
    <div class="card">
        <h2>Liste des Arbitres (<?= count($arbitres) ?>)</h2>
        <?php if (empty($arbitres)): ?>
            <p>Aucun arbitre pour le moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Photo</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arbitres as $a): ?>
                        <tr>
                            <td><?= $a['id_user'] ?></td>
                            <td><?= htmlspecialchars($a['name']) ?></td>
                            <td><?= htmlspecialchars($a['first_name']) ?></td>
                            <td><?php if ($a['photo']): ?><img src="<?= htmlspecialchars($a['photo']) ?>" width="60"><?php endif; ?></td>
                            <td>
                                <a href="arbitres.php?edit=<?= $a['id_user'] ?>" class="btn btn-warning">✏️ Modifier</a>
                                <a href="arbitres.php?delete=<?= $a['id_user'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer cet arbitre ?')">🗑️ Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>
