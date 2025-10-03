<?php
session_start();
require '../../includes/config.php';

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
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $sql = "DELETE FROM convocations WHERE id_convocation = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Convocation supprimée avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }
    $stmt->close();
    
    header('Location: convocations.php');
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_convocation'])) {
    $id = isset($_POST['id_convocation']) ? (int)$_POST['id_convocation'] : 0;
    $id_team = (int)$_POST['id_team'];
    $match_date = $_POST['match_date'];
    $match_time = $_POST['match_time'];
    $opponent = trim($_POST['opponent']);
    $location = trim($_POST['location']);
    $meeting_time = isset($_POST['meeting_time']) ? $_POST['meeting_time'] : null;
    $notes = trim($_POST['notes']);
    
    // Validation
    if ($id_team === 0 || empty($match_date) || empty($opponent)) {
        $_SESSION['flash']['danger'] = "Les champs obligatoires doivent être remplis.";
    } else {
        if ($id > 0) {
            // MODIFICATION
            $sql = "UPDATE convocations SET id_team = ?, match_date = ?, match_time = ?, opponent = ?, location = ?, meeting_time = ?, notes = ? WHERE id_convocation = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('issssssi', $id_team, $match_date, $match_time, $opponent, $location, $meeting_time, $notes, $id);
            $message = "Convocation modifiée avec succès.";
        } else {
            // AJOUT
            $sql = "INSERT INTO convocations (id_team, match_date, match_time, opponent, location, meeting_time, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $user_id = $_SESSION['user_id'];
            $stmt->bind_param('issssssi', $id_team, $match_date, $match_time, $opponent, $location, $meeting_time, $notes, $user_id);
            $message = "Convocation créée avec succès.";
        }
        
        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $message;
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement.";
        }
        $stmt->close();
    }
    
    header('Location: convocations.php');
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
$edit_convocation = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "SELECT * FROM convocations WHERE id_convocation = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_convocation = $result->fetch_assoc();
    $stmt->close();
}

// --- LISTE DES CONVOCATIONS ---
$sql = "
    SELECT c.*, t.team_name, t.category, u.first_name, u.name
    FROM convocations c
    INNER JOIN teams t ON c.id_team = t.id_team
    LEFT JOIN users u ON c.created_by = u.id_user
    ORDER BY c.match_date DESC, c.match_time DESC
";
$result = $conn->query($sql);
$convocations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $convocations[] = $row;
    }
}

