<?php

/**
 * ========================================
 * GESTION DES CONVOCATIONS - ES MOULON
 * ========================================
 * Permet de créer, modifier, supprimer et afficher les convocations
 * avec sélection des joueurs multi-équipes et organisation par catégorie
 */

require_once __DIR__ . '/../../../includes/config.php';

// ========================================
// 🔒 SÉCURITÉ - Vérification des accès
// ========================================

if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: ../../login.php');
    exit;
}

$allowed_roles = ['ROLE_ADMIN', 'ROLE_SPORT_MANAGER'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header('Location: ../../dashboard.php');
    exit;
}

// ===========================================================
// 🔍 Vérification AJAX : joueur déjà convoqué le même jour
// ===========================================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'check_player') {
    header('Content-Type: application/json');

    $id_player = isset($_GET['id_player']) ? (int)$_GET['id_player'] : 0;
    $match_date = $_GET['match_date'] ?? '';

    if ($id_player <= 0 || empty($match_date)) {
        echo json_encode(['exists' => false]);
        exit;
    }

    $sql = "
        SELECT c.match_date, t.name AS team_name, u.first_name, u.name
        FROM convocation_players cp
        INNER JOIN convocations c ON cp.id_convocation = c.id_convocation
        INNER JOIN teams t ON c.id_team = t.id_team
        INNER JOIN users u ON cp.id_player = u.id_user
        WHERE cp.id_player = ? AND c.match_date = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $id_player, $match_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'exists' => true,
            'match_date' => date('d/m/Y', strtotime($row['match_date'])),
            'team_name' => $row['team_name'],
            'player_name' => "{$row['first_name']} {$row['name']}"
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
    $stmt->close();
    exit;
}


// ========================================
// 🗑️ SUPPRESSION D'UNE CONVOCATION
// ========================================

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Supprimer d'abord les joueurs liés
    $conn->query("DELETE FROM convocation_players WHERE id_convocation = $id");

    // Puis la convocation
    $stmt = $conn->prepare("DELETE FROM convocations WHERE id_convocation = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Convocation supprimée avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }

    $stmt->close();
    header('Location: ?section=convocations');
    exit;
}

