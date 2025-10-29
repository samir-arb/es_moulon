<?php
require_once __DIR__ . '/../../../includes/config.php';

// URL de l'admin courante
$adminUrl = $_SERVER['PHP_SELF'] ?? '/es_moulon/BO/admin.php';

// ======================
//  Sécurité d'accès
// ======================
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header("Location: {$adminUrl}?section=login");
    exit;
}

$allowed_roles = ['ROLE_ADMIN', 'ROLE_EDITOR', 'ROLE_MODERATOR'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Accès refusé.";
    header("Location: {$adminUrl}?section=dashboard");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// ======================
//  🛡️ GÉNÉRATION TOKEN CSRF
// ======================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ======================
//  Suppression d'image
// ======================
if (isset($_GET['remove_media']) && isset($_GET['edit'])) {
    $article_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("UPDATE news SET id_media = NULL WHERE id_new = ?");
    $stmt->bind_param('i', $article_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['flash']['success'] = "Image retirée avec succès.";
    header("Location: {$adminUrl}?section=news&edit={$article_id}");
    exit;
}

// ======================
//  Suppression
// ======================
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($user_role === 'ROLE_ADMIN' || $user_role === 'ROLE_MODERATOR') {
        $stmt = $conn->prepare("DELETE FROM news WHERE id_new = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash']['success'] = "Article supprimé avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Vous n'avez pas la permission de supprimer.";
    }

    header("Location: {$adminUrl}?section=news");
    exit;
}

// ======================
//  Ajout / Modification
// ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_article'])) {
    
    // 🛡️ VÉRIFICATION TOKEN CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash']['danger'] = "❌ Token CSRF invalide. Tentative d'attaque détectée !";
        header("Location: {$adminUrl}?section=news");
        exit;
    }
    
    $id = isset($_POST['id_new']) ? (int)$_POST['id_new'] : 0;
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $id_media = !empty($_POST['id_media']) ? (int)$_POST['id_media'] : null;
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');

    if (empty($title)) {
        $_SESSION['flash']['danger'] = "Le titre est obligatoire.";
    } else {
        if ($id > 0) {
            //  MODIFICATION
            $stmt = $conn->prepare("
                UPDATE news 
                SET title = ?, content = ?, status = ?, id_media = ?, published_at = ?, updated_at = NOW()
                WHERE id_new = ?
            ");
            $stmt->bind_param('ssiisi', $title, $content, $status, $id_media, $published_at, $id);
            $msg = "Article mis à jour avec succès.";
        } else {
            //  AJOUT
            $stmt = $conn->prepare("
                INSERT INTO news (title, content, status, id_user, id_media, published_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ssiiss', $title, $content, $status, $user_id, $id_media, $published_at);
            $msg = "Nouvel article publié avec succès.";
        }

        $stmt->execute();
        $stmt->close();
        $_SESSION['flash']['success'] = $msg;
    }

    header("Location: {$adminUrl}?section=news");
    exit;
}

// ======================
//  Récupération pour modification
// ======================
$edit_article = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM news WHERE id_new = ?");
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $edit_article = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ======================
//  Liste des articles
// ======================
$result = $conn->query("
    SELECT n.*, u.first_name, u.name 
    FROM news n
    LEFT JOIN users u ON n.id_user = u.id_user
    ORDER BY n.published_at DESC
");
$articles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// ======================
//  Statistiques
// ======================
$total = count($articles);
$publies = count(array_filter($articles, fn($a) => $a['status'] == 1));
$brouillons = $total - $publies;
$dernier = $articles[0]['title'] ?? 'Aucun article';

// ======================
//  Médias disponibles
// ======================
$result = $conn->query("SELECT id_media, file_name, file_path, description FROM medias ORDER BY uploaded_at DESC");
$available_medias = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Actualités - ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_back.css/news.css') ?>">
</head>
<body>
    <div class="container">

        <!-- HEADER -->
        <div class="header">
            <div>
                <h1>📰 Gestion des Actualités</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <button class="btn btn-primary" onclick="toggleForm()">➕ Nouvel article</button>
        </div>

        <!-- FLASH MESSAGES -->
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
                <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach;
            unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!--  STATISTIQUES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Articles</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#10b981;"><?= $publies ?></div>
                <div class="stat-label">Publiés</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f59e0b;"><?= $brouillons ?></div>
                <div class="stat-label">Brouillons</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#1f2937;font-size:1rem;"><?= htmlspecialchars($dernier) ?></div>
                <div class="stat-label">Dernier article</div>
            </div>
        </div>

        <!--  FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit_article ? '' : 'display:none;' ?>">
            <h2><?= $edit_article ? '✏️ Modifier un article' : '➕ Nouvel article' ?></h2>

            <?php if ($edit_article && !empty($edit_article['updated_at'])): ?>
                <p style="color:#6b7280;font-size:0.9rem;margin-bottom:10px;">
                    🕒 Dernière modification le <?= date('d/m/Y à H:i', strtotime($edit_article['updated_at'])) ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?section=news">
                
                <!-- 🛡️ CHAMP CSRF CACHÉ (secret généré par le serveur) -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <?php if ($edit_article): ?>
                    <input type="hidden" name="id_new" value="<?= (int)$edit_article['id_new'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Titre *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($edit_article['title'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="content">Contenu</label>
                    <textarea id="content" name="content" rows="8"><?= htmlspecialchars($edit_article['content'] ?? '') ?></textarea>
                </div>

                <!-- SÉLECTEUR D'IMAGES -->
                <div class="form-group">
                    <label>Image associée</label>
                    
                    <?php if ($edit_article && !empty($edit_article['id_media'])): ?>
                        <!-- APERÇU DE L'IMAGE ACTUELLE -->
                        <?php
                        $current_media_query = $conn->query("SELECT file_path, file_name FROM medias WHERE id_media = " . (int)$edit_article['id_media']);
                        $current_media = $current_media_query ? $current_media_query->fetch_assoc() : null;
                        ?>
                        
                        <?php if ($current_media): ?>
                            <div style="background:#f0fdf4;border:2px solid #10b981;border-radius:12px;padding:15px;margin-bottom:15px;">
                                <div style="display:flex;align-items:center;gap:15px;">
                                    <img src="<?= asset($current_media['file_path']) ?>" 
                                         alt="Image actuelle" 
                                         style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                                    <div style="flex:1;">
                                        <p style="font-weight:600;color:#065f46;margin-bottom:5px;">✅ Image actuelle</p>
                                        <p style="font-size:0.9rem;color:#047857;"><?= htmlspecialchars($current_media['file_name']) ?></p>
                                    </div>
                                    <a href="<?= htmlspecialchars($adminUrl) ?>?section=news&edit=<?= $edit_article['id_new'] ?>&remove_media=1" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Voulez-vous vraiment retirer cette image ?')"
                                       style="padding:8px 16px;font-size:0.9rem;">
                                        ❌ Retirer l'image
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="media-selector-container">
                        <div class="media-selector-header">
                            <span style="font-weight:600;color:#1f2937;">
                                📷 Ma sélection (<span id="selectionCount">0</span>)
                            </span>
                            <button type="button" 
                                    onclick="openMediaManager()" 
                                    class="open-media-manager">
                                ➕ Ajouter des images
                            </button>
                            <button type="button" 
                                    onclick="clearSelection()" 
                                    class="btn btn-secondary"
                                    style="padding:8px 16px;">
                                🗑️ Vider la sélection
                            </button>
                        </div>
                        
                        <div class="media-selector-grid" id="mediaSelectorGrid">
                            <div class="no-results" style="grid-column:1/-1;text-align:center;padding:40px;color:#6b7280;">
                                Aucune image sélectionnée.<br>
                                Cliquez sur "➕ Ajouter des images" pour en choisir depuis votre médiathèque.
                            </div>
                        </div>
                    </div>
                    
                    <small style="color: #6b7280; margin-top: 8px; display: block;">
                        💡 Astuce : Cliquez sur "➕ Ajouter des images" pour ouvrir votre médiathèque. Sélectionnez les images que vous voulez, puis fermez la fenêtre.
                    </small>
                </div>

                <div class="form-group">
                    <label for="published_at">Date de publication</label>
                    <input type="datetime-local" id="published_at" name="published_at"
                        value="<?= $edit_article ? date('Y-m-d\TH:i', strtotime($edit_article['published_at'])) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="1" <?= ($edit_article && $edit_article['status'] == 1) ? 'selected' : '' ?>>Publié</option>
                        <option value="0" <?= ($edit_article && $edit_article['status'] == 0) ? 'selected' : '' ?>>Brouillon</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_article" class="btn btn-success">💾 Enregistrer</button>
                    <a href="<?= htmlspecialchars($adminUrl) ?>?section=news" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- 📜 LISTE DES ARTICLES -->
        <div class="card">
            <h2>Liste des articles (<?= $total ?>)</h2>
            <?php if (empty($articles)): ?>
                <p style="text-align:center;color:#6b7280;padding:30px;">Aucun article pour le moment.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Date publication</th>
                                <th>Dernière modif</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $a): ?>
                                <tr>
                                    <td><?= $a['id_new'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($a['title']) ?></strong>
                                        <?php if (!empty($a['id_media'])): ?>
                                            <span style="display:inline-block;margin-left:8px;background:#10b981;color:white;padding:2px 8px;border-radius:12px;font-size:0.75rem;">📷 Photo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($a['published_at'])) ?></td>
                                    <td><?= !empty($a['updated_at']) ? date('d/m/Y H:i', strtotime($a['updated_at'])) : '-' ?></td>
                                    <td>
                                        <?php if ($a['status']): ?>
                                            <span class="badge badge-active">Publié</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">Brouillon</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="<?= htmlspecialchars($adminUrl) ?>?section=news&edit=<?= $a['id_new'] ?>" class="btn btn-warning">✏️ Modifier</a>
                                        <?php if ($user_role === 'ROLE_ADMIN' || $user_role === 'ROLE_MODERATOR'): ?>
                                            <a href="<?= htmlspecialchars($adminUrl) ?>?section=news&delete=<?= $a['id_new'] ?>" class="btn btn-danger" onclick="return confirm('Confirmer la suppression ?')">🗑️ Supprimer</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle formulaire
        function toggleForm() {
            const form = document.getElementById('formSection');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Marquer l'élément pré-sélectionné au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('.media-selector-item input[type="radio"]:checked');
            if (checkedRadio) {
                checkedRadio.closest('.media-selector-item').classList.add('selected');
            }
        });

        // Sélection d'un média
        function selectMedia(element) {
            document.querySelectorAll('.media-selector-item').forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
        }

        // Recherche/filtre des médias
        function filterMedias() {
            const searchTerm = document.getElementById('mediaSearchInput').value.toLowerCase();
            const items = document.querySelectorAll('.media-selector-item');
            let visibleCount = 0;
            
            items.forEach(item => {
                const filename = item.getAttribute('data-filename');
                if (filename.includes(searchTerm)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            document.getElementById('visibleCount').textContent = visibleCount;
            
            const grid = document.getElementById('mediaSelectorGrid');
            let noResultsMsg = grid.querySelector('.no-results');
            
            if (visibleCount === 0 && !noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results';
                noResultsMsg.style.gridColumn = '1/-1';
                noResultsMsg.innerHTML = '😕 Aucun média trouvé pour "' + searchTerm + '"';
                grid.appendChild(noResultsMsg);
            } else if (visibleCount > 0 && noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    </script>
    
    <script>
        function openMediaManager() {
            window.mediaManager = window.open(
                '/es_moulon/BO/_backoffice/_sections/medias.php?popup=1&context=news',
                'mediaManager',
                'width=1000,height=700,scrollbars=yes,resizable=yes'
            );

            if (!window.mediaManager) {
                alert("⚠️ Impossible d’ouvrir la médiathèque. Vérifie ton bloqueur de pop-ups.");
            }
        }

    </script>

    <script>
        // Réception du message envoyé depuis la médiathèque
        window.addEventListener('message', function(event) {
            if (event.data.action === 'mediaAdded') {
                const { mediaId, fileName, filePath } = event.data;

                // ✅ Ferme proprement la popup si elle existe encore
                if (window.mediaManager && !window.mediaManager.closed) {
                    try {
                        window.mediaManager.close();
                    } catch (e) {
                        console.warn("Impossible de fermer la popup :", e);
                    }
                }

                // ✅ Champ caché id_media (créé s’il n’existe pas)
                let input = document.querySelector('input[name="id_media"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'id_media';
                    document.querySelector('form').appendChild(input);
                }
                input.value = mediaId;

                // ✅ Afficher un aperçu visuel sans recharger
                const grid = document.getElementById('mediaSelectorGrid');
                grid.innerHTML = `
                    <div style="display:flex;align-items:center;gap:15px;background:#ecfdf5;border:2px solid #10b981;border-radius:12px;padding:12px;">
                        <img src="${filePath}" alt="${fileName}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                        <div>
                            <p style="font-weight:600;color:#065f46;margin-bottom:4px;">✅ Image sélectionnée</p>
                            <p style="font-size:0.9em;color:#047857;">${fileName}</p>
                        </div>
                    </div>
                `;

                // ✅ Mets à jour le compteur
                const countEl = document.getElementById('selectionCount');
                if (countEl) countEl.textContent = '1';

                // 💡 Petit effet visuel de confirmation (optionnel)
                const toast = document.createElement('div');
                toast.textContent = "✅ Image ajoutée avec succès";
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #10b981;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 8px;
                    font-weight: 600;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
                    z-index: 9999;
                    transition: opacity 0.5s ease;
                `;
                document.body.appendChild(toast);
                setTimeout(() => (toast.style.opacity = '0'), 2000);
                setTimeout(() => toast.remove(), 2500);
            }
        });

    </script>

    <script>
       function clearSelection() {
            // Vide la zone d’aperçu
            const grid = document.getElementById('mediaSelectorGrid');
            grid.innerHTML = `
                <div class="no-results" style="grid-column:1/-1;text-align:center;padding:40px;color:#6b7280;">
                    Aucune image sélectionnée.<br>
                    Cliquez sur "➕ Ajouter des images" pour en choisir depuis votre médiathèque.
                </div>
            `;

            // Remet le compteur à zéro
            document.getElementById('selectionCount').textContent = '0';

            // Supprime la valeur du champ caché id_media
            const input = document.querySelector('input[name="id_media"]');
            if (input) input.value = '';

            // Optionnel : affiche une alerte douce
            alert('🗑️ Sélection vidée.');
        }
        
    </script>
   
</body>
</html>