// Liste des équipes
$teams_list = [];
$teams_result = $conn->query("SELECT id_team, team_name, category FROM teams ORDER BY category, team_name");
if ($teams_result) {
    while ($row = $teams_result->fetch_assoc()) {
        $teams_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Convocations - ES Moulon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #1f2937;
            font-size: 1.5rem;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .btn-primary {
            background: #1e40af;
            color: white;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1e40af;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .convocation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .convocation-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid #f59e0b;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .convocation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .convocation-card.past {
            border-left-color: #9ca3af;
            opacity: 0.7;
        }
        .convocation-card.upcoming {
            border-left-color: #10b981;
        }
        .convocation-header {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .convocation-date {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .convocation-team {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .convocation-body {
            margin-bottom: 16px;
        }
        .convocation-opponent {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .convocation-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 6px;
        }
        .convocation-notes {
            background: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #374151;
            margin-top: 12px;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .badge-status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-upcoming {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-past {
            background: #f3f4f6;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📅 Gestion des Convocations</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="dashboard.php" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <?php if (!$edit_convocation): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouvelle convocation
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
        <div class="card" id="formSection" style="<?= $edit_convocation ? '' : 'display:none;' ?>">
            <h2 style="margin-bottom: 20px; color: #1f2937;">
                <?= $edit_convocation ? '✏️ Modifier la convocation' : '➕ Nouvelle convocation' ?>
            </h2>
            
            <form method="POST" action="convocations.php">
                <?php if ($edit_convocation): ?>
                    <input type="hidden" name="id_convocation" value="<?= $edit_convocation['id_convocation'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_team">Équipe *</label>
                        <select id="id_team" name="id_team" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($teams_list as $team): ?>
                                <option value="<?= $team['id_team'] ?>" 
                                    <?= ($edit_convocation && $edit_convocation['id_team'] == $team['id_team']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team['category'] . ' - ' . $team['team_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="opponent">Adversaire *</label>
                        <input 
                            type="text" 
                            id="opponent" 
                            name="opponent" 
                            value="<?= $edit_convocation ? htmlspecialchars($edit_convocation['opponent']) : '' ?>" 
                            placeholder="Ex: FC Blois"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="match_date">Date du match *</label>
                        <input 
                            type="date" 
                            id="match_date" 
                            name="match_date" 
                            value="<?= $edit_convocation ? $edit_convocation['match_date'] : '' ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="match_time">Heure du match</label>
                        <input 
                            type="time" 
                            id="match_time" 
                            name="match_time" 
                            value="<?= $edit_convocation ? $edit_convocation['match_time'] : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="meeting_time">Heure de convocation</label>
                        <input 
                            type="time" 
                            id="meeting_time" 
                            name="meeting_time" 
                            value="<?= $edit_convocation ? $edit_convocation['meeting_time'] : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="location">Lieu</label>
                        <input 
                            type="text" 
                            id="location" 
                            name="location" 
                            value="<?= $edit_convocation ? htmlspecialchars($edit_convocation['location']) : '' ?>" 
                            placeholder="Ex: Stade municipal">
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Notes / Instructions</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            placeholder="Informations complémentaires pour les joueurs..."><?= $edit_convocation ? htmlspecialchars($edit_convocation['notes']) : '' ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_convocation" class="btn btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="convocations.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- LISTE -->
        <div class="card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">Convocations (<?= count($convocations) ?>)</h2>
            
            <?php if (empty($convocations)): ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">Aucune convocation pour le moment.</p>
            <?php else: ?>
                <div class="convocation-grid">
                    <?php 
                    $today = date('Y-m-d');
                    foreach ($convocations as $conv): 
                        $is_upcoming = $conv['match_date'] >= $today;
                        $status_class = $is_upcoming ? 'upcoming' : 'past';
                    ?>
                        <div class="convocation-card <?= $status_class ?>">
                            <div class="convocation-header">
                                <div class="convocation-date">
                                    📅 <?= date('d/m/Y', strtotime($conv['match_date'])) ?>
                                    <?php if ($conv['match_time']): ?>
                                        à <?= substr($conv['match_time'], 0, 5) ?>
                                    <?php endif; ?>
                                </div>
                                <span class="convocation-team">
                                    <?= htmlspecialchars($conv['category'] . ' - ' . $conv['team_name']) ?>
                                </span>
                                <span class="badge-status badge-<?= $is_upcoming ? 'upcoming' : 'past' ?>">
                                    <?= $is_upcoming ? 'À venir' : 'Passé' ?>
                                </span>
                            </div>
                            
                            <div class="convocation-body">
                                <div class="convocation-opponent">
                                    ⚔️ VS <?= htmlspecialchars($conv['opponent']) ?>
                                </div>
                                
                                <?php if ($conv['meeting_time']): ?>
                                    <div class="convocation-info">
                                        ⏰ RDV: <?= substr($conv['meeting_time'], 0, 5) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($conv['location']): ?>
                                    <div class="convocation-info">
                                        📍 <?= htmlspecialchars($conv['location']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($conv['notes']): ?>
                                    <div class="convocation-notes">
                                        💬 <?= nl2br(htmlspecialchars($conv['notes'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="actions">
                                <a href="convocations.php?edit=<?= $conv['id_convocation'] ?>" class="btn btn-warning">
                                    ✏️ Modifier
                                </a>
                                <a href="convocations.php?delete=<?= $conv['id_convocation'] ?>" 
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