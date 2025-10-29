<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../_core/image_optimizer.php'; // Conversion WebP automatique

// Protection de la page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header("Location: ../../_backoffice/auth/login.php");
    exit;
}

// Vérification des permissions
$allowed_roles = ['ROLE_ADMIN', 'ROLE_EDITOR'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header("Location: ../../admin.php?section=dashboard");
    exit;
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// 🛡️ GÉNÉRATION TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupérer toutes les catégories DE MÉDIAS
$categories_query = $conn->query("SELECT * FROM categories_medias ORDER BY display_order ASC");
$categories = [];
while ($cat = $categories_query->fetch_assoc()) {
    $categories[] = $cat;
}

// --- MODIFICATION DU NOM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_media'])) {
    
    // 🛡️ VÉRIFICATION TOKEN CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash']['danger'] = "❌ Token CSRF invalide. Tentative d'attaque détectée !";
        header("Location: ../../admin.php?section=medias");
        exit;
    }
    
    $media_id = (int)$_POST['media_id'];
    $new_name = trim($_POST['new_name']);

    if (!empty($new_name)) {
        $stmt = $conn->prepare("SELECT file_path, file_name FROM medias WHERE id_media = ?");
        $stmt->bind_param('i', $media_id);
        $stmt->execute();
        $media = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($media) {
            $old_path = __DIR__ . '/../../../public/assets/' . $media['file_path'];
            $extension = pathinfo($media['file_name'], PATHINFO_EXTENSION);
            $new_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $new_name) . '.' . $extension;
            $new_db_path = 'uploads/' . $new_filename;
            $new_path = __DIR__ . '/../../../public/assets/' . $new_db_path;

            if (file_exists($new_path) && $new_filename !== $media['file_name']) {
                $_SESSION['flash']['danger'] = "Un fichier avec ce nom existe déjà.";
            } else {
                if (rename($old_path, $new_path)) {
                    $stmt = $conn->prepare("UPDATE medias SET file_name = ?, file_path = ? WHERE id_media = ?");
                    $stmt->bind_param('ssi', $new_filename, $new_db_path, $media_id);
                    if ($stmt->execute()) {
                        $_SESSION['flash']['success'] = "✅ Média renommé avec succès : " . $new_filename;
                    }
                    $stmt->close();
                } else {
                    $_SESSION['flash']['danger'] = "Erreur lors du renommage du fichier.";
                }
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?section=medias" . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''));
    exit;
}

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $sql = "SELECT file_path FROM medias WHERE id_media = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $media = $result->fetch_assoc();

    if ($media) {
        $file_path = __DIR__ . '/../../../public/assets/' . ltrim($media['file_path'], '/');
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $sql = "DELETE FROM medias WHERE id_media = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = "Média supprimé avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
        }
    }
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF'] . "?section=medias");
    exit;
}

// --- UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_media'])) {
    
    // 🛡️ VÉRIFICATION TOKEN CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash']['danger'] = "❌ Token CSRF invalide. Tentative d'attaque détectée !";
        header("Location: ../../admin.php?section=medias");
        exit;
    }
    
    $description = trim($_POST['description'] ?? '');
    $custom_name = trim($_POST['custom_name'] ?? '');
    $id_category_media = !empty($_POST['id_category_media']) ? (int)$_POST['id_category_media'] : null;
    $file = $_FILES['media_file'] ?? null;

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash']['danger'] = "Erreur lors de l'upload du fichier.";
    } else {
        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        $file_type = $file['type'];

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['flash']['danger'] = "Type de fichier non autorisé. Utilisez : JPG, PNG, GIF, WEBP, SVG, PDF, DOC, DOCX, XLS, XLSX.";
        } else {
            $upload_dir = __DIR__ . '/../../../public/assets/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!empty($custom_name)) {
                $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $custom_name) . '.' . $extension;
            } else {
                $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
                $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $original_name) . '_' . time() . '.' . $extension;
            }

            $file_path = $upload_dir . $filename;

            if (file_exists($file_path)) {
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . uniqid() . '.' . $extension;
                $file_path = $upload_dir . $filename;
            }

            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $final_filename = $filename;
                $final_path = $db_path;
                $final_type = $file_type;

                // 🎯 CONVERSION AUTOMATIQUE EN WEBP pour les images
                $image_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($file_type, $image_types)) {
                    // Redimensionner si trop grand (max 1920px)
                    resizeImageIfNeeded($file_path, 1920, 1920);
                    
                    // Convertir en WebP (supprime l'original)
                    $optimization_result = optimizeUploadedImage($file_path, false, 85);
                    
                    if ($optimization_result['success']) {
                        $final_filename = $optimization_result['webp_filename'];
                        $final_path = 'uploads/' . $final_filename;
                        $final_type = 'image/webp';
                        $_SESSION['flash']['success'] = "✅ Image convertie en WebP et optimisée !";
                    } else {
                        // Si échec conversion, on garde l'original
                        $_SESSION['flash']['warning'] = "⚠️ Conversion WebP échouée, fichier original conservé.";
                    }
                } else {
                    // Pour les fichiers non-images (PDF, DOC, etc.)
                    $_SESSION['flash']['success'] = "✅ Fichier uploadé avec succès : " . $filename;
                }

                // Enregistrer en base de données
                $sql = "INSERT INTO medias (file_name, description, file_path, file_type, uploaded_at, id_user, id_category_media) VALUES (?, ?, ?, ?, NOW(), ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssii', $final_filename, $description, $final_path, $final_type, $user_id, $id_category_media);

                if ($stmt->execute()) {
                    if (!isset($_SESSION['flash']['success'])) {
                        $_SESSION['flash']['success'] = "✅ Média uploadé avec succès : " . $final_filename;
                    }
                } else {
                    $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement en BDD.";
                    // Supprimer le fichier si erreur BDD
                    $file_to_delete = $upload_dir . $final_filename;
                    if (file_exists($file_to_delete)) {
                        unlink($file_to_delete);
                    }
                }
                $stmt->close();
            } else {
                $_SESSION['flash']['danger'] = "Erreur lors du déplacement du fichier.";
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?section=medias");
    exit;
}

