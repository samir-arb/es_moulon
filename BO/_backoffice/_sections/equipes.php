<?php
require_once __DIR__ . '/../../../includes/config.php';

$adminUrl = $_SERVER['PHP_SELF'] ?? '/es_moulon/BO/admin.php';

// Protection de la page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header("Location: {$adminUrl}?section=login");
    exit;
}

// Vérification des permissions
$allowed_roles = ['ROLE_ADMIN', 'ROLE_SPORT_MANAGER'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header("Location: {$adminUrl}?section=dashboard");
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
        // Vérifier s'il y a des matchs liés
        $check_matches = $conn->prepare("SELECT COUNT(*) as nb FROM matches WHERE id_home_team = ? OR id_away_team = ?");
        $check_matches->bind_param("ii", $id, $id);
        $check_matches->execute();
        $res_matches = $check_matches->get_result()->fetch_assoc();
        $check_matches->close();

        if ($res_matches['nb'] > 0) {
            $_SESSION['flash']['warning'] = "Impossible de supprimer : cette équipe a " . $res_matches['nb'] . " match(s) enregistré(s).";
        } else {
            // Supprimer d'abord les liaisons dans teams_seasons
            $conn->query("DELETE FROM teams_seasons WHERE id_team = $id");
            
            // Puis supprimer l'équipe
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
    }

    header("Location: {$adminUrl}?section=equipes");
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_team'])) {
    $id = isset($_POST['id_team']) ? (int)$_POST['id_team'] : 0;
    $name = trim($_POST['team_name']);
    $id_category = (int)$_POST['id_category'];
    $level = trim($_POST['level']);
    $id_media = !empty($_POST['id_media']) ? (int)$_POST['id_media'] : null;
    $id_team_logo = !empty($_POST['id_team_logo']) ? (int)$_POST['id_team_logo'] : null;
    $id_club_team = isset($_POST['is_opponent']) ? 0 : 1; // Checkbox cochée = adversaire (0), sinon club (1)

    if (empty($name) || $id_category === 0) {
        $_SESSION['flash']['danger'] = "Le nom et la catégorie sont obligatoires.";
    } else {
        if ($id > 0) {
            // MODIFICATION
            $sql = "UPDATE teams SET name = ?, id_category = ?, level = ?, id_media = ?, id_team_logo = ?, id_club_team = ? WHERE id_team = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sisiiii', $name, $id_category, $level, $id_media, $id_team_logo, $id_club_team, $id);
            $message = "Équipe modifiée avec succès.";
        } else {
            // AJOUT
            $sql = "INSERT INTO teams (name, id_category, level, id_media, id_team_logo, id_club_team) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sisiii', $name, $id_category, $level, $id_media, $id_team_logo, $id_club_team);
            $message = "Équipe ajoutée avec succès.";
        }

        if ($stmt->execute()) {
            $team_id = ($id > 0) ? $id : $stmt->insert_id;
            
            // Lier automatiquement à la saison active si c'est un ajout ET une équipe du club
            if ($id === 0 && $id_club_team === 1) {
                $season_query = $conn->query("SELECT id_season FROM seasons WHERE is_active = 1 LIMIT 1");
                if ($season_query && $season_row = $season_query->fetch_assoc()) {
                    $id_season = $season_row['id_season'];
                    $link_sql = "INSERT INTO teams_seasons (id_team, id_season) VALUES (?, ?)";
                    $link_stmt = $conn->prepare($link_sql);
                    $link_stmt->bind_param('ii', $team_id, $id_season);
                    $link_stmt->execute();
                    $link_stmt->close();
                }
            }
            
            $_SESSION['flash']['success'] = $message;
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement : " . $stmt->error;
        }
        $stmt->close();
    }

    header("Location: {$adminUrl}?section=equipes");
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
$edit_team = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "SELECT t.*, m.file_path, m.file_name 
            FROM teams t 
            LEFT JOIN medias m ON t.id_media = m.id_media
            WHERE t.id_team = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_team = $result->fetch_assoc();
    $stmt->close();
}

// --- LISTE DES ÉQUIPES (groupées par catégorie) ---
$filter = $_GET['filter'] ?? 'club';

if ($filter === 'opponents') {
    $sql = "
        SELECT 
            t.id_team, 
            t.name, 
            t.level, 
            COALESCE(c.name, 'Non catégorisé') AS category, 
            t.id_category,
            med.file_path as team_image,
            (SELECT COUNT(*) FROM matches m WHERE m.id_away_team = t.id_team OR m.id_home_team = t.id_team) as nb_matches
        FROM teams t
        LEFT JOIN categories c ON t.id_category = c.id_category
        LEFT JOIN medias med ON t.id_media = med.id_media
        WHERE t.id_club_team = 0
        ORDER BY c.name, t.level, t.name
    ";
} else {
    $sql = "
        SELECT t.id_team, t.name, t.level, c.name AS category, c.id_category,
            COUNT(DISTINCT ucf.id_user) as nb_players,
            med.file_path as team_image,
            logo.file_path as team_logo
        FROM teams t
        JOIN categories c ON t.id_category = c.id_category
        LEFT JOIN users_club_functions ucf ON ucf.id_team = t.id_team AND ucf.id_club_function = 1
        LEFT JOIN medias med ON t.id_media = med.id_media
        LEFT JOIN medias logo ON t.id_team_logo = logo.id_media
        WHERE t.id_club_team = 1
        GROUP BY t.id_team
        ORDER BY c.name, t.level, t.name
    ";
}

