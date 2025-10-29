<?php
require_once __DIR__ . '/../../../includes/config.php';

// Sécurité
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: /es_moulon/BO/admin.php?section=login');
    exit;
}

$allowed_roles = ['ROLE_ADMIN'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: /es_moulon/BO/admin.php?section=dashboard');
    exit;
}

// ===== TRAITEMENT DES ACTIONS =====

if (isset($_GET['toggle_doc'])) {
    $id_category_media = (int)$_GET['toggle_doc'];
    $stmt = $pdo->prepare("UPDATE categories_medias SET is_active = NOT is_active WHERE id_category_media=?");
    $stmt->execute([$id_category_media]);
    $_SESSION['flash']['success'] = "Statut du document modifié.";
    header('Location: /es_moulon/BO/admin.php?section=club_info');
    exit;
}

if (isset($_GET['delete_doc'])) {
    $id_category_media = (int)$_GET['delete_doc'];
    $stmt = $pdo->prepare("DELETE FROM categories_medias WHERE id_category_media=?");
    $stmt->execute([$id_category_media]);
    $_SESSION['flash']['success'] = "Document supprimé.";
    header('Location: /es_moulon/BO/admin.php?section=club_info');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_info'])) {
    $stmt_check = $pdo->query("SELECT * FROM club_info LIMIT 1");
    $club_check = $stmt_check->fetch(PDO::FETCH_ASSOC);

    $stadium_name = trim($_POST['stadium_name']);
    $address = trim($_POST['address']);
    $google_maps_url = trim($_POST['google_maps_url']);
    $id_media = !empty($_POST['id_media']) ? (int)$_POST['id_media'] : null;

    if ($club_check) {
        $stmt = $pdo->prepare("UPDATE club_info SET stadium_name=?, address=?, google_maps_url=?, id_media=?, updated_at=NOW()");
        $stmt->execute([$stadium_name, $address, $google_maps_url, $id_media]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO club_info (stadium_name, address, google_maps_url, id_media) VALUES (?,?,?,?)");
        $stmt->execute([$stadium_name, $address, $google_maps_url, $id_media]);
    }
    $_SESSION['flash']['success'] = "Informations du club mises à jour.";
    header('Location: /es_moulon/BO/admin.php?section=club_info');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hours'])) {
    $days_temp = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ($days_temp as $key) {
        $open = $_POST[$key . '_open'] ?: null;
        $close = $_POST[$key . '_close'] ?: null;
        $pdo->prepare("UPDATE club_info SET {$key}_open=?, {$key}_close=?")->execute([$open, $close]);
    }
    $_SESSION['flash']['success'] = "Horaires mis à jour.";
    header('Location: /es_moulon/BO/admin.php?section=club_info');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_document'])) {
    $name = trim($_POST['title']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title'])));
    $description = trim($_POST['description']);
    $media_id = !empty($_POST['media_id']) ? (int)$_POST['media_id'] : null;
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO categories_medias (name, slug, description, media_id, display_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
    $stmt->execute([$name, $slug, $description, $media_id, $display_order, $is_active]);
    $_SESSION['flash']['success'] = "Document ajouté avec succès.";
    header('Location: /es_moulon/BO/admin.php?section=club_info');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_document'])) {
    $id_category_media = (int)$_POST['id_category_media'];
    $name = trim($_POST['title']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title'])));
    $description = trim($_POST['description']);
    $media_id = !empty($_POST['media_id']) ? (int)$_POST['media_id'] : null;
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE categories_medias SET name=?, slug=?, description=?, media_id=?, display_order=?, is_active=? WHERE id_category_media=?");
    $stmt->execute([$name, $slug, $description, $media_id, $display_order, $is_active, $id_category_media]);
    $_SESSION['flash']['success'] = "Document modifié avec succès.";
    header('Location: /es_moulon/BO/admin.php?section=club_info');
    exit;
}

// ===== RÉCUPÉRATION DES DONNÉES =====

$stmt = $pdo->query("SELECT * FROM club_info LIMIT 1");
$club = $stmt->fetch(PDO::FETCH_ASSOC);

$days = [
    'monday' => 'Lundi', 'tuesday' => 'Mardi', 'wednesday' => 'Mercredi',
    'thursday' => 'Jeudi', 'friday' => 'Vendredi', 'saturday' => 'Samedi', 'sunday' => 'Dimanche'
];

$stmt_docs = $pdo->query("
    SELECT cm.*, m.file_name, m.file_path
    FROM categories_medias cm
    LEFT JOIN medias m ON cm.media_id = m.id_media
    WHERE cm.media_id IS NOT NULL
    ORDER BY cm.display_order ASC
");
$documents = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

// ✅ Récupération des catégories de médias pour l'organisation
$stmt_all_categories = $pdo->query("
    SELECT id_category_media, name, icon, color 
    FROM categories_medias 
    ORDER BY display_order ASC
");
$all_categories = $stmt_all_categories->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    body {
        background: #ffffff !important;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .card h2 {
        color: #1c995a;
        font-size: 1.5rem;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header h1 {
        font-size: 2rem;
        color: #1f2937;
    }

    .header a {
        color: #1c995a;
        text-decoration: none;
        font-weight: 600;
    }

    .alert {
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-top: 15px;
    }

    .form-group label {
        font-weight: 600;
        color: #374151;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.95rem;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #1c995a;
    }

    /*  Style pour les optgroup */
    .form-group select optgroup {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1c995a;
        background: #f0f9f4;
        padding: 8px 0;
    }

    .form-group select option {
        padding: 8px 12px;
        font-weight: 400;
        color: #374151;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table th {
        background: #f3f4f6;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
    }

    table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    table tr:hover {
        background: #f9fafb;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        font-size: 0.95rem;
    }

    .btn-primary {
        background: #1c995a;
        color: white;
    }

    .btn-primary:hover {
        background: #14824b;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
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

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
    }

    .form-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    /* MODAL */
    .modal {
        display: none;
        position: fixed;
        z-index: 999999 !important;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }

    .modal.show {
        display: flex !important;
    }

    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 600px;
        max-height: 100vh;
        overflow-y: auto;
        margin: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        position: relative;
    }

    .modal-content h2 {
        color: #1c995a;
        margin-bottom: 20px;
        margin-top: 0;
    }

    .close {
        position: absolute;
        top: 15px;
        right: 20px;
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
    }

    .close:hover {
        color: #000;
    }

    .help-text {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 5px;
    }
</style>

<div class="container">
    <div class="header">
        <div>
            <h1>⚙️ Informations Pratiques & Documents</h1>
            <p style="color: #6b7280; margin-top: 4px;">
                <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
            </p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['flash']['success'] ?>
        </div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>

    <!-- INFOS DU CLUB -->
    <div class="card">
        <h2>📍 Informations du Club</h2>
        <form method="POST">
            <input type="hidden" name="save_info" value="1">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nom du stade</label>
                    <input type="text" name="stadium_name" value="<?= htmlspecialchars($club['stadium_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Adresse complète</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($club['address'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>URL Google Maps</label>
                    <input type="url" name="google_maps_url" value="<?= htmlspecialchars($club['google_maps_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Image du stade</label>
                    <select name="id_media">
                        <option value="">-- Aucune image --</option>
                        <?php
                        $stmt_images = $pdo->query("SELECT id_media, file_name FROM medias WHERE file_type LIKE 'image/%' ORDER BY file_name");
                        foreach ($stmt_images as $img):
                        ?>
                            <option value="<?= $img['id_media'] ?>" <?= ($club['id_media'] ?? null) == $img['id_media'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($img['file_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            </div>
        </form>
    </div>

    <!-- HORAIRES -->
    <div class="card">
        <h2>🕒 Horaires d'ouverture</h2>
        <form method="POST">
            <input type="hidden" name="save_hours" value="1">
            <table>
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Ouverture</th>
                        <th>Fermeture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $key => $label): ?>
                        <tr>
                            <td><strong><?= $label ?></strong></td>
                            <td><input type="time" name="<?= $key ?>_open" value="<?= htmlspecialchars($club[$key . '_open'] ?? '') ?>"></td>
                            <td><input type="time" name="<?= $key ?>_close" value="<?= htmlspecialchars($club[$key . '_close'] ?? '') ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            </div>
        </form>
    </div>

    <!-- DOCUMENTS -->
    <div class="card">
        <h2>📄 Documents téléchargeables</h2>
        <button type="button" class="btn btn-primary" onclick="openModal('addModal')">➕ Ajouter un document</button>

        <table>
            <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Titre</th>
                    <th>Fichier</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($documents) > 0): ?>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?= $doc['display_order'] ?></td>
                            <td><?= htmlspecialchars($doc['name']) ?></td>
                            <td>
                                <?php if ($doc['file_path']): ?>
                                    <a href="<?= asset($doc['file_path']) ?>" target="_blank"><?= htmlspecialchars($doc['file_name']) ?></a>
                                <?php else: ?>
                                    <span style="color:#aaa;">Aucun</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?section=club_info&toggle_doc=<?= $doc['id_category_media'] ?>" class="btn btn-warning btn-sm">
                                    <?= $doc['is_active'] ? '✅ Actif' : '❌ Inactif' ?>
                                </a>
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm"
                                    onclick='openEditModal(<?= json_encode($doc) ?>)'>
                                    ✏️ Modifier
                                </button>
                                <a href="?section=club_info&delete_doc=<?= $doc['id_category_media'] ?>"
                                    onclick="return confirm('Supprimer ce document ?')"
                                    class="btn btn-danger btn-sm">🗑️ Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color: #9ca3af; padding: 30px;">
                            Aucun document. Cliquez sur "Ajouter un document".
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL AJOUT -->
<div id="addModal" class="modal" onclick="if(event.target === this) closeModal('addModal')">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addModal')">&times;</span>
        <h2>➕ Ajouter un document téléchargeable</h2>
        <form method="POST">
            <input type="hidden" name="add_document" value="1">

            <div class="form-group">
                <label>Titre du document *</label>
                <input type="text" name="title" required placeholder="Ex: Règlement intérieur">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Description optionnelle"></textarea>
            </div>

            <div class="form-group">
                <label>Fichier (depuis la galerie) *</label>
                <select name="media_id" required>
                    <option value="">-- Sélectionner un fichier --</option>
                    
                    <?php
                    // ✅ Grouper les fichiers par catégorie
                    foreach ($all_categories as $category):
                        // Récupérer les médias de cette catégorie
                        $stmt_cat_medias = $pdo->prepare("
                            SELECT id_media, file_name, file_type 
                            FROM medias 
                            WHERE id_category_media = ? 
                            ORDER BY file_name ASC
                        ");
                        $stmt_cat_medias->execute([$category['id_category_media']]);
                        $medias_in_cat = $stmt_cat_medias->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($medias_in_cat) > 0):
                    ?>
                        <optgroup label="<?= $category['icon'] ?> <?= htmlspecialchars($category['name']) ?>">
                            <?php foreach ($medias_in_cat as $media): ?>
                                <option value="<?= $media['id_media'] ?>">
                                    <?= htmlspecialchars($media['file_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php 
                        endif;
                    endforeach;
                    
                    // ✅ Médias non classés
                    $stmt_uncat = $pdo->query("
                        SELECT id_media, file_name 
                        FROM medias 
                        WHERE id_category_media IS NULL 
                        ORDER BY file_name ASC
                    ");
                    $uncat_medias = $stmt_uncat->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($uncat_medias) > 0):
                    ?>
                        <optgroup label="⚠️ Non classés">
                            <?php foreach ($uncat_medias as $media): ?>
                                <option value="<?= $media['id_media'] ?>">
                                    <?= htmlspecialchars($media['file_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
                <small class="help-text">💡 Les fichiers sont organisés par catégorie pour faciliter la recherche</small>
            </div>

            <div class="form-group">
                <label>Ordre d'affichage</label>
                <input type="number" name="display_order" value="<?= count($documents) + 1 ?>" required>
            </div>

            <div class="form-group">
                <label>
                    Document actif (visible sur le site)
                    <input type="checkbox" name="is_active" checked> 
                    
                </label>
            </div>

            <button type="submit" class="btn btn-primary">💾 Ajouter le document</button>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="editModal" class="modal" onclick="if(event.target === this) closeModal('editModal')">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h2>✏️ Modifier le document</h2>
        <form method="POST">
            <input type="hidden" name="edit_document" value="1">
            <input type="hidden" name="id_category_media" id="edit_id">

            <div class="form-group">
                <label>Titre du document *</label>
                <input type="text" name="title" id="edit_title" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Fichier (depuis la galerie) *</label>
                <select name="media_id" id="edit_media" required>
                    <option value="">-- Sélectionner un fichier --</option>
                    
                    <?php
                    // Même structure pour le modal d'édition
                    foreach ($all_categories as $category):
                        $stmt_cat_medias = $pdo->prepare("
                            SELECT id_media, file_name 
                            FROM medias 
                            WHERE id_category_media = ? 
                            ORDER BY file_name ASC
                        ");
                        $stmt_cat_medias->execute([$category['id_category_media']]);
                        $medias_in_cat = $stmt_cat_medias->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($medias_in_cat) > 0):
                    ?>
                        <optgroup label="<?= $category['icon'] ?> <?= htmlspecialchars($category['name']) ?>">
                            <?php foreach ($medias_in_cat as $media): ?>
                                <option value="<?= $media['id_media'] ?>">
                                    <?= htmlspecialchars($media['file_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php 
                        endif;
                    endforeach;
                    
                    if (count($uncat_medias) > 0):
                    ?>
                        <optgroup label="⚠️ Non classés">
                            <?php foreach ($uncat_medias as $media): ?>
                                <option value="<?= $media['id_media'] ?>">
                                    <?= htmlspecialchars($media['file_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
                <small class="help-text">💡 Les fichiers sont organisés par catégorie</small>
            </div>

            <div class="form-group">
                <label>Ordre d'affichage</label>
                <input type="number" name="display_order" id="edit_order" required>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" id="edit_active"> 
                    Document actif (visible sur le site)
                </label>
            </div>

            <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function openEditModal(doc) {
        document.getElementById('edit_id').value = doc.id_category_media;
        document.getElementById('edit_title').value = doc.name;
        document.getElementById('edit_description').value = doc.description || '';
        document.getElementById('edit_media').value = doc.media_id || '';
        document.getElementById('edit_order').value = doc.display_order;
        document.getElementById('edit_active').checked = doc.is_active == 1;
        openModal('editModal');
    }
</script>