// --- PAGINATION & RECHERCHE & FILTRE ---
$items_per_page = 24;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Filtre de catégorie
$filter_category = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : null;
$where_clause = $filter_category ? "WHERE m.id_category_media = ?" : "";

// Comptage total
if ($filter_category) {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM medias m $where_clause");
    $stmt_count->bind_param('i', $filter_category);
    $stmt_count->execute();
    $total_medias = $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();
} else {
    $total_result = $conn->query("SELECT COUNT(*) as total FROM medias");
    $total_medias = $total_result->fetch_assoc()['total'];
}

$total_pages = ceil($total_medias / $items_per_page);

// Récupération des médias avec leur catégorie
$sql = "
    SELECT 
        m.*,
        c.name as category_name,
        c.icon as category_icon,
        c.color as category_color
    FROM medias m
    LEFT JOIN categories_medias c ON m.id_category_media = c.id_category_media
    $where_clause
    ORDER BY m.uploaded_at DESC
    LIMIT ? OFFSET ?
";

if ($filter_category) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $filter_category, $items_per_page, $offset);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $items_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$medias = [];
while ($row = $result->fetch_assoc()) {
    $medias[] = $row;
}
$stmt->close();

$is_popup = isset($_GET['popup']) && $_GET['popup'] == '1';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie Médias - ES Moulon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.75rem;
            color: #0f172a;
            font-weight: 700;
        }

        .header-stats {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        .actions-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .btn {
            padding: 9px 18px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
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
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        select {
            padding: 9px 14px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: white;
            font-size: 0.875rem;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
        }

        select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* GRILLE RESPONSIVE */
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .media-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 12px;
            }
        }

        /* CARTE MÉDIA COMPACTE */
        .media-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
            position: relative;
        }

        .media-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .media-preview {
            height: 160px;
            background: #f1f5f9;
            overflow: hidden;
            position: relative;
        }

        .media-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .media-preview img:hover {
            transform: scale(1.05);
        }

        /* BADGE CATÉGORIE SUR L'IMAGE */
        .category-overlay {
            position: absolute;
            top: 8px;
            left: 8px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            color: white;
            font-weight: 600;
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.5);
            z-index: 2;
        }

        .media-info {
            padding: 12px;
        }

        .media-filename {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 10px;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .media-actions {
            display: flex;
            gap: 6px;
            justify-content: space-between;
        }

        /* FORMULAIRE D'UPLOAD */
        #formSection {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        #formSection h2 {
            margin-bottom: 20px;
            color: #0f172a;
            font-size: 1.25rem;
        }

        .form-grid {
            display: grid;
            gap: 18px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.875rem;
            color: #334155;
        }

        input[type="text"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        textarea {
            resize: vertical;
            font-family: inherit;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .modal-close:hover {
            transform: scale(1.1);
        }

        #modalCaption {
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* MODAL RENOMMAGE */
        .modal-dialog {
            background: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: #0f172a;
            font-size: 1.25rem;
        }

        .modal-header .modal-close {
            position: static;
            color: #64748b;
            font-size: 28px;
        }

        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 14px;
            background: white;
            border-radius: 8px;
            text-decoration: none;
            color: #475569;
            font-weight: 600;
            font-size: 0.875rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: #3b82f6;
            color: white;
        }

        .pagination .current {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🖼️ Galerie Médias</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                    <span style="margin-left: 16px;"><?= $total_medias ?> médias</span>
                </p>
            </div>
        </div>

        <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['flash']['success'] ?>
            </div>
            <?php unset($_SESSION['flash']['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash']['danger'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['flash']['danger'] ?>
            </div>
            <?php unset($_SESSION['flash']['danger']); ?>
        <?php endif; ?>

        <div class="actions-bar">
            <button class="btn btn-primary" onclick="toggleUploadForm()">
                📤 Uploader
            </button>
            <a href="?section=reclasser_medias" class="btn btn-warning">
                🗂️ Reclasser
            </a>

            <select onchange="window.location.href='?section=medias&category=' + this.value">
                <option value="">🏷️ Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id_category_media'] ?>" <?= $filter_category == $cat['id_category_media'] ? 'selected' : '' ?>>
                        <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($filter_category): ?>
                <a href="?section=medias" class="btn btn-secondary">❌ Retirer filtre</a>
            <?php endif; ?>
        </div>

        <!-- FORMULAIRE D'UPLOAD -->
        <div id="formSection" style="display: none;">
            <h2>📤 Uploader un nouveau média</h2>
            <form method="POST" enctype="multipart/form-data">
                
                <!-- 🛡️ CHAMP CSRF CACHÉ -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="form-grid">
                    <div>
                        <label>Fichier *</label>
                        <input type="file" name="media_file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" required>
                    </div>

                    <div>
                        <label>Nom personnalisé (optionnel)</label>
                        <input type="text" name="custom_name" placeholder="Ex: logo_es_moulon" pattern="[a-zA-Z0-9_-]+">
                    </div>

                    <div>
                        <label>Catégorie</label>
                        <select name="id_category_media">
                            <option value="">-- Sans catégorie --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id_category_media'] ?>">
                                    <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Description</label>
                        <textarea name="description" rows="2"></textarea>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="upload_media" class="btn btn-primary">✅ Uploader</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleUploadForm()">Annuler</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- GRILLE DES MÉDIAS -->
        <div class="media-grid">
            <?php foreach ($medias as $media): ?>
                <div class="media-card">

                    <div class="media-preview">
                        <?php if ($media['category_name']): ?>
                            <div class="category-overlay" style="background: <?= $media['category_color'] ?>99;">
                                <?= $media['category_icon'] ?> <?= htmlspecialchars($media['category_name']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (strpos($media['file_type'], 'image') !== false): ?>
                            <img src="<?= asset($media['file_path']) ?>"
                                alt="<?= htmlspecialchars($media['file_name']) ?>"
                                onclick="openImageModal('<?= asset($media['file_path']) ?>', '<?= htmlspecialchars($media['file_name']) ?>')">

                        <?php elseif ($media['file_type'] === 'application/pdf'): ?>
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); cursor: pointer;" onclick="window.open('<?= asset($media['file_path']) ?>', '_blank')">
                                <div style="font-size: 3rem; margin-bottom: 8px;">📄</div>
                                <div style="font-size: 0.75rem; font-weight: 600; color: white;">
                                    PDF
                                </div>
                            </div>

                        <?php elseif (strpos($media['file_type'], 'video') !== false): ?>
                            <video controls style="width: 100%; height: 100%; object-fit: contain; background: #000;">
                                <source src="<?= asset($media['file_path']) ?>" type="<?= $media['file_type'] ?>">
                            </video>

                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #f1f5f9; cursor: pointer;" onclick="window.open('<?= asset($media['file_path']) ?>', '_blank')">
                                <div style="font-size: 3rem; margin-bottom: 8px;">📁</div>
                                <div style="font-size: 0.75rem; font-weight: 600; color: #64748b;">
                                    <?= strtoupper(pathinfo($media['file_name'], PATHINFO_EXTENSION)) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="media-info">
                        <div class="media-filename" title="<?= htmlspecialchars($media['file_name']) ?>">
                            <?= htmlspecialchars($media['file_name']) ?>
                        </div>

                        <div class="media-actions">
                            <?php if ($is_popup): ?>
                                <button class="btn btn-primary btn-sm" style="flex: 1;" id="select-btn-<?= $media['id_media'] ?>"
                                    onclick="addToSelection(<?= $media['id_media'] ?>, '<?= htmlspecialchars($media['file_name']) ?>', '<?= asset($media['file_path']) ?>')">
                                    ✓ Sélectionner
                                </button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm" onclick="openRenameModal(<?= $media['id_media'] ?>, '<?= pathinfo($media['file_name'], PATHINFO_FILENAME) ?>')">
                                    ✏️ Renommer
                                </button>
                                <a href="?section=medias&delete=<?= $media['id_media'] ?>&page=<?= $page ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Supprimer ce média ?')">
                                    🗑️
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?section=medias&page=<?= $page - 1 ?><?= $filter_category ? '&category=' . $filter_category : '' ?>">← Préc</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?section=medias&page=<?= $i ?><?= $filter_category ? '&category=' . $filter_category : '' ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?section=medias&page=<?= $page + 1 ?><?= $filter_category ? '&category=' . $filter_category : '' ?>">Suiv →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- MODAL D'APERÇU -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
        <div id="modalCaption"></div>
    </div>

    <!-- MODAL DE RENOMMAGE -->
    <div id="renameModal" class="modal">
        <div class="modal-dialog" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3>✏️ Renommer le média</h3>
                <span class="modal-close" onclick="closeRenameModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="media_id" id="renameMediaId">
                
                <!-- 🛡️ CHAMP CSRF CACHÉ -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div style="margin-bottom: 20px;">
                    <label>Nouveau nom (sans extension)</label>
                    <input type="text"
                        id="new_name"
                        name="new_name"
                        required
                        placeholder="Ex: logo_es_moulon"
                        pattern="[a-zA-Z0-9_-]+"
                        title="Lettres, chiffres, tirets et underscores uniquement">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="rename_media" class="btn btn-primary">✅ Renommer</button>
                    <button type="button" class="btn btn-secondary" onclick="closeRenameModal()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleUploadForm() {
            const form = document.getElementById('formSection');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            if (form.style.display === 'block') {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }

        function addToSelection(mediaId, fileName, filePath) {
            const urlParams = new URLSearchParams(window.location.search);
            const context = urlParams.get('context') || 'news';
            const storageKey = context === 'partners' ? 'partner_media_selection' : 'news_media_selection';

            let selection = JSON.parse(localStorage.getItem(storageKey) || '[]');
            if (!selection.find(item => item.id === mediaId)) {
                selection.push({
                    id: mediaId,
                    name: fileName,
                    path: filePath,
                    timestamp: Date.now()
                });
                localStorage.setItem(storageKey, JSON.stringify(selection));
            }

            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage({
                        action: 'mediaAdded',
                        mediaId,
                        fileName,
                        filePath
                    }, '*');
                }
            } catch (err) {
                console.warn("⚠️ Échec du message au parent :", err);
            }

            const btn = document.getElementById('select-btn-' + mediaId);
            if (btn) {
                btn.textContent = '✅ Sélectionné';
                btn.style.background = '#10b981';
                btn.disabled = true;
            }

            setTimeout(() => {
                try {
                    window.close();
                } catch (e) {}
            }, 300);
        }

        function openImageModal(src, caption) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const modalCaption = document.getElementById('modalCaption');

            modal.style.display = 'flex';
            modalImg.src = src;
            modalCaption.textContent = caption;
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function openRenameModal(mediaId, currentName) {
            document.getElementById('renameMediaId').value = mediaId;
            document.getElementById('new_name').value = currentName;
            document.getElementById('renameModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            setTimeout(() => document.getElementById('new_name').focus(), 100);
        }

        function closeRenameModal() {
            document.getElementById('renameModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeImageModal();
                closeRenameModal();
            }
        });

        <?php if (isset($_GET['popup']) && $_GET['popup'] == '1'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                const context = urlParams.get('context') || 'news';
                const storageKey = context === 'partners' ? 'partner_media_selection' : 'news_media_selection';
                const selection = JSON.parse(localStorage.getItem(storageKey) || '[]');

                if (selection.length > 0) {
                    const header = document.querySelector('.header');
                    const info = document.createElement('div');
                    const contextLabel = context === 'partners' ? 'partenaire(s)' : 'actualité(s)';
                    info.style.cssText = 'background:#10b981;color:white;padding:12px 20px;border-radius:8px;margin-top:10px;font-weight:600;font-size:0.9rem;';
                    info.innerHTML = '✅ ' + selection.length + ' image(s) sélectionnée(s) pour vos ' + contextLabel;
                    header.appendChild(info);
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>