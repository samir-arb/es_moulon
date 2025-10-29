<?php
require_once __DIR__ . '/../../../includes/config.php';

// Protection de la page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: login.php');
    exit;
}

// Vérification des permissions
$allowed_roles = ['ROLE_ADMIN', 'ROLE_EDITOR'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header('Location: dashboard.php');
    exit;
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// --- MISE À JOUR PHOTO HERO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hero'])) {
    $hero_title = trim($_POST['hero_title'] ?? '');
    $hero_subtitle = trim($_POST['hero_subtitle'] ?? '');
    $hero_lead = trim($_POST['hero_lead'] ?? '');
    $id_hero_media = !empty($_POST['id_hero_media']) ? (int)$_POST['id_hero_media'] : null;

    // Vérifier si une ligne existe déjà
    $check_sql = "SELECT id_site_setting FROM site_settings LIMIT 1";
    $check_result = $conn->query($check_sql);

    if ($check_result && $check_result->num_rows > 0) {
        // UPDATE - Mise à jour de la ligne existante
        $sql = "UPDATE site_settings SET hero_title = ?, hero_subtitle = ?, hero_lead = ?, id_hero_media = ? WHERE id_site_setting = 1";
        $stmt = $conn->prepare($sql);
        if ($id_hero_media) {
            $stmt->bind_param('sssi', $hero_title, $hero_subtitle, $hero_lead, $id_hero_media);
        } else {
            $stmt->bind_param('ssss', $hero_title, $hero_subtitle, $hero_lead, $id_hero_media);
        }
    } else {
        // INSERT - Première configuration
        $sql = "INSERT INTO site_settings (hero_title, hero_subtitle, hero_lead, id_hero_media) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($id_hero_media) {
            $stmt->bind_param('sssi', $hero_title, $hero_subtitle, $hero_lead, $id_hero_media);
        } else {
            $stmt->bind_param('ssss', $hero_title, $hero_subtitle, $hero_lead, $id_hero_media);
        }
    }

    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "✅ Configuration Hero mise à jour avec succès !";
    } else {
        $_SESSION['flash']['danger'] = "❌ Erreur lors de la mise à jour.";
    }
    $stmt->close();

    header('Location: admin.php?section=home');
    exit;
}

// --- RÉCUPÉRATION CONFIG HERO ---
$hero_config = null;
$hero_sql = "SELECT hero_title, hero_subtitle, hero_lead, id_hero_media FROM site_settings LIMIT 1";
$hero_result = $conn->query($hero_sql);
if ($hero_result && $hero_result->num_rows > 0) {
    $row = $hero_result->fetch_assoc();
    $hero_config = [
        'title' => $row['hero_title'],
        'subtitle' => $row['hero_subtitle'],
        'lead' => $row['hero_lead'],
        'id_media' => $row['id_hero_media']
    ];
}

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $sql = "DELETE FROM home_blocks WHERE id_home_block = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Bloc supprimé avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }
    $stmt->close();

    header('Location: admin.php?section=home');
    exit;
}

// --- CHANGEMENT DE STATUT ---
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];

    $sql = "UPDATE home_blocks SET status = 1 - status WHERE id_home_block = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    header('Location: admin.php?section=home');
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_block'])) {
    $id = isset($_POST['id_home_block']) ? (int)$_POST['id_home_block'] : 0;
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $button_link = trim($_POST['button_link']);
    $block_type = $_POST['block_type'];
    $display_order = (int)$_POST['display_order'];
    $status = isset($_POST['status']) ? 1 : 0;
    $id_media = !empty($_POST['id_media']) ? (int)$_POST['id_media'] : null;

    // Validation
    if (empty($title) || empty($block_type)) {
        $_SESSION['flash']['danger'] = "Le titre et le type de bloc sont obligatoires.";
    } else {
        if ($id > 0) {
            // MODIFICATION
            $sql = "UPDATE home_blocks SET title = ?, content = ?, button_link = ?, block_type = ?, display_order = ?, status = ?, id_media = ?, updated_at = NOW() WHERE id_home_block = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssiiii', $title, $content, $button_link, $block_type, $display_order, $status, $id_media, $id);
            $message = "Bloc modifié avec succès.";
        } else {
            // AJOUT
            $sql = "INSERT INTO home_blocks (title, content, button_link, block_type, display_order, status, id_user, id_media) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssiioi', $title, $content, $button_link, $block_type, $display_order, $status, $user_id, $id_media);
            $message = "Bloc ajouté avec succès.";
        }

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $message;
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement.";
        }
        $stmt->close();
    }

    header('Location: admin.php?section=home');
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
$edit_block = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "SELECT * FROM home_blocks WHERE id_home_block = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_block = $result->fetch_assoc();
    $stmt->close();
}

