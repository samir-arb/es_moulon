<?php
require_once __DIR__ . '/../../../includes/config.php';

// Protection
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['ROLE_ADMIN', 'ROLE_SPORT_MANAGER'])) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: /es_moulon/BO/admin.php?section=login');
    exit;
}

// Récupérer TOUTES les équipes du club
$club_teams_query = $conn->query("SELECT id_team, name, level FROM teams WHERE id_club_team = 1 ORDER BY level, name");
$club_teams = [];
while ($row = $club_teams_query->fetch_assoc()) {
    $club_teams[] = $row;
}

// Récupérer TOUTES les équipes adversaires
$opponent_teams_query = $conn->query("SELECT id_team, name FROM teams WHERE id_club_team = 0 ORDER BY name");
$opponent_teams = [];
while ($row = $opponent_teams_query->fetch_assoc()) {
    $opponent_teams[] = $row;
}

// Par défaut, prendre la première équipe du club
$id_default_team = $club_teams[0]['id_team'] ?? 1;

// SUPPRESSION
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM matches WHERE id_match = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "✅ Match supprimé avec succès";
    } else {
        $_SESSION['flash']['danger'] = "❌ Erreur lors de la suppression";
    }
    $stmt->close();
    header('Location: /es_moulon/BO/admin.php?section=calendrier');
    exit;
}

// AJOUT / MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_match'])) {
    $id = isset($_POST['id_match']) ? (int)$_POST['id_match'] : 0;
    $id_home_team = (int)$_POST['id_home_team'];
    $id_away_team = (int)$_POST['id_away_team'];
    $match_date = $_POST['match_date'];
    $location = trim($_POST['location']);
    $home_score = isset($_POST['home_score']) && $_POST['home_score'] !== '' ? (int)$_POST['home_score'] : null;
    $away_score = isset($_POST['away_score']) && $_POST['away_score'] !== '' ? (int)$_POST['away_score'] : null;
    $phase = $_POST['phase'] ?? 'Aller';
    $match_type = $_POST['match_type'] ?? 'championnat';

    // Validation
    if ($id_home_team === 0 || $id_away_team === 0 || empty($match_date) || empty($location)) {
        $_SESSION['flash']['danger'] = "❌ Tous les champs obligatoires doivent être remplis.";
    } elseif ($id_home_team === $id_away_team) {
        $_SESSION['flash']['danger'] = "❌ Les deux équipes doivent être différentes.";
    } else {
        if ($id > 0) {
            // MODIFICATION
            $sql = "UPDATE matches SET 
                    id_home_team = ?, 
                    id_away_team = ?, 
                    match_date = ?, 
                    location = ?, 
                    home_score = ?, 
                    away_score = ?, 
                    phase = ?, 
                    match_type = ? 
                    WHERE id_match = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iisssiisi', $id_home_team, $id_away_team, $match_date, $location, $home_score, $away_score, $phase, $match_type, $id);
            $message = "✅ Match modifié avec succès";
        } else {
            // AJOUT
            $id_season = 1; // Assurez-vous qu'une saison existe avec id=1
            $sql = "INSERT INTO matches (id_home_team, id_away_team, match_date, location, home_score, away_score, phase, match_type, id_season) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iisssiisi', $id_home_team, $id_away_team, $match_date, $location, $home_score, $away_score, $phase, $match_type, $id_season);
            $message = "✅ Match ajouté avec succès";
        }

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $message;
        } else {
            $_SESSION['flash']['danger'] = "❌ Erreur : " . $stmt->error;
        }
        $stmt->close();
    }

    header('Location: /es_moulon/BO/admin.php?section=calendrier');
    exit;
}

