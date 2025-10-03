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
$allowed_roles = ['ROLE_ADMIN', 'ROLE_EDITOR'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header('Location: dashboard.php');
    exit;
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Récupérer le chemin du fichier
    $sql = "SELECT file_path FROM medias WHERE id_media = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $media = $result->fetch_assoc();
    
    if ($media) {
        // Supprimer le fichier physique
        $file_path = '../../' . $media['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Supprimer de la BDD
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
    
    header('Location: medias.php');
    exit;
}

// --- UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_media']) && isset($_FILES['media_file'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $file = $_FILES['media_file'];
    
    // Validation
    if (empty($title)) {
        $_SESSION['flash']['danger'] = "Le titre est obligatoire.";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash']['danger'] = "Erreur lors de l'upload du fichier.";
    } else {
        // Vérifier le type de fichier
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $file['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['flash']['danger'] = "Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WEBP.";
        } else {
            // Créer le dossier uploads s'il n'existe pas
            $upload_dir = '../../uploads/medias/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Générer un nom unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('media_') . '.' . $extension;
            $file_path = $upload_dir . $filename;
            
            // Déplacer le fichier
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Enregistrer en BDD
                $db_path = 'uploads/medias/' . $filename;
                $sql = "INSERT INTO medias (title, description, file_path, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssi', $title, $description, $db_path, $file_type, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['flash']['success'] = "Média uploadé avec succès.";
                } else {
                    $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement.";
                    unlink($file_path); // Supprimer le fichier si échec BDD
                }
                $stmt->close();
            } else {
                $_SESSION['flash']['danger'] = "Erreur lors du déplacement du fichier.";
            }
        }
    }
    
    header('Location: medias.php');
    exit;
}

// --- LISTE DES MÉDIAS ---
$sql = "
    SELECT m.*, u.first_name, u.name 
    FROM medias m
    LEFT JOIN users u ON m.uploaded_by = u.id_user
    ORDER BY m.uploaded_at DESC
";
$result = $conn->query($sql);
$medias = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $medias[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Médias - ES Moulon</title>
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
            max-width: 1400px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
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
        .file-input-wrapper {
            position: relative;
            padding: 40px;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s;
        }
        .file-input-wrapper:hover {
            border-color: #1e40af;
            background: #eff6ff;
        }
        .file-input-wrapper input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .media-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .media-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .media-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f3f4f6;
        }
        .media-info {
            padding: 16px;
        }
        .media-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        .media-description {
            color: #6b7280;
            font-size: 0.85rem;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .media-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #9ca3af;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .media-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }
        .stats-bar {
            display: flex;
            gap: 20px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .stat-item {
            flex: 1;
            text-align: center;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e40af;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🖼️ Gestion des Médias</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="dashboard.php" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                ⬆️ Upload un média
            </button>
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

        <!-- STATISTIQUES -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-value"><?= count($medias) ?></div>
                <div class="stat-label">Médias total</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= count(array_filter($medias, function($m) { return strpos($m['file_type'], 'image') !== false; })) ?></div>
                <div class="stat-label">Images</div>
            </div>
        </div>

        <!-- FORMULAIRE UPLOAD -->
        <div class="card" id="formSection" style="display:none;">
            <h2 style="margin-bottom: 20px; color: #1f2937;">⬆️ Upload un nouveau média</h2>
            
            <form method="POST" action="medias.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Titre *</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        placeholder="Ex: Match U15 contre FC Blois"
                        required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        placeholder="Décrivez cette image..."></textarea>
                </div>

                <div class="form-group">
                    <label>Fichier * (JPG, PNG, GIF, WEBP - Max 5 Mo)</label>
                    <div class="file-input-wrapper">
                        <input 
                            type="file" 
                            name="media_file" 
                            accept="image/*" 
                            required
                            onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné'">
                        <div style="font-size: 3rem; margin-bottom: 12px;">📁</div>
                        <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                            Cliquez pour sélectionner un fichier
                        </div>
                        <div id="fileName" style="color: #6b7280; font-size: 0.9rem;">
                            ou glissez-déposez ici
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="upload_media" class="btn btn-success">
                        ⬆️ Upload
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('formSection').style.display='none';">
                        Annuler
                    </button>
                </div>
            </form>
        </div>

        <!-- GALERIE -->
        <div class="card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">Galerie (<?= count($medias) ?>)</h2>
            
            <?php if (empty($medias)): ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">Aucun média pour le moment.</p>
            <?php else: ?>
                <div class="media-grid">
                    <?php foreach ($medias as $media): ?>
                        <div class="media-card">
                            <img 
                                src="<?= htmlspecialchars('../../' . $media['file_path']) ?>" 
                                alt="<?= htmlspecialchars($media['title']) ?>"
                                class="media-image"
                                onclick="window.open('<?= htmlspecialchars('../../' . $media['file_path']) ?>', '_blank')">
                            
                            <div class="media-info">
                                <div class="media-title"><?= htmlspecialchars($media['title']) ?></div>
                                
                                <?php if ($media['description']): ?>
                                    <div class="media-description">
                                        <?= htmlspecialchars($media['description']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="media-meta">
                                    <span>👤 <?= htmlspecialchars($media['first_name'] . ' ' . $media['name']) ?></span>
                                    <span>📅 <?= date('d/m/Y', strtotime($media['uploaded_at'])) ?></span>
                                </div>
                                
                                <div class="media-actions">
                                    <a href="<?= htmlspecialchars('../../' . $media['file_path']) ?>" 
                                       download 
                                       class="btn btn-primary" 
                                       style="flex: 1; text-align: center;">
                                        ⬇️ Télécharger
                                    </a>
                                    <a href="medias.php?delete=<?= $media['id_media'] ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Confirmer la suppression ?')">
                                        🗑️
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>