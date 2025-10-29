<?php
require_once __DIR__ . '/../../../includes/config.php';

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

    // Supprimer d'abord la relation users_club_functions
    $stmt = $conn->prepare("DELETE FROM users_club_functions WHERE id_user = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    // Puis supprimer l'utilisateur
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Arbitre supprimé avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }
    $stmt->close();

    header("Location: /es_moulon/BO/admin.php?section=arbitres");
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "
        SELECT u.id_user, u.first_name, u.name, u.id_media, m.file_path, m.file_name
        FROM users u
        INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
        INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
        LEFT JOIN medias m ON u.id_media = m.id_media
        WHERE cf.function_name LIKE '%arbitre%' AND u.id_user = ?
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
    $nom = trim($_POST['nom']);
    $id_media = !empty($_POST['id_media']) ? (int)$_POST['id_media'] : null;

    if ($id > 0) {
        // MODIFICATION
        if ($id_media) {
            $stmt = $conn->prepare("UPDATE users SET first_name=?, name=?, id_media=? WHERE id_user=?");
            $stmt->bind_param('ssii', $prenom, $nom, $id_media, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name=?, name=?, id_media=NULL WHERE id_user=?");
            $stmt->bind_param('ssi', $prenom, $nom, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = "Arbitre modifié avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de la modification.";
        }
        $stmt->close();
    } else {
        // --- AJOUT ---
        // Rôle par défaut : ROLE_LICENSED
        $role_query = $conn->query("SELECT id_role FROM roles WHERE role_name = 'ROLE_LICENSED' LIMIT 1");
        $role_row = $role_query && $role_query->num_rows > 0 ? $role_query->fetch_assoc() : null;
        $id_role = $role_row ? (int)$role_row['id_role'] : 1; // Valeur de secours si non trouvé

        if ($id_media) {
            $stmt = $conn->prepare("INSERT INTO users (first_name, name, id_media, id_role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param('ssii', $prenom, $nom, $id_media, $id_role);
        } else {
            $stmt = $conn->prepare("INSERT INTO users (first_name, name, id_role, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param('ssi', $prenom, $nom, $id_role);
        }

        if ($stmt->execute()) {
            $new_user_id = $stmt->insert_id;
            $stmt->close();

            // Récupérer l'ID de la fonction "Arbitre"
            $func_query = $conn->query("SELECT id_club_function FROM club_functions WHERE function_name LIKE '%arbitre%' LIMIT 1");
            $func_row = $func_query->fetch_assoc();

            if ($func_row) {
                $id_function = $func_row['id_club_function'];

                // Récupérer la saison active
                $season_query = $conn->query("SELECT id_season FROM seasons WHERE is_active = 1 LIMIT 1");
                $season_row = $season_query ? $season_query->fetch_assoc() : null;
                $id_season = $season_row ? $season_row['id_season'] : 1; // Défaut à 1 si aucune saison active

                // Date de début (aujourd'hui)
                $start_date = date('Y-m-d');

                // Insérer la relation arbitre <-> fonction <-> saison
                $stmt = $conn->prepare("INSERT INTO users_club_functions (id_user, id_club_function, id_season, start_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('iiis', $new_user_id, $id_function, $id_season, $start_date);
                $stmt->execute();
                $stmt->close();
            }

            $_SESSION['flash']['success'] = "Nouvel arbitre ajouté avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'ajout : " . $stmt->error;
        }
    }


    header("Location: /es_moulon/BO/admin.php?section=arbitres");
    exit;
}

// --- LISTE DES ARBITRES ---
$sql = "
    SELECT u.id_user, u.first_name, u.name, u.id_media, m.file_path, m.file_name
    FROM users u
    INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
    INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
    LEFT JOIN medias m ON u.id_media = m.id_media
    WHERE cf.function_name LIKE '%arbitre%'
    ORDER BY u.name ASC
";
$res = $conn->query($sql);
$arbitres = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>


    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* HEADER - Même style que joueurs.php */
        .header {
            background: white;
            padding: 24px 32px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .header p {
            margin: 8px 0 0 0;
            font-size: 0.95rem;
        }

        .header p a {
            color: #1e40af;
            text-decoration: none;
            font-weight: 500;
        }

        .header p a:hover {
            text-decoration: underline;
        }

        /* ALERTS */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-weight: 500;
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

        /* CARD */
        .card {
            background: white;
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 24px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* FORM */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            font-size: 0.95rem;
        }

        .form-group input[type="text"],
        .form-group select {
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .form-group small {
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 6px;
        }

        .form-group small a {
            color: #1e40af;
            font-weight: 600;
            text-decoration: none;
        }

        .form-group small a:hover {
            text-decoration: underline;
        }

        /* IMAGE PREVIEW */
        #imagePreview {
            margin-top: 12px;
        }

        #imagePreview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #e5e7eb;
        }

        /* BUTTONS */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-primary {
            background: #1e40af;
            color: white;
        }

        .btn-primary:hover {
            background: #1e3a8a;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        /* LISTE ARBITRES */
        .arbitres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .arbitre-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .arbitre-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .arbitre-header {
            display: flex;
            align-items: start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .arbitre-photo {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #1e40af;
            flex-shrink: 0;
        }

        .arbitre-photo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            flex-shrink: 0;
        }

        .arbitre-info {
            flex: 1;
            min-width: 0;
        }

        .arbitre-name {
            font-weight: 700;
            color: #1f2937;
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .arbitre-firstname {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .arbitre-badge {
            display: inline-block;
            background: #1e40af;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 8px;
        }

        .arbitre-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .arbitre-actions .btn {
            padding: 8px 16px;
            font-size: 0.85rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state span {
            font-size: 3rem;
            display: block;
            margin-bottom: 16px;
        }

        .stats-badge {
            background: #1e40af;
            color: white;
            padding: 6px 16px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-left: 12px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .arbitres-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div>
                <h1>🎯 Gestion des Arbitres</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <?php if (!$edit): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouvel arbitre
                </button>
            <?php endif; ?>
        </div>

        <!-- ALERTS -->
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endforeach;
            unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit ? '' : 'display:none;' ?>">
            <h2><?= $edit ? "✏️ Modifier un Arbitre" : "➕ Ajouter un Arbitre" ?></h2>
            <form method="post" action="/es_moulon/BO/admin.php?section=arbitres">
                <?php if ($edit): ?>
                    <input type="hidden" name="id_user" value="<?= $edit['id_user'] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom"
                            value="<?= $edit ? htmlspecialchars($edit['first_name']) : '' ?>"
                            required placeholder="Entrez le prénom">
                    </div>

                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom"
                            value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>"
                            required placeholder="Entrez le nom">
                    </div>
                </div>

                <!-- SÉLECTION D'IMAGE -->
                <div class="form-group">
                    <label for="id_media">Photo de l'arbitre</label>
                    <select id="id_media" name="id_media" onchange="previewImage(this)">
                        <option value="">-- Aucune photo --</option>
                        <?php
                        $medias_query = $conn->query("SELECT id_media, file_name, file_path FROM medias ORDER BY uploaded_at DESC");
                        if ($medias_query) {
                            while ($media_item = $medias_query->fetch_assoc()):
                        ?>
                                <option value="<?= $media_item['id_media'] ?>"
                                    data-url="<?= htmlspecialchars(asset($media_item['file_path'])) ?>"
                                    <?= ($edit && isset($edit['id_media']) && $edit['id_media'] == $media_item['id_media']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($media_item['file_name']) ?>
                                </option>
                        <?php
                            endwhile;
                        }
                        ?>
                    </select>
                    <small>
                        Pas d'image ? <a href="/es_moulon/BO/admin.php?section=medias" target="_blank">Uploadez-en une ici</a>
                    </small>

                    <!-- Prévisualisation de l'image -->
                    <div id="imagePreview">
                        <?php if ($edit && !empty($edit['file_path'])): ?>
                            <img src="<?= htmlspecialchars(asset($edit['file_path'])) ?>" alt="Photo actuelle" style="max-width:200px;max-height:200px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);border:2px solid #e5e7eb;margin-top:12px;">
                        <?php endif; ?>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" name="save_arbitre" class="btn btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="/es_moulon/BO/admin.php?section=arbitres" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- LISTE DES ARBITRES -->
        <div class="card">
            <h2>
                📋 Liste des Arbitres
                <span class="stats-badge"><?= count($arbitres) ?> arbitre<?= count($arbitres) > 1 ? 's' : '' ?></span>
            </h2>

            <?php if (empty($arbitres)): ?>
                <div class="empty-state">
                    <span>🎽</span>
                    <p>Aucun arbitre enregistré pour le moment.</p>
                    <p>Ajoutez le premier arbitre officiel de l'ES Moulon !</p>
                </div>
            <?php else: ?>
                <div class="arbitres-grid">
                    <?php foreach ($arbitres as $arbitre): ?>
                        <div class="arbitre-card">
                            <div class="arbitre-header">
                                <?php if (!empty($arbitre['file_path'])): ?>
                                    <img src="<?= asset($arbitre['file_path']) ?>"

                                        alt="Photo de <?= htmlspecialchars($arbitre['first_name'] . ' ' . $arbitre['name']) ?>"
                                        class="arbitre-photo"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="arbitre-photo-placeholder" style="display:none;">🎯</div>
                                <?php else: ?>
                                    <div class="arbitre-photo-placeholder">🎯</div>
                                <?php endif; ?>

                                <div class="arbitre-info">
                                    <div class="arbitre-name"><?= htmlspecialchars($arbitre['name']) ?></div>
                                    <div class="arbitre-firstname"><?= htmlspecialchars($arbitre['first_name']) ?></div>
                                    <span class="arbitre-badge">Arbitre Officiel</span>
                                </div>
                            </div>

                            <div class="arbitre-actions">
                                <a href="/es_moulon/BO/admin.php?section=arbitres&edit=<?= $arbitre['id_user'] ?>"
                                    class="btn btn-warning">
                                    ✏️ Modifier
                                </a>
                                <a href="/es_moulon/BO/admin.php?section=arbitres&delete=<?= $arbitre['id_user'] ?>"
                                    class="btn btn-danger"
                                    onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet arbitre ?')">
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
        function previewImage(select) {
            const opt = select.options[select.selectedIndex];
            const url = opt?.dataset?.url || '';
            const preview = document.getElementById('imagePreview');

            if (url) {
                preview.innerHTML =
                    '<img src="' + url + '" alt="aperçu" ' +
                    'style="max-width:200px;max-height:200px;border-radius:8px;' +
                    'box-shadow:0 2px 8px rgba(0,0,0,0.1);border:2px solid #e5e7eb;' +
                    'margin-top:12px;">';
            } else {
                preview.innerHTML = '';
            }
        }

        // Afficher l'aperçu au chargement si une image est déjà sélectionnée
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('id_media');
            if (select && select.value) {
                previewImage(select);
            }
        });
    </script>



</body>

</html>