// RÉCUPÉRATION POUR MODIFICATION
$edit = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("
        SELECT m.*, 
               th.name as home_team, th.id_club_team as home_is_club,
               ta.name as away_team, ta.id_club_team as away_is_club
        FROM matches m
        LEFT JOIN teams th ON m.id_home_team = th.id_team
        LEFT JOIN teams ta ON m.id_away_team = ta.id_team
        WHERE m.id_match = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// LISTE DES MATCHS (du plus ancien au plus récent)
$matchs_query = $conn->query("
    SELECT m.*, 
           th.name as home_team, th.id_club_team as home_is_club, th.level as home_level,
           ta.name as away_team, ta.id_club_team as away_is_club, ta.level as away_level
    FROM matches m
    LEFT JOIN teams th ON m.id_home_team = th.id_team
    LEFT JOIN teams ta ON m.id_away_team = ta.id_team
    WHERE th.id_club_team = 1 OR ta.id_club_team = 1
    ORDER BY m.match_date ASC
");
$matchs = $matchs_query ? $matchs_query->fetch_all(MYSQLI_ASSOC) : [];

// Statistiques
$stats = ['total' => count($matchs), 'a_venir' => 0, 'joues' => 0, 'victoires' => 0, 'nuls' => 0, 'defaites' => 0];
foreach ($matchs as $m) {
    if (is_null($m['home_score'])) {
        $stats['a_venir']++;
    } else {
        $stats['joues']++;
        $is_home_club = $m['home_is_club'] == 1;
        $club_score = $is_home_club ? $m['home_score'] : $m['away_score'];
        $opp_score = $is_home_club ? $m['away_score'] : $m['home_score'];

        if ($club_score > $opp_score) $stats['victoires']++;
        elseif ($club_score == $opp_score) $stats['nuls']++;
        else $stats['defaites']++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier & Résultats - ES Moulon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
        }

        .header {
            background: linear-gradient(135deg, #009639, #007028);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 150, 57, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 2.2em;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.9;
            margin-top: 5px;
        }

        .header a {
            color: white;
            text-decoration: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card:nth-child(1) .stat-number {
            color: #3498db;
        }

        .stat-card:nth-child(2) .stat-number {
            color: #9b59b6;
        }

        .stat-card:nth-child(3) .stat-number {
            color: #95a5a6;
        }

        .stat-card:nth-child(4) .stat-number {
            color: #27ae60;
        }

        .stat-card:nth-child(5) .stat-number {
            color: #f39c12;
        }

        .stat-card:nth-child(6) .stat-number {
            color: #e74c3c;
        }

        .stat-label {
            font-size: 0.9em;
            color: #666;
            font-weight: 600;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .card h2 {
            color: #009639;
            margin-bottom: 25px;
            border-bottom: 3px solid #009639;
            padding-bottom: 12px;
            font-size: 1.5em;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
            font-size: 0.95em;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #009639;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.95em;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #009639;
            color: white;
        }

        .btn-primary:hover {
            background: #007028;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 150, 57, 0.3);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-warning {
            background: #ffc107;
            color: #000;
            padding: 8px 16px;
            font-size: 0.85em;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            font-size: 0.85em;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        /* Liste des matchs */
        .matches-list {
            display: grid;
            gap: 15px;
            margin-top: 20px;
        }

        .match-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            display: grid;
            grid-template-columns: 140px 1fr auto;
            gap: 20px;
            align-items: center;
            transition: all 0.3s;
        }

        .match-card:hover {
            border-color: #009639;
            box-shadow: 0 4px 15px rgba(0, 150, 57, 0.15);
            transform: translateY(-2px);
        }

        .match-date {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #009639, #007028);
            color: white;
            border-radius: 10px;
        }

        .match-date .day {
            font-size: 2em;
            font-weight: 700;
            line-height: 1;
        }

        .match-date .month {
            font-size: 0.9em;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .match-date .time {
            font-size: 0.85em;
            margin-top: 8px;
            opacity: 0.9;
        }

        .match-info {
            flex: 1;
        }

        .match-teams {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .match-teams .team {
            flex: 1;
            font-size: 1.1em;
            font-weight: 600;
        }

        .match-teams .team.club {
            color: #009639;
        }

        .match-teams .vs {
            font-weight: 700;
            color: #999;
            padding: 0 10px;
        }

        .match-teams .team-level {
            font-size: 0.8em;
            color: #666;
            font-weight: 400;
        }

        .match-details {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .match-score {
            font-size: 1.8em;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 8px;
        }

        .match-score.victory {
            color: #27ae60;
            background: #d4edda;
        }

        .match-score.defeat {
            color: #e74c3c;
            background: #f8d7da;
        }

        .match-score.draw {
            color: #f39c12;
            background: #fff3cd;
        }

        .match-score.upcoming {
            color: #95a5a6;
            background: #e9ecef;
            font-size: 1em;
        }

        .match-location {
            font-size: 0.9em;
            color: #666;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 14px;
            font-size: 0.8em;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-aller {
            background: #28a745;
            color: white;
        }

        .badge-retour {
            background: #007bff;
            color: white;
        }

        .badge-championnat {
            background: #17a2b8;
            color: white;
        }

        .badge-coupe {
            background: #dc3545;
            color: white;
        }

        .badge-amical {
            background: #6c757d;
            color: white;
        }

        .match-actions {
            display: flex;
            gap: 8px;
            flex-direction: column;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .match-card {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .match-teams {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📅 Calendrier & Résultats</h1>
                <p>
                    <a href="/es_moulon/BO/admin.php?section=dashboard">← Retour au tableau de bord</a>
                </p>
            </div>
            <?php if (!$edit): ?>
                <button class="btn btn-primary" onclick="toggleForm()">➕ Nouveau match</button>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
                <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach;
            unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['a_venir'] ?></div>
                <div class="stat-label">À venir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['joues'] ?></div>
                <div class="stat-label">Joués</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['victoires'] ?></div>
                <div class="stat-label">Victoires</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['nuls'] ?></div>
                <div class="stat-label">Nuls</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['defaites'] ?></div>
                <div class="stat-label">Défaites</div>
            </div>
        </div>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit ? '' : 'display:none;' ?>">
            <h2><?= $edit ? '✏️ Modifier le match' : '➕ Ajouter un match' ?></h2>

            <form method="POST" action="">
                <input type="hidden" name="save_match" value="1">
                <?php if ($edit): ?>
                    <input type="hidden" name="id_match" value="<?= $edit['id_match'] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label>🏠 Équipe à domicile</label>
                        <select name="id_home_team" required>
                            <option value="">-- Sélectionner --</option>
                            <optgroup label="🏆 Équipes du club">
                                <?php foreach ($club_teams as $t): ?>
                                    <option value="<?= $t['id_team'] ?>" <?= ($edit && $edit['id_home_team'] == $t['id_team']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['level']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="⚔️ Adversaires">
                                <?php foreach ($opponent_teams as $t): ?>
                                    <option value="<?= $t['id_team'] ?>" <?= ($edit && $edit['id_home_team'] == $t['id_team']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>✈️ Équipe à l'extérieur</label>
                        <select name="id_away_team" required>
                            <option value="">-- Sélectionner --</option>
                            <optgroup label="🏆 Équipes du club">
                                <?php foreach ($club_teams as $t): ?>
                                    <option value="<?= $t['id_team'] ?>" <?= ($edit && $edit['id_away_team'] == $t['id_team']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['level']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="⚔️ Adversaires">
                                <?php foreach ($opponent_teams as $t): ?>
                                    <option value="<?= $t['id_team'] ?>" <?= ($edit && $edit['id_away_team'] == $t['id_team']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>📅 Date et heure</label>
                        <input type="datetime-local" name="match_date" value="<?= $edit ? date('Y-m-d\TH:i', strtotime($edit['match_date'])) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label>📍 Lieu</label>
                        <input type="text" name="location" value="<?= $edit ? htmlspecialchars($edit['location']) : '' ?>" placeholder="Stade du Moulon" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>🔄 Phase</label>
                        <select name="phase" required>
                            <option value="Aller" <?= ($edit && $edit['phase'] == 'Aller') ? 'selected' : '' ?>>Aller</option>
                            <option value="Retour" <?= ($edit && $edit['phase'] == 'Retour') ? 'selected' : '' ?>>Retour</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>⚽ Type de match</label>
                        <select name="match_type" required>
                            <option value="championnat" <?= ($edit && $edit['match_type'] == 'championnat') ? 'selected' : '' ?>>Championnat</option>
                            <option value="coupe" <?= ($edit && $edit['match_type'] == 'coupe') ? 'selected' : '' ?>>Coupe</option>
                            <option value="amical" <?= ($edit && $edit['match_type'] == 'amical') ? 'selected' : '' ?>>Amical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>🎯 Score domicile (optionnel)</label>
                        <input type="number" name="home_score" min="0" max="50" value="<?= $edit && !is_null($edit['home_score']) ? $edit['home_score'] : '' ?>" placeholder="Laisser vide si pas encore joué">
                    </div>

                    <div class="form-group">
                        <label>🎯 Score extérieur (optionnel)</label>
                        <input type="number" name="away_score" min="0" max="50" value="<?= $edit && !is_null($edit['away_score']) ? $edit['away_score'] : '' ?>" placeholder="Laisser vide si pas encore joué">
                    </div>
                </div>

                <div style="margin-top: 25px; display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-success">
                        <?= $edit ? '💾 Enregistrer les modifications' : '➕ Ajouter le match' ?>
                    </button>
                    <?php if ($edit): ?>
                        <a href="/es_moulon/BO/admin.php?section=calendrier" class="btn btn-secondary">Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- LISTE DES MATCHS -->
        <div class="card">
            <h2>📋 Liste des matchs (<?= count($matchs) ?>)</h2>

            <?php if (empty($matchs)): ?>
                <div class="empty-state">
                    <p style="font-size:1.1em;margin-bottom:10px;">Aucun match enregistré</p>
                    <p style="font-size:0.9em;">Ajoutez votre premier match avec le bouton ci-dessus.</p>
                </div>
            <?php else: ?>
                <div class="matches-list">
                    <?php foreach ($matchs as $m):
                        $is_home_club = $m['home_is_club'] == 1;
                        $team_club = $is_home_club ? $m['home_team'] : $m['away_team'];
                        $team_adversaire = $is_home_club ? $m['away_team'] : $m['home_team'];
                        $club_level = $is_home_club ? $m['home_level'] : $m['away_level'];

                        // Score du point de vue de l'équipe du club
                        $score_club = $is_home_club ? $m['home_score'] : $m['away_score'];
                        $score_adv = $is_home_club ? $m['away_score'] : $m['home_score'];

                        $score_class = '';
                        if (!is_null($score_club)) {
                            if ($score_club > $score_adv) $score_class = 'victory';
                            elseif ($score_club < $score_adv) $score_class = 'defeat';
                            else $score_class = 'draw';
                        }

                        $date_obj = new DateTime($m['match_date']);
                        $months_fr = ['', 'JAN', 'FÉV', 'MAR', 'AVR', 'MAI', 'JUN', 'JUI', 'AOÛ', 'SEP', 'OCT', 'NOV', 'DÉC'];
                    ?>
                        <div class="match-card">
                            <div class="match-date">
                                <div class="day"><?= $date_obj->format('d') ?></div>
                                <div class="month"><?= $months_fr[(int)$date_obj->format('m')] ?> <?= $date_obj->format('Y') ?></div>
                                <div class="time"><?= $date_obj->format('H:i') ?></div>
                            </div>

                            <div class="match-info">
                                <div class="match-teams">
                                    <?php if ($is_home_club): ?>
                                        <!-- Domicile : Club en premier -->
                                        <div class="team club">
                                            🏠 <?= htmlspecialchars($team_club) ?>
                                            <?php if ($club_level): ?>
                                                <span class="team-level">(<?= htmlspecialchars($club_level) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="vs">VS</div>
                                        <div class="team">
                                            <?= htmlspecialchars($team_adversaire) ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Extérieur : Adversaire en premier -->
                                        <div class="team">
                                            <?= htmlspecialchars($team_adversaire) ?>
                                        </div>
                                        <div class="vs">VS</div>
                                        <div class="team club">
                                            ✈️ <?= htmlspecialchars($team_club) ?>
                                            <?php if ($club_level): ?>
                                                <span class="team-level">(<?= htmlspecialchars($club_level) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="match-details">
                                    <?php if (!is_null($score_club)): ?>
                                        <div class="match-score <?= $score_class ?>">
                                            <?= $score_club ?> - <?= $score_adv ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="match-score upcoming">À venir</div>
                                    <?php endif; ?>

                                    <div class="match-location">📍 <?= htmlspecialchars($m['location']) ?></div>

                                    <span class="badge badge-<?= strtolower($m['phase']) ?>">
                                        <?= $m['phase'] ?>
                                    </span>
                                    <span class="badge badge-<?= $m['match_type'] ?>">
                                        <?= ucfirst($m['match_type']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="match-actions">
                                <a href="/es_moulon/BO/admin.php?section=calendrier&edit=<?= $m['id_match'] ?>"
                                    class="btn btn-warning"
                                    title="Modifier">
                                    ✏️ Modifier
                                </a>
                                <a href="/es_moulon/BO/admin.php?section=calendrier&delete=<?= $m['id_match'] ?>"
                                    class="btn btn-danger"
                                    title="Supprimer"
                                    onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce match ?')">
                                    🗑️ Supprimer
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('formSection');
            if (form.style.display === 'none') {
                form.style.display = 'block';
                window.scrollTo({
                    top: form.offsetTop - 20,
                    behavior: 'smooth'
                });
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>

</html>