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

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $sql = "DELETE FROM matches WHERE id_match = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Match supprimé avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }
    $stmt->close();
    
    header('Location: resultats.php');
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_result'])) {
    $id = isset($_POST['id_match']) ? (int)$_POST['id_match'] : 0;
    $id_home_team = (int)$_POST['id_home_team'];
    $id_away_team = (int)$_POST['id_away_team'];
    $match_date = $_POST['match_date'];
    $location = trim($_POST['location']);
    $home_score = (int)$_POST['home_score'];
    $away_score = (int)$_POST['away_score'];

    if ($id_home_team === 0 || $id_away_team === 0 || empty($match_date)) {
        $_SESSION['flash']['danger'] = "Tous les champs obligatoires doivent être remplis.";
    } else {
        if ($id > 0) {
            // MODIFICATION
            $sql = "UPDATE matches 
                    SET id_home_team=?, id_away_team=?, match_date=?, location=?, home_score=?, away_score=? 
                    WHERE id_match=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iissiii', $id_home_team, $id_away_team, $match_date, $location, $home_score, $away_score, $id);
            $message = "Match modifié avec succès.";
        } else {
            // AJOUT
            $sql = "INSERT INTO matches (id_home_team, id_away_team, match_date, location, home_score, away_score, id_season, id_user) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iissiii', $id_home_team, $id_away_team, $match_date, $location, $home_score, $away_score, $_SESSION['user_id']);
            $message = "Match ajouté avec succès.";
        }
        
        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $message;
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement.";
        }
        $stmt->close();
    }
    
    header('Location: resultats.php');
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
$edit_result = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "SELECT * FROM matches WHERE id_match = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_result = $result->fetch_assoc();
    $stmt->close();
}

// --- LISTE DES MATCHS + ÉQUIPES ---
$sql = "
    SELECT m.id_match, m.match_date, m.location, m.home_score, m.away_score,
           th.name AS home_team, ta.name AS away_team,
           c.name AS category
    FROM matches m
    JOIN teams th ON m.id_home_team = th.id_team
    JOIN teams ta ON m.id_away_team = ta.id_team
    LEFT JOIN categories c ON th.id_category = c.id_category
    ORDER BY m.match_date DESC
";
$result = $conn->query($sql);
$results = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}

// --- LISTE DES ÉQUIPES ---
$teams_list = [];
$teams = $conn->query("
    SELECT t.id_team, t.name AS team_name, c.name AS category
    FROM teams t
    LEFT JOIN categories c ON t.id_category = c.id_category
    ORDER BY c.name, t.name
");
if ($teams) {
    while ($row = $teams->fetch_assoc()) {
        $teams_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Résultats - ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_back.css/resultats.css') ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🏆 Gestion des Résultats</h1>
                <p>
                    <a href="dashboard.php" class="link-retour">← Retour au dashboard</a>
                </p>
            </div>
            <?php if (!$edit_result): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouveau match
                </button>
            <?php endif; ?>
        </div>

        <?php
        // Messages flash
        if (isset($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $type => $message) {
                echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
            }
            unset($_SESSION['flash']);
        }
        ?>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit_result ? '' : 'display:none;' ?>">
            <h2>
                <?= $edit_result ? '✏️ Modifier le match' : '➕ Nouveau match' ?>
            </h2>
            
            <form method="POST" action="resultats.php">
                <?php if ($edit_result): ?>
                    <input type="hidden" name="id_match" value="<?= $edit_result['id_match'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_home_team">Équipe à domicile *</label>
                        <select id="id_home_team" name="id_home_team" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($teams_list as $team): ?>
                                <option value="<?= $team['id_team'] ?>"
                                    <?= ($edit_result && $edit_result['id_home_team'] == $team['id_team']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team['category'] . ' - ' . $team['team_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_away_team">Équipe extérieure *</label>
                        <select id="id_away_team" name="id_away_team" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($teams_list as $team): ?>
                                <option value="<?= $team['id_team'] ?>"
                                    <?= ($edit_result && $edit_result['id_away_team'] == $team['id_team']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team['category'] . ' - ' . $team['team_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="match_date">Date du match *</label>
                        <input 
                            type="datetime-local" 
                            id="match_date" 
                            name="match_date" 
                            value="<?= $edit_result ? date('Y-m-d\TH:i', strtotime($edit_result['match_date'])) : '' ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="location">Lieu</label>
                        <input 
                            type="text" 
                            id="location" 
                            name="location" 
                            value="<?= $edit_result ? htmlspecialchars($edit_result['location']) : '' ?>" 
                            placeholder="Ex: Stade municipal">
                    </div>
                </div>

                <div class="form-group">
                    <label>Score *</label>
                    <div class="score-inputs">
                        <input 
                            type="number" 
                            name="home_score" 
                            min="0" 
                            max="50"
                            value="<?= $edit_result ? $edit_result['home_score'] : '' ?>" 
                            placeholder="0"
                            required>
                        <span class="score-separator">-</span>
                        <input 
                            type="number" 
                            name="away_score" 
                            min="0" 
                            max="50"
                            value="<?= $edit_result ? $edit_result['away_score'] : '' ?>" 
                            placeholder="0"
                            required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_result" class="btn btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="resultats.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- LISTE -->
        <div class="card">
            <h2>Résultats récents (<?= count($results) ?>)</h2>
            
            <?php if (empty($results)): ?>
                <p class="no-results">Aucun match pour le moment.</p>
            <?php else: ?>
                <div class="result-grid">
                    <?php foreach ($results as $res): 
                        $home = (int)$res['home_score'];
                        $away = (int)$res['away_score'];
                        
                        if ($home > $away) {
                            $outcome = 'victory';
                            $outcome_text = $res['home_team'] . " gagne";
                            $badge_class = 'badge-victory';
                        } elseif ($home < $away) {
                            $outcome = 'defeat';
                            $outcome_text = $res['home_team'] . " perd";
                            $badge_class = 'badge-defeat';
                        } else {
                            $outcome = 'draw';
                            $outcome_text = "Match nul";
                            $badge_class = 'badge-draw';
                        }
                    ?>
                        <div class="result-card <?= $outcome ?>">
                            <div class="result-header">
                                <div class="result-date">
                                    📅 <?= date('d/m/Y H:i', strtotime($res['match_date'])) ?>
                                </div>
                                <span class="result-team">
                                    <?= htmlspecialchars($res['category']) ?>
                                </span>
                            </div>
                            
                            <div class="result-match">
                                <div class="result-teams">
                                    <div class="team-name"><?= htmlspecialchars($res['home_team']) ?></div>
                                    <div class="team-name"><?= htmlspecialchars($res['away_team']) ?></div>
                                </div>
                                
                                <div class="result-score">
                                    <span class="score-number"><?= $home ?></span>
                                    <span class="score-separator">-</span>
                                    <span class="score-number"><?= $away ?></span>
                                </div>
                                
                                <div class="result-badge <?= $badge_class ?>">
                                    <?= $outcome_text ?>
                                </div>
                            </div>
                            
                            <div class="result-info">
                                📍 <?= $res['location'] ?: 'Lieu non défini' ?>
                            </div>
                            
                            <div class="actions">
                                <a href="resultats.php?edit=<?= $res['id_match'] ?>" class="btn btn-warning">
                                    ✏️ Modifier
                                </a>
                                <a href="resultats.php?delete=<?= $res['id_match'] ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Confirmer la suppression ?')">
                                    🗑️ Supprimer
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