// ========================================
// 💾 AJOUT OU MODIFICATION D'UNE CONVOCATION
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_convocation'])) {

    // Récupération des données du formulaire
    $id = isset($_POST['id_convocation']) ? (int)$_POST['id_convocation'] : 0;
    $id_team = (int)$_POST['id_team'];
    $match_date = $_POST['match_date'] ?? null;
    $match_time = $_POST['match_time'] ?? null;
    $opponent = trim($_POST['opponent'] ?? '');
    $home_away = $_POST['home_away'] ?? 'domicile';
    $location = trim($_POST['location'] ?? '');
    $meeting_time = $_POST['meeting_time'] ?? null;
    $meeting_place = trim($_POST['meeting_place'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $selected_players = isset($_POST['players']) ? array_map('intval', $_POST['players']) : [];

    // Validation des champs obligatoires
    if ($id_team === 0 || empty($match_date) || empty($opponent)) {
        $_SESSION['flash']['danger'] = "⚠️ Veuillez remplir tous les champs obligatoires.";
        header('Location: ?section=convocations');
        exit;
    }

    // Vérification des doublons (joueurs déjà convoqués le même jour)
    if (!empty($selected_players)) {
        $placeholders = implode(',', array_fill(0, count($selected_players), '?'));
        $types = str_repeat('i', count($selected_players));

        $sql_check = "
            SELECT cp.id_player, u.first_name, u.name, c.id_team, t.name AS team_name
            FROM convocation_players cp
            INNER JOIN convocations c ON cp.id_convocation = c.id_convocation
            INNER JOIN users u ON cp.id_player = u.id_user
            INNER JOIN teams t ON c.id_team = t.id_team
            WHERE c.match_date = ? 
              AND cp.id_player IN ($placeholders)
              " . ($id > 0 ? "AND c.id_convocation != $id" : "") . "
        ";

        $stmt = $conn->prepare($sql_check);
        $bind_types = 's' . $types;
        $stmt->bind_param($bind_types, $match_date, ...$selected_players);
        $stmt->execute();
        $result = $stmt->get_result();

        $duplicates = [];
        while ($row = $result->fetch_assoc()) {
            $duplicates[] = "{$row['first_name']} {$row['name']} ({$row['team_name']})";
        }
        $stmt->close();

        if (!empty($duplicates)) {
            $_SESSION['flash']['warning'] = "⚠️ Attention ! Ces joueurs sont déjà convoqués le " .
                date('d/m/Y', strtotime($match_date)) . " :<br><strong>" .
                implode(', ', $duplicates) . "</strong>";
        }
    }

    // Enregistrement dans la base de données
    if ($id > 0) {
        // MODIFICATION
        $sql = "UPDATE convocations 
                SET id_team = ?, match_date = ?, match_time = ?, opponent = ?, 
                    home_away = ?, location = ?, meeting_time = ?, meeting_place = ?, message = ?
                WHERE id_convocation = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'issssssssi',
            $id_team,
            $match_date,
            $match_time,
            $opponent,
            $home_away,
            $location,
            $meeting_time,
            $meeting_place,
            $message,
            $id
        );
        $message_flash = "✅ Convocation modifiée avec succès.";
    } else {
        // AJOUT
        $sql = "INSERT INTO convocations 
                (id_team, match_date, match_time, opponent, home_away, location, 
                 meeting_time, meeting_place, message, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $created_by = $_SESSION['user_id'];
        $stmt->bind_param(
            'issssssssi',
            $id_team,
            $match_date,
            $match_time,
            $opponent,
            $home_away,
            $location,
            $meeting_time,
            $meeting_place,
            $message,
            $created_by
        );
        $message_flash = "✅ Convocation créée avec succès.";
    }

    if ($stmt->execute()) {
        $convocation_id = $id > 0 ? $id : $conn->insert_id;

        // Supprimer les anciens joueurs liés (en cas de modification)
        $conn->query("DELETE FROM convocation_players WHERE id_convocation = " . (int)$convocation_id);

        // Enregistrer les nouveaux joueurs sélectionnés
        if (!empty($selected_players)) {
            $stmt2 = $conn->prepare("INSERT INTO convocation_players (id_convocation, id_player, status) 
                                    VALUES (?, ?, 'convoqué')");
            foreach ($selected_players as $player_id) {
                $stmt2->bind_param('ii', $convocation_id, $player_id);
                $stmt2->execute();
            }
            $stmt2->close();
        }

        $_SESSION['flash']['success'] = $message_flash;
    } else {
        $_SESSION['flash']['danger'] = "❌ Erreur lors de l'enregistrement.";
    }

    $stmt->close();
    header('Location: ?section=convocations');
    exit;
}

// ========================================
// ✏️ RÉCUPÉRATION POUR ÉDITION
// ========================================

$edit_convocation = null;
$selected_players = [];

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    // Récupérer les données de la convocation
    $stmt = $conn->prepare("SELECT * FROM convocations WHERE id_convocation = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit_convocation = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Récupérer les joueurs déjà sélectionnés
    if ($edit_convocation) {
        $stmt = $conn->prepare("SELECT id_player FROM convocation_players WHERE id_convocation = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $selected_players[] = $row['id_player'];
        }
        $stmt->close();
    }
}

// ========================================
// 📋 RÉCUPÉRATION DES CONVOCATIONS
// ========================================

$sql = "
    SELECT 
        c.*, 
        t.name AS team_name, 
        t.level AS team_level,
        cat.name AS category_name,
        cat.id_category
    FROM convocations c
    INNER JOIN teams t ON c.id_team = t.id_team
    LEFT JOIN categories cat ON t.id_category = cat.id_category
    WHERE t.id_club_team = 1
    ORDER BY cat.name, c.match_date DESC, c.match_time DESC
";
$result = $conn->query($sql);
$all_convocations = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Organiser les convocations par catégorie
$convocations_by_category = [];
$total_convocations = 0;
foreach ($all_convocations as $conv) {
    $category = $conv['category_name'] ?? 'Sans catégorie';
    $convocations_by_category[$category][] = $conv;
    $total_convocations++;
}

// ========================================
// 🏆 RÉCUPÉRATION DES ÉQUIPES PAR CATÉGORIE
// ========================================

$teams_result = $conn->query("
    SELECT 
        t.id_team, 
        t.name AS team_name, 
        t.level AS team_level,
        cat.name AS category_name,
        cat.id_category
    FROM teams t
    LEFT JOIN categories cat ON t.id_category = cat.id_category
    WHERE t.id_club_team = 1
    ORDER BY cat.name, t.level, t.name
");

$teams_by_category = [];
while ($team = $teams_result->fetch_assoc()) {
    $category = $team['category_name'] ?? 'Sans catégorie';
    $teams_by_category[$category][] = $team;
}

// ========================================
// 👥 RÉCUPÉRATION DES JOUEURS AVEC LEURS ÉQUIPES
// ========================================

$sqlPlayers = "
    SELECT 
        u.id_user, 
        u.first_name, 
        u.name, 
        ucf.id_team,
        ucf.position,
        ucf.jersey_number,
        t.name AS team_name,
        t.level AS team_level,
        cat.name AS category_name
    FROM users u
    INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
    INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
    INNER JOIN teams t ON ucf.id_team = t.id_team
    LEFT JOIN categories cat ON t.id_category = cat.id_category
    WHERE LOWER(cf.function_name) LIKE '%joueur%' 
    AND t.id_club_team = 1
    AND u.status = 1
    ORDER BY u.name, u.first_name, cat.name, t.level
";
$players_result = $conn->query($sqlPlayers);

// Organiser les joueurs avec toutes leurs équipes
$players_data = [];
while ($player = $players_result->fetch_assoc()) {
    $player_id = $player['id_user'];

    if (!isset($players_data[$player_id])) {
        $players_data[$player_id] = [
            'id_user' => $player['id_user'],
            'first_name' => $player['first_name'],
            'name' => $player['name'],
            'teams' => []
        ];
    }

    $players_data[$player_id]['teams'][] = [
        'id_team' => $player['id_team'],
        'team_name' => $player['team_name'],
        'team_level' => $player['team_level'],
        'category_name' => $player['category_name'],
        'position' => $player['position'],
        'jersey_number' => $player['jersey_number']
    ];
}
?>

    <style>
        /* ========================================
           STYLES GÉNÉRAUX
           ======================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f3f4f6;;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ========================================
           HEADER
           ======================================== */
        .header {
            background: white;
            padding: 24px 32px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 28px;
            color: #1a202c;
            font-weight: 700;
        }

        /* ========================================
           BOUTONS
           ======================================== */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #465cbcff 0%, #462764ff 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        /* Boutons d'action du panier */
        .basket-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .basket-actions .btn {
            flex: 1;
            min-width: 180px;
            justify-content: center;
        }

        /* ========================================
           ALERTES
           ======================================== */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        /* ========================================
           CARTES
           ======================================== */
        .card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .card h2 {
            font-size: 22px;
            color: #1a202c;
            margin-bottom: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ========================================
           FORMULAIRES
           ======================================== */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .form-group label .required {
            color: #ef4444;
            margin-left: 4px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .section-title {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            margin: 20px 0 16px 0;
            font-weight: 600;
            font-size: 16px;
        }

        .form-actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* ========================================
           SÉLECTION DES JOUEURS
           ======================================== */
        .players-selector {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            max-height: 500px;
            overflow-y: auto;
            background: #fafbfc;
        }

        .players-selector::-webkit-scrollbar {
            width: 8px;
        }

        .players-selector::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 10px;
        }

        .players-selector::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border-radius: 10px;
        }

        /* Panier de joueurs sélectionnés */
        #selection-basket {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px dashed #3b82f6;
            min-height: 120px;
        }

        #selection-basket .no-players {
            color: #60a5fa;
            font-weight: 500;
        }

        #selection-basket .players-list {
            padding: 8px;
        }

        .basket-player-item {
            background: white;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }

        .basket-player-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .basket-player-info {
            flex: 1;
        }

        .basket-player-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .basket-player-team {
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
        }

        .basket-remove-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .basket-remove-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        .player-item {
            display: flex;
            align-items: flex-start;
            padding: 14px;
            margin-bottom: 8px;
            background: #f9fafb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .player-item:hover {
            background: #f3f4f6;
        }

        .player-item.selected {
            background: #dbeafe;
            border-left-color: #3b82f6;
        }

        .player-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            margin-top: 2px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .player-info {
            flex: 1;
        }

        .player-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .player-teams {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
        }

        .team-badge {
            background: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }

        .team-badge.active {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }

        .no-players {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-style: italic;
        }

        /* ========================================
           GRILLE DES CONVOCATIONS
           ======================================== */
        .convocation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 24px;
            margin-top: 20px;
        }

        .convocation-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .convocation-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .convocation-header {
            background: linear-gradient(135deg, #323232ff 0%, #000000ff 100%);
            color: white;
            padding: 20px;
        }

        .convocation-date {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .convocation-team {
            background: rgba(10, 191, 62, 1);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .convocation-body {
            padding: 20px;
        }

        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-icon {
            font-size: 18px;
            width: 24px;
            flex-shrink: 0;
        }

        .info-section {
            background: #f0f9ff;
            padding: 14px;
            border-radius: 10px;
            margin-top: 12px;
            border-left: 3px solid #3b82f6;
        }

        .info-section-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .convocation-players {
            background: #f9fafb;
            padding: 16px;
            border-radius: 10px;
            margin-top: 16px;
        }

        .convocation-players strong {
            display: block;
            margin-bottom: 10px;
            color: #374151;
        }

        .players-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .player-badge {
            background: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: #1f2937;
            border: 1px solid #e5e7eb;
        }

        .convocation-message {
            background: #fef3c7;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
            font-size: 14px;
            color: #92400e;
            border-left: 3px solid #f59e0b;
        }

        .convocation-actions {
            padding: 16px 20px;
            background: #f9fafb;
            display: flex;
            gap: 10px;
            border-top: 1px solid #e5e7eb;
        }

        .home-away-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .home-away-badge.domicile {
            background: #d1fae5;
            color: #065f46;
        }

        .home-away-badge.extérieur {
            background: #dbeafe;
            color: #1e40af;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .category-header {
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        .category-header h3 {
            font-size: 20px;
            color: #1f2937;
            font-weight: 700;
        }

        /*  Toast d’avertissement élégant */

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fef3c7;
            color: #92400e;
            padding: 14px 18px;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 9999;
        }

        .toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }


        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .convocation-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="container">

        <!-- ========================================
         HEADER
         ======================================== -->
        <div class="header">
            <div>
                <h1>📅 Gestion des Convocations</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
        </div>

        <!-- ========================================
         MESSAGES FLASH
         ======================================== -->
        <?php
        if (isset($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $type => $msg) {
                $icon = $type === 'success' ? '✓' : ($type === 'danger' ? '✕' : '⚠');
                $display_msg = ($type === 'warning') ? $msg : htmlspecialchars($msg);
                echo "<div class='alert alert-$type'><span>$icon</span> $display_msg</div>";
            }
            unset($_SESSION['flash']);
        }
        ?>

        <!-- ========================================
         FORMULAIRE DE CRÉATION/MODIFICATION
         ======================================== -->
        <div class="card">
            <h2><?= $edit_convocation ? "✏️ Modifier la convocation" : "➕ Nouvelle convocation" ?></h2>

            <form method="POST" action="?section=convocations">
                <?php if ($edit_convocation): ?>
                    <input type="hidden" name="id_convocation" value="<?= $edit_convocation['id_convocation'] ?>">
                <?php endif; ?>

                <!-- Informations du match -->
                <div class="section-title">⚽ Informations du match</div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_team">Équipe <span class="required">*</span></label>
                        <select id="id_team" name="id_team" required>
                            <option value="">-- Sélectionner une équipe --</option>
                            <?php foreach ($teams_by_category as $category => $teams): ?>
                                <optgroup label="🏆 <?= htmlspecialchars($category) ?>">
                                    <?php foreach ($teams as $team): ?>
                                        <option value="<?= $team['id_team'] ?>"
                                            <?= ($edit_convocation && $edit_convocation['id_team'] == $team['id_team']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($team['team_level'] . ' - ' . $team['team_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="opponent">Adversaire <span class="required">*</span></label>
                        <input type="text" id="opponent" name="opponent" required
                            placeholder="Ex: FC Issoudun"
                            value="<?= $edit_convocation ? htmlspecialchars($edit_convocation['opponent']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="match_date">Date du match <span class="required">*</span></label>
                        <input type="date" id="match_date" name="match_date" required
                            value="<?= $edit_convocation ? $edit_convocation['match_date'] : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="match_time">Heure du match</label>
                        <input type="time" id="match_time" name="match_time"
                            value="<?= $edit_convocation ? $edit_convocation['match_time'] : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="home_away">Domicile / Extérieur</label>
                        <select id="home_away" name="home_away">
                            <option value="domicile" <?= ($edit_convocation && $edit_convocation['home_away'] == 'domicile') ? 'selected' : '' ?>>
                                🏠 Domicile
                            </option>
                            <option value="extérieur" <?= ($edit_convocation && $edit_convocation['home_away'] == 'extérieur') ? 'selected' : '' ?>>
                                ✈️ Extérieur
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location">Lieu du match</label>
                        <input type="text" id="location" name="location"
                            placeholder="Ex: Stade Municipal"
                            value="<?= $edit_convocation ? htmlspecialchars($edit_convocation['location']) : '' ?>">
                    </div>
                </div>

                <!-- Rendez-vous -->
                <div class="section-title">📍 Rendez-vous</div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="meeting_time">Heure de rendez-vous</label>
                        <input type="time" id="meeting_time" name="meeting_time"
                            value="<?= $edit_convocation ? ($edit_convocation['meeting_time'] ?? '') : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="meeting_place">Lieu de rendez-vous</label>
                        <input type="text" id="meeting_place" name="meeting_place"
                            placeholder="Ex: Vestiaire du club"
                            value="<?= $edit_convocation ? htmlspecialchars($edit_convocation['meeting_place'] ?? '') : '' ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="message">📝 Message / Instructions</label>
                        <textarea id="message" name="message"
                            placeholder="Informations complémentaires pour les joueurs..."><?= $edit_convocation ? htmlspecialchars($edit_convocation['message'] ?? '') : '' ?></textarea>
                    </div>
                </div>

                <!-- Sélection des joueurs -->
                <div class="section-title">👥 Joueurs convoqués</div>

                <div class="form-group full-width">
                    <!-- Filtres -->
                    <div class="form-grid" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <label for="filter-category">Filtrer par catégorie</label>
                            <select id="filter-category">
                                <option value="">Toutes les catégories</option>
                                <?php
                                $categories_query = $conn->query("SELECT DISTINCT name FROM categories ORDER BY name");
                                while ($cat = $categories_query->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($cat['name']) ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="filter-team">Filtrer par équipe</label>
                            <select id="filter-team">
                                <option value="">Toutes les équipes</option>
                                <?php
                                $teams_query = $conn->query("SELECT id_team, name, level FROM teams WHERE id_club_team = 1 ORDER BY name");
                                while ($team = $teams_query->fetch_assoc()): ?>
                                    <option value="<?= $team['id_team'] ?>">
                                        <?= htmlspecialchars($team['level'] . ' - ' . $team['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Liste des joueurs disponibles -->
                    <div id="players-container" class="players-selector">
                        <div class="no-players">Chargement des joueurs...</div>
                    </div>

                    <!-- Panier de joueurs sélectionnés -->
                    <div class="section-title basket-title" style="margin-top: 20px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                        🧺 Joueurs sélectionnés (0)
                    </div>
                    <div id="selection-basket" class="players-selector">
                        <div class="no-players">Sélectionnez des joueurs pour les ajouter à la convocation</div>
                    </div>

                    <!-- Boutons d'actions sur les joueurs -->
                    <div class="basket-actions" style="margin-top: 15px;">
                        <button type="button" class="btn btn-info" onclick="toggleAllPlayers(true)">
                            ✓ Tout sélectionner
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleAllPlayers(false)">
                            ✕ Tout décocher
                        </button>
                        <button type="button" class="btn btn-danger" onclick="clearBasket()">
                            🗑️ Vider la sélection
                        </button>
                    </div>

                    <!-- Champs cachés pour l'envoi des joueurs sélectionnés -->
                    <div id="selected-hidden-inputs"></div>
                </div>

                <!-- Boutons d'action du formulaire -->
                <div class="form-actions">
                    <button type="submit" name="save_convocation" class="btn btn-success">
                        💾 Enregistrer la convocation
                    </button>
                    <a href="?section=convocations" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- ========================================
         LISTE DES CONVOCATIONS
         ======================================== -->
        <div class="card">
            <h2>📋 Liste des convocations (<?= $total_convocations ?>)</h2>

            <?php if ($total_convocations === 0): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p>Aucune convocation créée pour le moment</p>
                </div>

            <?php else: ?>
                <?php foreach ($convocations_by_category as $category => $category_convocations): ?>

                    <div class="category-header">
                        <h3>🏆 <?= htmlspecialchars($category) ?> (<?= count($category_convocations) ?>)</h3>
                    </div>

                    <div class="convocation-grid">
                        <?php foreach ($category_convocations as $conv): ?>
                            <div class="convocation-card">

                                <!-- En-tête de la carte -->
                                <div class="convocation-header">
                                    <div class="convocation-date">
                                        <?php
                                        $formatter = new IntlDateFormatter(
                                            'fr_FR',
                                            IntlDateFormatter::LONG,
                                            IntlDateFormatter::NONE,
                                            'Europe/Paris',
                                            IntlDateFormatter::GREGORIAN,
                                            'd MMMM yyyy'
                                        );
                                        echo $formatter->format(new DateTime($conv['match_date']));
                                        ?>
                                        <?php if (!empty($conv['match_time'])): ?>
                                            - <?= substr($conv['match_time'], 0, 5) ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="convocation-team">
                                        <?= htmlspecialchars($conv['team_level'] . ' - ' . $conv['team_name']) ?>
                                    </span>
                                </div>

                                <!-- Corps de la carte -->
                                <div class="convocation-body">

                                    <!-- Adversaire -->
                                    <div class="info-row">
                                        <span class="info-icon">⚔️</span>
                                        <strong><?= htmlspecialchars($conv['opponent']) ?></strong>
                                    </div>

                                    <!-- Domicile / Extérieur -->
                                    <?php if (!empty($conv['home_away'])): ?>
                                        <div class="info-row">
                                            <span class="info-icon">
                                                <?= $conv['home_away'] == 'domicile' ? '🏠' : '✈️' ?>
                                            </span>
                                            <span class="home-away-badge <?= $conv['home_away'] ?>">
                                                <?= ucfirst($conv['home_away']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Lieu du match -->
                                    <?php if (!empty($conv['location'])): ?>
                                        <div class="info-row">
                                            <span class="info-icon">📍</span>
                                            <?= htmlspecialchars($conv['location']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Section Rendez-vous -->
                                    <?php if (!empty($conv['meeting_time']) || !empty($conv['meeting_place'])): ?>
                                        <div class="info-section">
                                            <div class="info-section-title">📍 Rendez-vous</div>
                                            <?php if (!empty($conv['meeting_time'])): ?>
                                                <div class="info-row">
                                                    <span class="info-icon">🕐</span>
                                                    <?= substr($conv['meeting_time'], 0, 5) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($conv['meeting_place'])): ?>
                                                <div class="info-row">
                                                    <span class="info-icon">📌</span>
                                                    <?= htmlspecialchars($conv['meeting_place']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Joueurs convoqués -->
                                    <?php
                                    $pStmt = $conn->prepare("
                                    SELECT u.first_name, u.name 
                                    FROM convocation_players cp 
                                    JOIN users u ON cp.id_player = u.id_user 
                                    WHERE cp.id_convocation = ? 
                                    ORDER BY u.name
                                ");
                                    $pStmt->bind_param('i', $conv['id_convocation']);
                                    $pStmt->execute();
                                    $players = $pStmt->get_result();

                                    if ($players->num_rows > 0):
                                    ?>
                                        <div class="convocation-players">
                                            <strong>👥 Joueurs convoqués (<?= $players->num_rows ?>)</strong>
                                            <div class="players-list">
                                                <?php while ($p = $players->fetch_assoc()): ?>
                                                    <span class="player-badge">
                                                        <?= htmlspecialchars($p['first_name'] . ' ' . $p['name']) ?>
                                                    </span>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    <?php
                                        $pStmt->close();
                                    endif;
                                    ?>

                                    <!-- Message / Instructions -->
                                    <?php if (!empty($conv['message'])): ?>
                                        <div class="convocation-message">
                                            💬 <?= nl2br(htmlspecialchars($conv['message'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions -->
                                <div class="convocation-actions">
                                    <a href="?section=convocations&edit=<?= $conv['id_convocation'] ?>"
                                        class="btn btn-warning">
                                        ✏️ Modifier
                                    </a>
                                    <a href="?section=convocations&delete=<?= $conv['id_convocation'] ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cette convocation ?')">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <script>
        // ========================================
        // DONNÉES PHP VERS JAVASCRIPT
        // ========================================
        const allPlayersData = <?= json_encode(array_values($players_data)) ?>;
        const allTeamsByCategory = <?= json_encode($teams_by_category) ?>;
        const preselectedPlayers = <?= json_encode($selected_players ?? []) ?>;

        // ========================================
        // VARIABLES GLOBALES
        // ========================================
        let selectedPlayers = new Set(preselectedPlayers);
        const selectedMap = new Map();

        // Initialiser le map avec les joueurs présélectionnés
        preselectedPlayers.forEach(id => {
            const player = allPlayersData.find(p => p.id_user == id);
            if (player) {
                selectedMap.set(id, player);
            }
        });

        // ========================================
        // FONCTIONS UTILITAIRES
        // ========================================

        /**
         * Échappe les caractères HTML pour éviter les injections XSS
         */
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        /**
         * Synchronise les champs cachés avec les joueurs sélectionnés
         */
        function syncHiddenInputs() {
            const container = document.getElementById('selected-hidden-inputs');
            container.innerHTML = '';

            selectedPlayers.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'players[]';
                input.value = id;
                container.appendChild(input);
            });
        }

        /**
         * Met à jour le titre du panier avec le nombre de joueurs
         */
        function updateBasketTitle() {
            const basketTitle = document.querySelector('.basket-title');
            if (basketTitle) {
                basketTitle.textContent = `🧺 Joueurs sélectionnés (${selectedPlayers.size})`;
            }
        }

        // ========================================
        // GESTION DU PANIER
        // ========================================

        /**
         * Affiche les joueurs sélectionnés dans le panier
         */
        function renderBasket() {
            const container = document.getElementById('selection-basket');

            if (selectedPlayers.size === 0) {
                container.innerHTML = '<div class="no-players">Sélectionnez des joueurs pour les ajouter à la convocation</div>';
                updateBasketTitle();
                syncHiddenInputs();
                return;
            }

            let html = '';

            selectedPlayers.forEach(id => {
                let player = selectedMap.get(id);

                // Si le joueur n'est pas dans le map, le chercher dans les données
                if (!player) {
                    player = allPlayersData.find(p => p.id_user == id);
                    if (player) {
                        selectedMap.set(id, player);
                    }
                }

                if (!player) return;

                const teamInfo = player.teams?.[0] ?
                    `${player.teams[0].category_name} - ${player.teams[0].team_level}` :
                    '';

                html += `
            <div class="basket-player-item">
                <div class="basket-player-info">
                    <div class="basket-player-name">${escapeHtml(player.first_name)} ${escapeHtml(player.name)}</div>
                    ${teamInfo ? `<div class="basket-player-team">${escapeHtml(teamInfo)}</div>` : ''}
                </div>
                <button type="button" 
                        class="basket-remove-btn" 
                        onclick="removeFromBasket(${player.id_user})"
                        title="Retirer de la sélection">
                    ✕ Retirer
                </button>
            </div>
        `;
            });

            container.innerHTML = html;
            updateBasketTitle();
            syncHiddenInputs();
        }

        /**
         * Retire un joueur du panier
         */
        function removeFromBasket(playerId) {
            if (selectedPlayers.has(playerId)) {
                selectedPlayers.delete(playerId);

                // Décocher la checkbox correspondante
                const checkbox = document.querySelector(`#players-container input[value="${playerId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.closest('.player-item')?.classList.remove('selected');
                }

                renderBasket();
            }
        }

        /**
         * Vide complètement le panier
         */
        function clearBasket() {
            if (selectedPlayers.size === 0) {
                alert('ℹ️ La sélection est déjà vide');
                return;
            }

            if (confirm('🗑️ Voulez-vous vraiment vider la sélection de tous les joueurs ?')) {
                selectedPlayers.clear();

                // Décocher toutes les checkboxes
                document.querySelectorAll('#players-container input[type="checkbox"]').forEach(cb => {
                    cb.checked = false;
                    cb.closest('.player-item')?.classList.remove('selected');
                });

                renderBasket();
            }
        }

        /**
         * Sélectionne ou désélectionne tous les joueurs visibles
         */
        function toggleAllPlayers(selectAll) {
            const visibleCheckboxes = document.querySelectorAll('#players-container input[type="checkbox"]:not([style*="display: none"])');

            visibleCheckboxes.forEach(cb => {
                const playerId = parseInt(cb.value);
                cb.checked = selectAll;
                cb.closest('.player-item')?.classList.toggle('selected', selectAll);

                if (selectAll) {
                    selectedPlayers.add(playerId);

                    // Ajouter au map si pas déjà présent
                    if (!selectedMap.has(playerId)) {
                        const player = allPlayersData.find(p => p.id_user == playerId);
                        if (player) {
                            selectedMap.set(playerId, player);
                        }
                    }
                } else {
                    selectedPlayers.delete(playerId);
                }
            });

            renderBasket();
        }

        // ========================================
        // AFFICHAGE DE LA LISTE DES JOUEURS
        // ========================================

        /**
         * Affiche la liste des joueurs filtrée
         */
        function displayPlayers() {
            const container = document.getElementById('players-container');
            const selectedCategory = document.getElementById('filter-category')?.value || '';
            const selectedTeam = document.getElementById('filter-team')?.value || '';

            // Filtrer les joueurs selon les critères
            const filteredPlayers = allPlayersData.filter(player => {
                const matchCategory = selectedCategory ?
                    player.teams.some(t => t.category_name === selectedCategory) :
                    true;
                const matchTeam = selectedTeam ?
                    player.teams.some(t => t.id_team == selectedTeam) :
                    true;
                return matchCategory && matchTeam;
            });

            if (filteredPlayers.length === 0) {
                container.innerHTML = '<div class="no-players">Aucun joueur ne correspond à ces filtres</div>';
                return;
            }

            // Trier par nom
            filteredPlayers.sort((a, b) => a.name.localeCompare(b.name));

            let html = '';

            filteredPlayers.forEach(player => {
                const isChecked = selectedPlayers.has(player.id_user);
                const itemClass = isChecked ? 'selected' : '';

                // Créer les badges des équipes
                const teamBadges = player.teams.map(team =>
                    `<span class="team-badge">${escapeHtml(team.category_name)} - ${escapeHtml(team.team_level)}</span>`
                ).join('');

                html += `
            <div class="player-item ${itemClass}" onclick="togglePlayer(this, ${player.id_user})">
                <input type="checkbox" 
                       value="${player.id_user}" 
                       ${isChecked ? 'checked' : ''}
                       onclick="event.stopPropagation();" 
                       onchange="togglePlayerSelection(this)">
                <div class="player-info">
                    <div class="player-name">${escapeHtml(player.first_name)} ${escapeHtml(player.name)}</div>
                    <div class="player-teams">${teamBadges}</div>
                </div>
            </div>
        `;
            });

            container.innerHTML = html;
        }

        // ========================================
        // INTERACTIONS AVEC LES JOUEURS
        // ========================================

        /**
         * Bascule la sélection d'un joueur (clic sur la ligne)
         */
        function togglePlayer(element, playerId) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            togglePlayerSelection(checkbox);
        }

        /**
         * Gère le changement d'état d'une checkbox
         */
        function togglePlayerSelection(checkbox) {
            const playerId = parseInt(checkbox.value);
            const playerItem = checkbox.closest('.player-item');

            if (checkbox.checked) {
                selectedPlayers.add(playerId);
                playerItem.classList.add('selected');

                // Ajouter le joueur au map
                if (!selectedMap.has(playerId)) {
                    const player = allPlayersData.find(p => p.id_user == playerId);
                    if (player) {
                        selectedMap.set(playerId, player);
                    }
                }
            } else {
                selectedPlayers.delete(playerId);
                playerItem.classList.remove('selected');
            }

            renderBasket();
        }

        /**
         * Met à jour les options du filtre d'équipes selon la catégorie
         */
        function updateTeamFilterOptions() {
            const selectedCategory = document.getElementById('filter-category')?.value || '';
            const teamSelect = document.getElementById('filter-team');

            teamSelect.innerHTML = '<option value="">Toutes les équipes</option>';

            if (selectedCategory && allTeamsByCategory[selectedCategory]) {
                // Afficher uniquement les équipes de la catégorie sélectionnée
                allTeamsByCategory[selectedCategory].forEach(team => {
                    const option = document.createElement('option');
                    option.value = team.id_team;
                    option.textContent = `${team.team_level} - ${team.team_name}`;
                    teamSelect.appendChild(option);
                });
            } else {
                // Afficher toutes les équipes
                Object.keys(allTeamsByCategory).forEach(category => {
                    allTeamsByCategory[category].forEach(team => {
                        const option = document.createElement('option');
                        option.value = team.id_team;
                        option.textContent = `${team.team_level} - ${team.team_name}`;
                        teamSelect.appendChild(option);
                    });
                });
            }
        }

        // ========================================
        // INITIALISATION
        // ========================================

        document.addEventListener('DOMContentLoaded', function() {

            // Afficher les joueurs au chargement
            displayPlayers();

            // Afficher le panier au chargement
            renderBasket();

            // Event listener sur le filtre de catégorie
            document.getElementById('filter-category')?.addEventListener('change', function() {
                updateTeamFilterOptions();
                displayPlayers();
            });

            // Event listener sur le filtre d'équipe
            document.getElementById('filter-team')?.addEventListener('change', function() {
                displayPlayers();
            });

            // Event listener sur l'équipe principale (formulaire)
            document.getElementById('id_team')?.addEventListener('change', function() {
                displayPlayers();
            });

            // Synchroniser les inputs cachés avant la soumission du formulaire
            document.querySelector('form')?.addEventListener('submit', function() {
                syncHiddenInputs();
            });
        });

        //  TOAST SYSTEME (affichage fluide des alertes)
     
        function showToast(message) {
            let toast = document.querySelector('.toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.className = 'toast';
                document.body.appendChild(toast);
            }
            toast.innerHTML = message;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }

        //  Vérification doublon joueur (appel AJAX intégré)
 
        async function checkPlayerDuplicate(playerId, matchDate) {
            if (!matchDate) return false;

            try {
                const response = await fetch(`?section=convocations&ajax=check_player&id_player=${playerId}&match_date=${matchDate}`);
                const data = await response.json();

                if (data.exists) {
                    showToast(`⚠️ ${data.player_name} est déjà convoqué le ${data.match_date} avec ${data.team_name}.`);
                    return true;
                }
            } catch (error) {
                console.error("Erreur vérification convocation:", error);
            }
            return false;
        }

        //  Vérification automatique au clic sur un joueur
        
        document.addEventListener('change', async function(e) {
            if (e.target.matches('input[name="players[]"]')) {
                const checkbox = e.target;
                const playerId = checkbox.value;
                const matchDate = document.getElementById('match_date').value;

                if (checkbox.checked && matchDate) {
                    await checkPlayerDuplicate(playerId, matchDate);
                    //  Laisse le joueur coché — simple avertissement
                }
            }
        });

    </script>

</body>

</html>