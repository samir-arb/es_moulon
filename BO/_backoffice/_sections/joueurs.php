<?php
require_once __DIR__ . '/../../../includes/config.php';

// --- Sécurité ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: ../auth/login.php');
    exit;
}

$allowed_roles = ['ROLE_ADMIN', 'ROLE_SPORT_MANAGER'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header('Location: dashboard.php');
    exit;
}

$user_role = $_SESSION['role'];
$filter_team = isset($_GET['team']) && is_numeric($_GET['team']) ? (int)$_GET['team'] : 0;

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $user_role === 'ROLE_ADMIN') {
    $id = (int)$_GET['delete'];
    $sql = "DELETE FROM users_club_functions WHERE id_user_club_function = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Joueur supprimé avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }
    $stmt->close();
    header('Location: joueurs.php' . ($filter_team ? '?team='.$filter_team : ''));
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_player'])) {
    $id_link = isset($_POST['id_user_club_function']) ? (int)$_POST['id_user_club_function'] : 0;
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $birth_date   = $_POST['birth_date'];
    $id_team      = (int)$_POST['id_team'];
    $position     = trim($_POST['position']);
    $jersey_number = !empty($_POST['jersey_number']) ? (int)$_POST['jersey_number'] : null;

    if (empty($first_name) || empty($last_name) || empty($birth_date) || $id_team === 0) {
        $_SESSION['flash']['danger'] = "Les champs obligatoires doivent être remplis.";
    } else {
        if ($id_link > 0) {
            // --- MODIFICATION ---
            $sql = "UPDATE users u
                    INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
                    SET u.first_name = ?, u.name = ?, u.birth_date = ?, 
                        ucf.id_team = ?, ucf.position = ?, ucf.jersey_number = ?
                    WHERE ucf.id_user_club_function = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssissi', $first_name, $last_name, $birth_date, $id_team, $position, $jersey_number, $id_link);
            $message = "Joueur modifié avec succès.";
        } else {
            // --- AJOUT ---
            // 1. Création du user
            $sqlUser = "INSERT INTO users (first_name, name, birth_date) VALUES (?, ?, ?)";
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->bind_param('sss', $first_name, $last_name, $birth_date);
            $stmtUser->execute();
            $id_user = $stmtUser->insert_id;
            $stmtUser->close();

            // 2. Lien joueur
            $id_club_function = 1; // ⚠️ ID exact de "Joueur" dans ta table club_functions
            $id_season = 2;        // ⚠️ ID de la saison active 2025/2026
            $sqlLink = "INSERT INTO users_club_functions 
                        (id_user, id_team, id_club_function, id_season, position, jersey_number, start_date) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sqlLink);
            $stmt->bind_param('iiiisi', $id_user, $id_team, $id_club_function, $id_season, $position, $jersey_number);
            $message = "Joueur ajouté avec succès.";
        }

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $message;
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement.";
        }
        $stmt->close();
    }
    header('Location: joueurs.php' . ($filter_team ? '?team='.$filter_team : ''));
    exit;
}

// --- RÉCUPÉRATION POUR MODIF ---
$edit_player = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "SELECT ucf.*, u.first_name, u.name, u.birth_date, t.name AS team_name, t.level AS team_level
            FROM users_club_functions ucf
            INNER JOIN users u ON ucf.id_user = u.id_user
            INNER JOIN teams t ON ucf.id_team = t.id_team
            WHERE ucf.id_user_club_function = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_player = $result->fetch_assoc();
    $stmt->close();
}

// --- LISTE DES JOUEURS ---
if ($filter_team > 0) {
    $sql = "SELECT ucf.id_user_club_function, u.first_name, u.name, u.birth_date,
                   ucf.jersey_number, ucf.position, t.name AS team_name, t.level AS team_level
            FROM users_club_functions ucf
            INNER JOIN users u ON ucf.id_user = u.id_user
            INNER JOIN teams t ON ucf.id_team = t.id_team
            INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
            WHERE ucf.id_team = ? AND cf.function_name = 'Joueur'
            ORDER BY ucf.jersey_number, u.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $filter_team);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT ucf.id_user_club_function, u.first_name, u.name, u.birth_date,
                   ucf.jersey_number, ucf.position, t.name AS team_name, t.level AS team_level
            FROM users_club_functions ucf
            INNER JOIN users u ON ucf.id_user = u.id_user
            INNER JOIN teams t ON ucf.id_team = t.id_team
            INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
            WHERE cf.function_name = 'Joueur'
            ORDER BY t.name, ucf.jersey_number, u.name";
    $result = $conn->query($sql);
}

