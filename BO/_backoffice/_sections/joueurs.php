<?php

file_put_contents('C:/laragon/www/es_moulon/test.txt', date('H:i:s') . ' - Passage dans joueurs.php' . PHP_EOL, FILE_APPEND);
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

// 🛡️ GÉNÉRATION TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
    header('Location: /es_moulon/BO/admin.php?section=joueurs' . ($filter_team ? '&team=' . $filter_team : ''));
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_player'])) {
    
    // 🛡️ VÉRIFICATION TOKEN CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash']['danger'] = "❌ Token CSRF invalide. Tentative d'attaque détectée !";
        header('Location: /es_moulon/BO/admin.php?section=joueurs' . ($filter_team ? '&team=' . $filter_team : ''));
        exit;
    }
    
    $id_link = isset($_POST['id_user_club_function']) ? (int)$_POST['id_user_club_function'] : 0;
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $birth_date = $_POST['birth_date'];
    $id_team = (int)$_POST['id_team'];
    $position = trim($_POST['position']);
    $jersey_number = !empty($_POST['jersey_number']) ? (int)$_POST['jersey_number'] : null;

    if (empty($first_name) || empty($last_name) || empty($birth_date) || $id_team === 0) {
        $_SESSION['flash']['danger'] = "Les champs obligatoires doivent être remplis.";
    } else {
        if ($id_link > 0) {
            // MODIFICATION
            $sql = "UPDATE users u
                    INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
                    SET u.first_name = ?, u.name = ?, u.birth_date = ?, 
                        ucf.id_team = ?, ucf.position = ?, ucf.jersey_number = ?
                    WHERE ucf.id_user_club_function = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssissi', $first_name, $last_name, $birth_date, $id_team, $position, $jersey_number, $id_link);
            $message = "Joueur modifié avec succès.";

            if ($stmt->execute()) {
                $_SESSION['flash']['success'] = $message;
            } else {
                $_SESSION['flash']['danger'] = "Erreur : " . $stmt->error;
            }
            $stmt->close();
        } else {
            // AJOUT
            $sqlUser = "INSERT INTO users (first_name, name, birth_date, id_role, has_backoffice_access) VALUES (?, ?, ?, ?, ?)";
            $stmtUser = $conn->prepare($sqlUser);
            $id_role = 5; // ROLE_LICENSED
            $stmtUser->bind_param('sssii', $first_name, $last_name, $birth_date, $id_role, $has_backoffice_access);

            if ($stmtUser->execute()) {
                $id_user = $stmtUser->insert_id;
                $stmtUser->close();

                // Récupérer la saison active
                $id_club_function = 1;
                $season_query = $conn->query("SELECT id_season FROM seasons WHERE is_active = 1 LIMIT 1");
                $season_row = $season_query->fetch_assoc();
                $id_season = $season_row['id_season'];

                $sqlLink = "INSERT INTO users_club_functions (id_user, id_team, id_club_function, id_season, position, jersey_number, start_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sqlLink);
                $stmt->bind_param('iiiisi', $id_user, $id_team, $id_club_function, $id_season, $position, $jersey_number);

                if ($stmt->execute()) {
                    $_SESSION['flash']['success'] = "Joueur ajouté avec succès.";
                } else {
                    $_SESSION['flash']['danger'] = "Erreur lien : " . $stmt->error;
                }
                $stmt->close();
            } else {
                $_SESSION['flash']['danger'] = "Erreur user : " . $stmtUser->error;
            }
        }
    }

    header('Location: /es_moulon/BO/admin.php?section=joueurs' . ($filter_team ? '&team=' . $filter_team : ''));
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

    <div class="container">
        <div class="header">
            <div>
                <h1>👥 Gestion des Joueurs et Licenciés</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <?php if (!$edit_player): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouveau joueur
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

            <form method="POST" action="/es_moulon/BO/admin.php?section=joueurs">
                
                <!-- 🛡️ CHAMP CSRF CACHÉ -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
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
                <a href="/es_moulon/BO/admin.php?section=joueurs" class="btn btn-secondary">Annuler</a>
            </div>
            </form>
        </div>
        
        <!-- LISTE PAR ÉQUIPES -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: #1f2937;">Joueurs par équipe</h2>
                
            </div>

            <?php
            // Grouper les joueurs par équipe
            $players_by_team = [];
            foreach ($players as $player) {
                $team_key = $player['team_name'] . ' (' . $player['team_level'] . ')';
                if (!isset($players_by_team[$team_key])) {
                    $players_by_team[$team_key] = [
                        'id_team' => $player['id_team'] ?? 0,
                        'players' => []
                    ];
                }
                $players_by_team[$team_key]['players'][] = $player;
            }
            ?>

            <?php if (empty($players_by_team)): ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">Aucun joueur pour le moment.</p>
            <?php else: ?>
                <div style="display: grid; gap: 20px;">
                    <?php foreach ($players_by_team as $team_name => $team_data): ?>
                        <div style="background: #f9fafb; border-left: 4px solid #1e40af; border-radius: 12px; padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h3 style="color: #1f2937; font-size: 1.2rem;">
                                    <?= htmlspecialchars($team_name) ?>
                                    <span style="background: #1e40af; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; margin-left: 12px;">
                                        <?= count($team_data['players']) ?> joueur<?= count($team_data['players']) > 1 ? 's' : '' ?>
                                    </span>
                                </h3>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
                                <?php foreach ($team_data['players'] as $player): 
                                    $birth = new DateTime($player['birth_date']);
                                    $today = new DateTime();
                                    $age = $today->diff($birth)->y;
                                ?>
                                    <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <?php if ($player['jersey_number']): ?>
                                                <div style="background: #1e40af; color: white; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0;">
                                                    <?= $player['jersey_number'] ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                                                    <?= htmlspecialchars($player['first_name'] . ' ' . $player['name']) ?>
                                                </div>
                                                <div style="font-size: 0.85rem; color: #6b7280;">
                                                    <?= $age ?> ans
                                                    <?php if ($player['position']): ?>
                                                        • <?= htmlspecialchars($player['position']) ?>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div style="display: flex; gap: 6px; margin-top: 12px;">
                                                    <a href="/es_moulon/BO/admin.php?section=joueurs&edit=<?= $player['id_user_club_function'] ?>" 
                                                    style="padding: 6px 12px; background: #f59e0b; color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
                                                        ✏️
                                                    </a>
                                                    <?php if ($user_role === 'ROLE_ADMIN'): ?>
                                                        <a href="/es_moulon/BO/admin.php?section=joueurs&delete=<?= $player['id_user_club_function'] ?>" 
                                                        style="padding: 6px 12px; background: #ef4444; color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem;"
                                                        onclick="return confirm('Confirmer la suppression de <?= htmlspecialchars($player['first_name'] . ' ' . $player['name']) ?> ?')">
                                                            🗑️
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>