// --- LISTE DES BLOCS ---
$sql = "
    SELECT hb.*, u.first_name, u.name, m.file_name as media_file_name
    FROM home_blocks hb
    LEFT JOIN users u ON hb.id_user = u.id_user
    LEFT JOIN medias m ON hb.id_media = m.id_media
    ORDER BY hb.display_order ASC, hb.id_home_block DESC
";
$result = $conn->query($sql);
$blocks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $blocks[] = $row;
    }
}

// Liste des médias pour le formulaire GROUPÉS PAR CATÉGORIE
$medias_by_category = [];
$medias_result = $conn->query("
    SELECT m.id_media, m.file_name, m.file_path, c.name as category_name, c.id_category_media
    FROM medias m
    LEFT JOIN categories_medias c ON m.id_category_media = c.id_category_media
    WHERE c.is_active = 1
    ORDER BY c.display_order ASC, m.uploaded_at DESC
");
if ($medias_result) {
    while ($row = $medias_result->fetch_assoc()) {
        $cat_name = $row['category_name'] ?? 'Non classé';
        if (!isset($medias_by_category[$cat_name])) {
            $medias_by_category[$cat_name] = [];
        }
        $medias_by_category[$cat_name][] = $row;
    }
}

// Types de blocs disponibles
$block_types = [
    'resultats' => 'Résultats',
    'actualites' => 'Actualités',
    'club' => 'Présentation du club',
    'partner' => 'Partenaires'
];
?>

<div class="container">
    <div class="header">
        <div>
            <h1>🏠 Gestion de la Page d'Accueil</h1>
            <p style="color: #6b7280; margin-top: 4px;">
                <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
            </p>
        </div>
        <?php if (!$edit_block): ?>
            <button class="btn btn-primary" onclick="
                document.getElementById('formSection').style.display='block'; 
                document.getElementById('formSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
            ">
                ➕ Nouveau bloc
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

    <!-- GESTION PHOTO HERO -->
    <div class="card">
        <h2 style="margin-bottom: 20px; color: #1f2937;">🎨 Photo Hero (Bannière d'accueil)</h2>
        <p style="color: #6b7280; margin-bottom: 20px;">
            Configurez l'image et les textes de la grande bannière en haut de la page d'accueil.
        </p>

        <form method="POST" action="admin.php?section=home">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="hero_title">Titre Hero</label>
                    <input
                        type="text"
                        id="hero_title"
                        name="hero_title"
                        value="<?= $hero_config ? htmlspecialchars($hero_config['title'] ?? '') : '' ?>"
                        placeholder="Ex: Bienvenue sur le site"
                        required>
                </div>

                <div class="form-group full-width">
                    <label for="hero_subtitle">Sous-titre Hero</label>
                    <input
                        type="text"
                        id="hero_subtitle"
                        name="hero_subtitle"
                        value="<?= $hero_config ? htmlspecialchars($hero_config['subtitle'] ?? '') : '' ?>"
                        placeholder="Ex: E S Moulon"
                        required>
                </div>

                <div class="form-group full-width">
                    <label for="hero_lead">Texte d'introduction</label>
                    <textarea
                        id="hero_lead"
                        name="hero_lead"
                        rows="3"
                        placeholder="Ex: Depuis 1940, notre club s'engage à développer le football local et à promouvoir les valeurs du sport."><?= $hero_config ? htmlspecialchars($hero_config['lead'] ?? '') : '' ?></textarea>
                    <small style="color: #6b7280; font-size: 0.85rem;">
                        💡 Ce texte s'affiche sous le titre principal
                    </small>
                </div>

                <div class="form-group full-width">
                    <label for="id_hero_media">Image de fond Hero</label>
                    <select id="id_hero_media" name="id_hero_media" size="10" style="height: 250px; font-family: monospace;">
                        <option value="">-- Aucune image (fond par défaut) --</option>
                        <?php foreach ($medias_by_category as $category => $medias): ?>
                            <optgroup label="📁 <?= htmlspecialchars($category) ?>">
                                <?php foreach ($medias as $media): ?>
                                    <option value="<?= $media['id_media'] ?>"
                                        <?= ($hero_config && isset($hero_config['id_media']) && $hero_config['id_media'] == $media['id_media']) ? 'selected' : '' ?>>
                                        📷 <?= htmlspecialchars($media['file_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #6b7280; font-size: 0.85rem;">
                        💡 Conseil : Utilisez une image en format paysage (1920x600px minimum)
                    </small>
                </div>

                <!-- Prévisualisation de l'image sélectionnée -->
                <div class="form-group full-width" id="hero_preview" style="display: none;">
                    <label>Aperçu de l'image sélectionnée :</label>
                    <img id="hero_preview_img" src="" alt="Aperçu" style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="save_hero" class="btn btn-success">
                    💾 Enregistrer la configuration Hero
                </button>
            </div>
        </form>
    </div>

    <script>
    // Prévisualisation de l'image Hero en temps réel
    (function() {
        const select = document.getElementById('id_hero_media');
        const preview = document.getElementById('hero_preview');
        const previewImg = document.getElementById('hero_preview_img');
        
        // Données des médias pour prévisualisation
        const mediasData = <?= json_encode(array_merge(...array_values($medias_by_category))) ?>;
        
        select.addEventListener('change', function() {
            const selectedId = parseInt(this.value);
            
            if (!selectedId) {
                preview.style.display = 'none';
                return;
            }
            
            const media = mediasData.find(m => m.id_media == selectedId);
            if (media && media.file_path) {
                previewImg.src = '<?= asset('uploads/') ?>' + media.file_path;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Afficher l'aperçu au chargement si une image est déjà sélectionnée
        if (select.value) {
            select.dispatchEvent(new Event('change'));
        }
    })();
    </script>

    <div class="info-box">
        <h3>💡 Comment ça fonctionne ?</h3>
        <p>
            Créez des blocs de contenu qui s'afficheront sur la page d'accueil de votre site.<br>
            <strong>Types de blocs disponibles :</strong> Résultats récents, Actualités, Présentation du club, Partenaires.<br>
            <strong>Ordre d'affichage :</strong> Les blocs sont affichés du plus petit au plus grand numéro d'ordre.
        </p>
    </div>

    <!-- FORMULAIRE -->
    <div class="card" id="formSection" style="<?= $edit_block ? '' : 'display:none;' ?>">
        <h2 style="margin-bottom: 20px; color: #1f2937;">
            <?= $edit_block ? '✏️ Modifier le bloc' : '➕ Nouveau bloc' ?>
        </h2>

        <form method="POST" action="admin.php?section=home">
            <?php if ($edit_block): ?>
                <input type="hidden" name="id_home_block" value="<?= $edit_block['id_home_block'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Titre du bloc *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= $edit_block ? htmlspecialchars($edit_block['title']) : '' ?>"
                        placeholder="Ex: Derniers résultats"
                        required>
                </div>

                <div class="form-group">
                    <label for="block_type">Type de bloc *</label>
                    <select id="block_type" name="block_type" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($block_types as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($edit_block && $edit_block['block_type'] === $key) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="display_order">Ordre d'affichage *</label>
                    <input
                        type="number"
                        id="display_order"
                        name="display_order"
                        value="<?= $edit_block ? $edit_block['display_order'] : '1' ?>"
                        min="1"
                        required>
                    <small style="color: #6b7280; font-size: 0.85rem;">1 = premier bloc affiché</small>
                </div>

                <div class="form-group">
                    <label for="id_media">Image associée (optionnel)</label>
                    <select id="id_media" name="id_media" size="8" style="height: 200px; font-family: monospace;">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($medias_by_category as $category => $medias): ?>
                            <optgroup label="📁 <?= htmlspecialchars($category) ?>">
                                <?php foreach ($medias as $media): ?>
                                    <option value="<?= $media['id_media'] ?>"
                                        <?= ($edit_block && $edit_block['id_media'] == $media['id_media']) ? 'selected' : '' ?>>
                                        📷 <?= htmlspecialchars($media['file_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="content">Contenu</label>
                    <textarea
                        id="content"
                        name="content"
                        placeholder="Texte descriptif du bloc..."><?= $edit_block ? htmlspecialchars($edit_block['content']) : '' ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label for="button_link">Lien du bouton (optionnel)</label>
                    <input
                        type="text"
                        id="button_link"
                        name="button_link"
                        value="<?= $edit_block ? htmlspecialchars($edit_block['button_link']) : '' ?>"
                        placeholder="Ex: resultats.php ou https://...">
                </div>

                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="checkbox-wrapper">
                        <input
                            type="checkbox"
                            id="status"
                            name="status"
                            <?= ($edit_block && $edit_block['status'] == 1) || !$edit_block ? 'checked' : '' ?>>
                        <label for="status" style="margin: 0; font-weight: normal;">Bloc actif (visible sur le site)</label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="save_block" class="btn btn-success">
                    💾 Enregistrer
                </button>
                <a href="admin.php?section=home" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

    <!-- LISTE DES BLOCS -->
    <div class="card">
        <h2 style="margin-bottom: 20px; color: #1f2937;">Blocs de la page d'accueil (<?= count($blocks) ?>)</h2>

        <?php if (empty($blocks)): ?>
            <p style="text-align: center; color: #6b7280; padding: 40px;">Aucun bloc pour le moment. Créez votre premier bloc !</p>
        <?php else: ?>
            <div class="blocks-grid">
                <?php foreach ($blocks as $block): ?>
                    <div class="block-item <?= $block['status'] == 0 ? 'inactive' : '' ?>">
                        <div class="block-content">
                            <div class="block-header">
                                <span class="block-order">Ordre: <?= $block['display_order'] ?></span>
                                <h3 class="block-title"><?= htmlspecialchars($block['title']) ?></h3>
                                <span class="badge badge-type"><?= $block_types[$block['block_type']] ?></span>
                                <?php if ($block['status'] == 1): ?>
                                    <span class="badge badge-active">✓ Actif</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">✗ Inactif</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($block['content']): ?>
                                <div class="block-description">
                                    <?= htmlspecialchars(substr($block['content'], 0, 200)) . (strlen($block['content']) > 200 ? '...' : '') ?>
                                </div>
                            <?php endif; ?>

                            <div class="block-meta">
                                <?php if ($block['button_link']): ?>
                                    <span>🔗 Lien: <?= htmlspecialchars($block['button_link']) ?></span>
                                <?php endif; ?>
                                <?php if ($block['media_title']): ?>
                                    <span>🖼️ Image: <?= htmlspecialchars($block['media_title']) ?></span>
                                <?php endif; ?>
                                <span>👤 <?= htmlspecialchars($block['first_name'] . ' ' . $block['name']) ?></span>
                            </div>
                        </div>

                        <div class="block-actions">
                            <a href="admin.php?section=home&edit=<?= $block['id_home_block'] ?>" class="btn btn-warning">
                                ✏️ Modifier
                            </a>
                            <a href="admin.php?section=home&toggle_status=<?= $block['id_home_block'] ?>"
                                class="btn <?= $block['status'] == 1 ? 'btn-secondary' : 'btn-success' ?>">
                                <?= $block['status'] == 1 ? '⏸️ Désactiver' : '▶️ Activer' ?>
                            </a>
                            <a href="admin.php?section=home&delete=<?= $block['id_home_block'] ?>"
                                class="btn btn-danger"
                                onclick="return confirm('Confirmer la suppression de ce bloc ?')">
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