$players = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
}

// --- Équipes ---
$teams_list = [];
$teams_result = $conn->query("SELECT id_team, name, level FROM teams ORDER BY level, name");
if ($teams_result) {
    while ($row = $teams_result->fetch_assoc()) {
        $teams_list[] = $row;
    }
}

// Postes dispo
$positions = ['Gardien', 'Défenseur', 'Milieu', 'Attaquant'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Joueurs et Licenciés - ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_back.css/joueurs.css') ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>👥 Gestion des Joueurs et Licenciés</h1>
                <p><a href="dashboard.php">← Retour au dashboard</a></p>
            </div>
            <?php if (!$edit_player): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouveaux joueurs
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
        <div class="card" id="formSection" style="<?= $edit_player ? '' : 'display:none;' ?>">
            <h2><?= $edit_player ? '✏️ Modifier le joueur' : '➕ Nouveau joueur' ?></h2>

            <form method="POST" action="joueurs.php
            <?= $filter_team ? '?team=' . $filter_team : '' ?>">   
                <?php if ($edit_player): ?>
                    <input type="hidden" name="id_user_club_function" value="<?= $edit_player['id_user_club_function'] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">Prénom *</label>
                        <input type="text" id="first_name" name="first_name" value="<?= $edit_player ? htmlspecialchars($edit_player['first_name']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Nom *</label>
                        <input type="text" id="last_name" name="last_name" value="<?= $edit_player ? htmlspecialchars($edit_player['name']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="birth_date">Date de naissance *</label>
                        <input type="date" id="birth_date" name="birth_date" value="<?= $edit_player ? $edit_player['birth_date'] : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="id_team">Équipe *</label>
                        <select id="id_team" name="id_team" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($teams_list as $team): ?>
                                <option value="<?= $team['id_team'] ?>" <?= ($edit_player && $edit_player['id_team'] == $team['id_team']) || ($filter_team == $team['id_team']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team['level'] . ' - ' . $team['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!$edit_player || $edit_player['position'] !== null): ?>
                    <div class="form-group">
                        <label for="position">Poste</label>
                        <select id="position" name="position">
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?= $pos ?>" <?= ($edit_player && $edit_player['position'] === $pos) ? 'selected' : '' ?>><?= $pos ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jersey_number">Numéro de maillot</label>
                        <input type="number" id="jersey_number" name="jersey_number" min="1" max="99" value="<?= $edit_player ? $edit_player['jersey_number'] : '' ?>">
                    </div>
                </div>
                 <?php endif; ?>
                <div class="form-actions">
                    <button type="submit" name="save_player" class="btn btn-success">💾 Enregistrer</button>
                    <a href="joueurs.php<?= $filter_team ? '?team=' . $filter_team : '' ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- LISTE -->
        <div class="card">
            <h2>Liste des joueurs (<?= count($players) ?>)</h2>
            <?php if (empty($players)): ?>
                <p>Aucun joueur pour le moment.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Nom complet</th>
                                <th>Date de naissance</th>
                                <th>Âge</th>
                                <th>Équipe</th>
                                <th>Poste</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($players as $player): 
                                $birth = new DateTime($player['birth_date']);
                                $today = new DateTime();
                                $age = $today->diff($birth)->y;
                            ?>
                                <tr>
                                    <td><?= $player['jersey_number'] ? $player['jersey_number'] : '-' ?></td>
                                    <td><?= htmlspecialchars($player['first_name'] . ' ' . $player['name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($player['birth_date'])) ?></td>
                                    <td><?= $age ?> ans</td>
                                    <td><?= htmlspecialchars($player['team_level'] . ' - ' . $player['team_name']) ?></td>
                                    <td><?= $player['position'] ? htmlspecialchars($player['position']) : '-' ?></td>
                                    <td>
                                        <a href="joueurs.php?edit=<?= $player['id_user_club_function'] ?>" class="btn btn-warning">✏️</a>
                                        <?php if ($user_role === 'ROLE_ADMIN'): ?>
                                            <a href="joueurs.php?delete=<?= $player['id_user_club_function'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce joueur ?')">🗑️</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>