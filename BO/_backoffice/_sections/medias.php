<?php
require_once __DIR__ . '/../../../includes/config.php';

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

// --- MODIFICATION DU NOM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_media'])) {
    $media_id = (int)$_POST['media_id'];
    $new_name = trim($_POST['new_name']);
    
    if (!empty($new_name)) {
        // Récupérer l'ancien fichier
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
            
            // Vérifier si le nouveau nom existe déjà
            if (file_exists($new_path) && $new_filename !== $media['file_name']) {
                $_SESSION['flash']['danger'] = "Un fichier avec ce nom existe déjà.";
            } else {
                // Renommer le fichier physique
                if (rename($old_path, $new_path)) {
                    // Mettre à jour la BDD
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
    $description = trim($_POST['description'] ?? '');
    $custom_name = trim($_POST['custom_name'] ?? '');
    $file = $_FILES['media_file'] ?? null;

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash']['danger'] = "Erreur lors de l'upload du fichier.";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $file['type'];

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['flash']['danger'] = "Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WEBP.";
        } else {
            $upload_dir = __DIR__ . '/../../../public/assets/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            
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
                $db_path = 'uploads/' . $filename;
                $sql = "INSERT INTO medias (file_name, description, file_path, file_type, uploaded_at, id_user) VALUES (?, ?, ?, ?, NOW(), ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssi', $filename, $description, $db_path, $file_type, $user_id);

                if ($stmt->execute()) {
                    $_SESSION['flash']['success'] = "✅ Média uploadé avec succès : " . $filename;
                } else {
                    $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement en BDD.";
                    unlink($file_path);
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

// --- PAGINATION & RECHERCHE ---
$items_per_page = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Comptage total
$total_result = $conn->query("SELECT COUNT(*) as total FROM medias");
$total_medias = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_medias / $items_per_page);

// Liste des médias (avec pagination)
$sql = "
    SELECT m.*, u.first_name, u.name 
    FROM medias m
    LEFT JOIN users u ON m.id_user = u.id_user
    ORDER BY m.uploaded_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$medias = [];
while ($row = $result->fetch_assoc()) {
    $medias[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= asset('_back.css/medias.css') ?>">
    <title>Gestion des Médias - ES Moulon</title>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🖼️ Gestion des Médias</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="../../admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
                <?php if (isset($_GET['popup'])): ?>
                    <p style="background:#dbeafe;color:#1e40af;padding:10px;border-radius:8px;margin-top:10px;font-weight:600;">
                        ℹ️ Après avoir uploadé votre image, fermez cette fenêtre pour retourner à votre article.
                    </p>
                <?php endif; ?>
            </div>
            <button class="btn btn-primary" onclick="toggleUploadForm()">
                ⬆️ Uploader un média
            </button>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?= htmlspecialchars($type) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endforeach; unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- STATISTIQUES -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-value"><?= $total_medias ?></div>
                <div class="stat-label">Médias total</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= count(array_filter($medias, fn($m) => strpos($m['file_type'], 'image') !== false)) ?></div>
                <div class="stat-label">Images (page actuelle)</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $page ?> / <?= $total_pages ?></div>
                <div class="stat-label">Page</div>
            </div>
        </div>

        <!-- FORMULAIRE UPLOAD -->
        <div class="card" id="formSection" style="display:none;">
            <h2 style="margin-bottom: 20px; color: #1f2937;">⬆️ Uploader un nouveau média</h2>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="custom_name">Nom du fichier (optionnel)</label>
                    <input 
                        type="text" 
                        id="custom_name" 
                        name="custom_name" 
                        placeholder="Ex: logo_es_moulon, photo_equipe_u15...">
                    <small style="color: #6b7280; font-size: 0.85rem;">
                        💡 Conseil : Utilisez des noms explicites pour retrouver facilement vos images
                    </small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Décrivez ce média..."></textarea>
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
                        ⬆️ Uploader
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="toggleUploadForm()">
                        Annuler
                    </button>
                </div>
            </form>
        </div>

        <!-- GALERIE -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:20px;flex-wrap:wrap;">
                <h2 style="color: #1f2937;margin:0;">📚 Galerie (<?= $total_medias ?> médias)</h2>
                
                <!-- BARRE DE RECHERCHE -->
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="🔍 Rechercher par nom de fichier..." 
                    onkeyup="filterMedias()"
                    style="flex:1;max-width:400px;padding:10px 15px;border:2px solid #e5e7eb;border-radius:8px;font-size:0.95rem;">
            </div>

            <?php if (empty($medias)): ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">
                    Aucun média pour le moment. Uploadez votre première image !
                </p>
            <?php else: ?>
                <div class="media-grid" id="mediaGrid">
                    <?php foreach ($medias as $media): ?>
                        <div class="media-card" data-filename="<?= strtolower($media['file_name']) ?>">
                            <div class="media-thumbnail" onclick="openImageModal('<?= asset($media['file_path']) ?>', '<?= htmlspecialchars($media['file_name']) ?>')">
                                <img src="<?= asset($media['file_path']) ?>" 
                                     alt="<?= htmlspecialchars($media['description'] ?? $media['file_name']) ?>"
                                     loading="lazy">
                            </div>

                            <div class="media-info">
                                <div class="media-title"><?= htmlspecialchars($media['file_name']) ?></div>

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
                                    <?php if (isset($_GET['popup']) && $_GET['popup'] == '1'): ?>
                                        <!-- MODE SÉLECTION POUR ACTUALITÉS/PARTENAIRES -->
                                        <button type="button" 
                                                onclick="addToSelection(<?= $media['id_media'] ?>, '<?= htmlspecialchars($media['file_name']) ?>', '<?= asset($media['file_path']) ?>')" 
                                                class="btn btn-success" 
                                                id="select-btn-<?= $media['id_media'] ?>"
                                                title="Ajouter à ma sélection">
                                            ➕ Ajouter
                                        </button>
                                    <?php else: ?>
                                        <!-- MODE NORMAL -->
                                        <button type="button" 
                                                onclick="copyToClipboard('<?= asset($media['file_path']) ?>')" 
                                                class="btn btn-success" 
                                                title="Copier l'URL de l'image">
                                            📋 Copier URL
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button type="button" 
                                            onclick="openImageModal('<?= asset($media['file_path']) ?>', '<?= htmlspecialchars($media['file_name']) ?>')" 
                                            class="btn btn-primary"
                                            title="Agrandir">
                                        👁️
                                    </button>
                                    
                                    <button type="button"
                                            onclick="openRenameModal(<?= $media['id_media'] ?>, '<?= htmlspecialchars(pathinfo($media['file_name'], PATHINFO_FILENAME)) ?>')"
                                            class="btn btn-warning"
                                            title="Renommer">
                                        ✏️
                                    </button>
                                    
                                    <a href="?section=medias&delete=<?= $media['id_media'] ?>&page=<?= $page ?><?= isset($_GET['popup']) ? '&popup=1' : '' ?><?= isset($_GET['context']) ? '&context=' . $_GET['context'] : '' ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm('⚠️ Confirmer la suppression de <?= htmlspecialchars($media['file_name']) ?> ?')">
                                        🗑️
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- MESSAGE SI AUCUN RÉSULTAT -->
                <div id="noResults" style="display:none;text-align:center;padding:40px;color:#6b7280;">
                    😕 Aucun média trouvé pour cette recherche
                </div>

                <!-- PAGINATION -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?section=medias&page=<?= $page - 1 ?>">← Précédent</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?section=medias&page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?section=medias&page=<?= $page + 1 ?>">Suivant →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
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
                <div class="form-group">
                    <label for="new_name">Nouveau nom (sans extension)</label>
                    <input type="text" 
                           id="new_name" 
                           name="new_name" 
                           required 
                           placeholder="Ex: logo_es_moulon"
                           pattern="[a-zA-Z0-9_-]+"
                           title="Utilisez uniquement des lettres, chiffres, tirets et underscores">
                </div>
                <div class="form-actions">
                    <button type="submit" name="rename_media" class="btn btn-success">✅ Renommer</button>
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
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // ===== SYSTÈME DE SÉLECTION POUR ACTUALITÉS/PARTENAIRES =====
        function addToSelection(mediaId, fileName, filePath) {
            // Détecter le contexte depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const context = urlParams.get('context') || 'news';
            const storageKey = context === 'partners' ? 'partner_media_selection' : 'news_media_selection';
            
            // Récupérer la sélection existante
            let selection = JSON.parse(localStorage.getItem(storageKey) || '[]');
            
            // Vérifier si déjà dans la sélection
            const exists = selection.find(item => item.id === mediaId);
            
            if (exists) {
                alert('✅ Cette image est déjà dans votre sélection !');
                return;
            }
            
            // Ajouter à la sélection
            selection.push({
                id: mediaId,
                name: fileName,
                path: filePath,
                timestamp: Date.now()
            });
            
            localStorage.setItem(storageKey, JSON.stringify(selection));
            
            // Feedback visuel
            const btn = document.getElementById('select-btn-' + mediaId);
            if (btn) {
                btn.textContent = '✅ Ajouté';
                btn.style.background = '#10b981';
                btn.disabled = true;
                
                setTimeout(() => {
                    btn.textContent = '➕ Ajouter';
                    btn.style.background = '';
                    btn.disabled = false;
                }, 2000);
            }
            
            const contextLabel = context === 'partners' ? 'partenaires' : 'actualités';
            alert('✅ Image ajoutée à votre sélection ' + contextLabel + ' !\n\n' + fileName);
            
            // Notifier la fenêtre parente
            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.postMessage({ action: 'mediaAdded', mediaId, fileName, filePath }, '*');
                } catch (e) {
                    console.log('Impossible de notifier la fenêtre parente');
                }
            }
        }

        // ===== AUTRES FONCTIONS =====
        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('✅ URL copiée dans le presse-papier !\n\n' + text);
                }).catch(() => {
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        }

        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                alert('✅ URL copiée !\n\n' + text);
            } catch (err) {
                prompt('Copiez cette URL manuellement :', text);
            }
            document.body.removeChild(textarea);
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

        function filterMedias() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.media-card');
            const noResults = document.getElementById('noResults');
            let visibleCount = 0;

            cards.forEach(card => {
                const filename = card.getAttribute('data-filename');
                if (filename.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Fermer avec Echap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeImageModal();
                closeRenameModal();
            }
        });
        
        // Afficher compteur de sélection si en mode popup
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
                info.style.cssText = 'background:#10b981;color:white;padding:12px 20px;border-radius:8px;margin-top:10px;font-weight:600;';
                info.innerHTML = '✅ ' + selection.length + ' image(s) sélectionnée(s) pour vos ' + contextLabel;
                header.querySelector('div').appendChild(info);
            }
        });
        <?php endif; ?>
    </script>

    <style>
        .media-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .media-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        .media-thumbnail {
            height: 180px;
            overflow: hidden;
            cursor: pointer;
            background: #f3f4f6;
            flex-shrink: 0;
        }

        .media-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .media-card:hover .media-thumbnail img {
            transform: scale(1.05);
        }

        .media-info {
            padding: 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .media-title {
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .media-description {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 8px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .media-meta {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-bottom: 10px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .media-meta span {
            display: inline-block;
        }

        .media-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }

        .media-actions .btn {
            padding: 6px;
            font-size: 1rem;
            text-align: center;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .media-actions .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
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

        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 10px 16px;
            background: white;
            color: #374151;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
            font-size: 0.9rem;
        }

        .pagination a:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .pagination .active {
            background: #1e3a8a;
            color: white;
            border-color: #1e3a8a;
        }

        /* MODALS */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .modal-content {
            max-width: 90%;
            max-height: 80%;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }

        .modal-close {
            position: absolute;
            top: 30px;
            right: 50px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
            z-index: 10000;
        }

        .modal-close:hover {
            color: #ef4444;
        }

        #modalCaption {
            color: white;
            margin-top: 20px;
            font-size: 1.2rem;
            font-weight: 600;
        }

        /* MODAL RENOMMAGE */
        .modal-dialog {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: #1f2937;
        }

        .modal-header .modal-close {
            position: static;
            color: #6b7280;
            font-size: 30px;
        }
    </style>
</body>
</html>