$result = $conn->query($sql);
$all_teams = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_teams[] = $row;
    }
}

// Grouper par catégorie
$teams_by_category = [];
foreach ($all_teams as $team) {
    $cat_name = $team['category'];
    if (!isset($teams_by_category[$cat_name])) {
        $teams_by_category[$cat_name] = [];
    }
    $teams_by_category[$cat_name][] = $team;
}

// Récupérer les catégories existantes
$cat_result = $conn->query("SELECT id_category, name FROM categories ORDER BY name");
$categories = [];
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Récupérer les médias pour le select
$medias_query = $conn->query("SELECT id_media, file_name, file_path FROM medias WHERE file_type LIKE 'image/%' ORDER BY uploaded_at DESC");
$medias_list = [];
if ($medias_query) {
    while ($media_item = $medias_query->fetch_assoc()) {
        $medias_list[] = $media_item;
    }
}

// Statistiques
$stats_club = $conn->query("SELECT COUNT(*) as total FROM teams WHERE id_club_team = 1")->fetch_assoc()['total'];
$stats_opponents = $conn->query("SELECT COUNT(*) as total FROM teams WHERE id_club_team = 0")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Équipes - ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_back.css/news.css') ?>">

    <style>
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }
        .filter-tab {
            padding: 12px 24px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 600;
            font-size: 1em;
            color: #6b7280;
            transition: all 0.3s;
            text-decoration: none;
        }
        .filter-tab:hover {
            color: #1e40af;
        }
        .filter-tab.active {
            color: #1e40af;
            border-bottom-color: #1e40af;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        .team-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .team-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .team-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .badge-club {
            background: #dcfce7;
            color: #15803d;
        }
        .badge-opponent {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>

</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>👥 Gestion des Équipes</h1>
                <p style="color:#6b7280;margin-top:4px;">
                    <a href="<?= $adminUrl ?>?section=dashboard" style="color:#1e40af;text-decoration:none;">← Retour au tableau de bord</a>
                </p>
            </div>
            <button class="btn btn-primary" onclick="toggleForm()">➕ Nouvelle équipe</button>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
                <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats_club ?></div>
                <div class="stat-label">Équipes du Club</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#ef4444;"><?= $stats_opponents ?></div>
                <div class="stat-label">Équipes Adversaires</div>
            </div>
        </div>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit_team ? '' : 'display:none;' ?>">
            <h2><?= $edit_team ? '✏️ Modifier l\'équipe' : '➕ Nouvelle équipe' ?></h2>
            
            <form method="POST" action="">
                <input type="hidden" name="save_team" value="1">
                <?php if ($edit_team): ?>
                    <input type="hidden" name="id_team" value="<?= $edit_team['id_team'] ?>">
                <?php endif; ?>
                
                <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
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
                        <input type="text" id="level" name="level" value="<?= $edit_team ? htmlspecialchars($edit_team['level']) : '' ?>" placeholder="Ex: U19, U17, Seniors...">
                    </div>

                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="is_opponent" value="1" 
                                   <?= ($edit_team && $edit_team['id_club_team'] == 0) ? 'checked' : '' ?>>
                            <span>⚠️ Équipe adversaire</span>
                        </label>
                        <small style="color:#6b7280;">Cochez pour les équipes adversaires (n'apparaîtront pas sur le site public)</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_media">📸 Photo d'équipe (présentation)</label>
                    <select id="id_media" name="id_media" onchange="previewImage(this, 'photo')">
                        <option value="">-- Aucune photo --</option>
                        <?php foreach ($medias_list as $media_item): ?>
                            <option value="<?= $media_item['id_media'] ?>"
                                data-img="<?= asset($media_item['file_path']) ?>"
                                <?= ($edit_team && isset($edit_team['id_media']) && $edit_team['id_media'] == $media_item['id_media']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($media_item['file_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="photoPreview" style="margin-top:12px;"></div>
                    <small style="color:#6b7280;display:block;margin-top:8px;">
                        Photo de groupe pour la page de présentation de l'équipe
                    </small>
                </div>

                <div class="form-group">
                    <label for="id_team_logo">🏆 Logo officiel (pour les matchs)</label>
                    <select id="id_team_logo" name="id_team_logo" onchange="previewImage(this, 'logo')">
                        <option value="">-- Aucun logo --</option>
                        <?php foreach ($medias_list as $media_item): ?>
                            <option value="<?= $media_item['id_media'] ?>"
                                data-img="<?= asset($media_item['file_path']) ?>"
                                <?= ($edit_team && isset($edit_team['id_team_logo']) && $edit_team['id_team_logo'] == $media_item['id_media']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($media_item['file_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="logoPreview" style="margin-top:12px;"></div>
                    <small style="color:#6b7280;display:block;margin-top:8px;">
                        Logo utilisé dans les cards de matchs, calendrier et résultats
                    </small>
                </div>

                <div class="form-actions" style="margin-top:24px;">
                    <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                    <a href="?section=equipes" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- FILTRES -->
        <div class="card">
            <div class="filter-tabs">
                <a href="?section=equipes&filter=club" class="filter-tab <?= $filter === 'club' ? 'active' : '' ?>">
                    🏆 Équipes du Club (<?= $stats_club ?>)
                </a>
                <a href="?section=equipes&filter=opponents" class="filter-tab <?= $filter === 'opponents' ? 'active' : '' ?>">
                    ⚔️ Équipes Adversaires (<?= $stats_opponents ?>)
                </a>
            </div>

            <?php if (empty($teams_by_category)): ?>
                <p style="text-align:center;color:#6b7280;padding:40px;">
                    Aucune équipe <?= $filter === 'opponents' ? 'adversaire' : 'du club' ?> pour le moment.
                </p>
            <?php else: ?>
                <?php foreach ($teams_by_category as $category_name => $teams): ?>
                    <div style="margin-bottom: 50px;">
                        <h3 style="font-size:1.8em;color:#1e40af;margin-bottom:25px;padding-bottom:10px;border-bottom:3px solid #1e40af;">
                            📂 <?= htmlspecialchars($category_name) ?> 
                            <span style="color:#6b7280;font-size:0.7em;font-weight:400;">(<?= count($teams) ?> équipe<?= count($teams) > 1 ? 's' : '' ?>)</span>
                        </h3>
                        
                        <div class="grid">
                            <?php foreach ($teams as $team): ?>
                                <div class="team-card">
                                    <span class="team-badge <?= $filter === 'opponents' ? 'badge-opponent' : 'badge-club' ?>">
                                        <?= $filter === 'opponents' ? 'Adversaire' : htmlspecialchars($team['category']) ?>
                                    </span>

                                    <?php if (!empty($team['team_image'])): ?>
                                        <div style="width:100%;height:140px;overflow:hidden;border-radius:8px;margin-bottom:12px;">
                                            <img src="<?= asset($team['team_image']) ?>" 
                                                 alt="<?= htmlspecialchars($team['name']) ?>"
                                                 style="width:100%;height:100%;object-fit:cover;">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="font-weight:700;font-size:1.2em;color:#1f2937;margin-bottom:8px;">
                                        <?= htmlspecialchars($team['name']) ?>
                                    </div>
                                    
                                    <?php if (!empty($team['level'])): ?>
                                        <div style="color:#6b7280;font-size:0.9em;margin-bottom:8px;">
                                            📊 <?= htmlspecialchars($team['level']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="color:#6b7280;margin-bottom:15px;">
                                        <?php if ($filter === 'opponents'): ?>
                                            <strong><?= $team['nb_matches'] ?></strong> match(s)
                                        <?php else: ?>
                                            <strong><?= $team['nb_players'] ?></strong> joueur(s)
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div style="display:flex;gap:8px;">
                                        <a href="?section=equipes&edit=<?= $team['id_team'] ?>" class="btn btn-warning" style="flex:1;">✏️</a>
                                        <?php if ($filter !== 'opponents'): ?>
                                            <a href="?section=joueurs&team=<?= $team['id_team'] ?>" class="btn btn-primary" style="flex:1;">👀 Joueurs</a>
                                        <?php endif; ?>
                                        <?php if ($user_role === 'ROLE_ADMIN'): ?>
                                            <a href="?section=equipes&delete=<?= $team['id_team'] ?>" 
                                               class="btn btn-danger"
                                               onclick="return confirm('Confirmer la suppression ?')">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('formSection');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function previewImage(select, type) {
            const opt = select.options[select.selectedIndex];
            const previewId = type === 'logo' ? 'logoPreview' : 'photoPreview';
            const prev = document.getElementById(previewId);
            
            if (opt.dataset.img) {
                prev.innerHTML = '<img src="' + opt.dataset.img + '" style="max-width:200px;max-height:200px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">';
            } else {
                prev.innerHTML = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Prévisualiser la photo au chargement
            const selectPhoto = document.getElementById('id_media');
            if (selectPhoto && selectPhoto.value) {
                previewImage(selectPhoto, 'photo');
            }
            
            // Prévisualiser le logo au chargement
            const selectLogo = document.getElementById('id_team_logo');
            if (selectLogo && selectLogo.value) {
                previewImage(selectLogo, 'logo');
            }
        });
    </script>
</body>
</html>