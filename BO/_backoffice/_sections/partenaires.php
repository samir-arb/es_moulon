<?php
require_once __DIR__ . '/../../../includes/config.php';

$adminUrl = $_SERVER['PHP_SELF'] ?? '/es_moulon/BO/admin.php';

// Sécurité
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header("Location: {$adminUrl}?section=login");
    exit;
}

$allowed_roles = ['ROLE_ADMIN', 'ROLE_EDITOR'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: {$adminUrl}?section=dashboard");
    exit;
}

$user_role = $_SESSION['role'];

// 🛡️ GÉNÉRATION TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ========================================
// GESTION IMAGE FOOTER
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_footer_image'])) {
    
    // 🛡️ VÉRIFICATION TOKEN CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash']['danger'] = "❌ Token CSRF invalide. Tentative d'attaque détectée !";
        header("Location: {$adminUrl}?section=partenaires");
        exit;
    }
    
    $footer_media_id = isset($_POST['footer_media_id']) && !empty($_POST['footer_media_id']) ? (int)$_POST['footer_media_id'] : null;
    
    try {
        // Retirer l'usage 'partner_footer' de toutes les images
        $conn->query("UPDATE medias SET usage_type = NULL WHERE usage_type = 'partner_footer'");
        
        // Appliquer le nouvel usage si une image est sélectionnée
        if ($footer_media_id) {
            $stmt = $conn->prepare("UPDATE medias SET usage_type = 'partner_footer' WHERE id_media = ?");
            $stmt->bind_param('i', $footer_media_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['flash']['success'] = "Image footer mise à jour avec succès !";
        } else {
            $_SESSION['flash']['success'] = "Image footer retirée.";
        }
    } catch (Exception $e) {
        $_SESSION['flash']['danger'] = "Erreur : " . $e->getMessage();
    }
    
    header("Location: {$adminUrl}?section=partenaires");
    exit;
}

// SUPPRESSION
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($user_role === 'ROLE_ADMIN') {
        $stmt = $conn->prepare("DELETE FROM partners WHERE id_partner = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash']['success'] = "Partenaire supprimé.";
    }
    header("Location: {$adminUrl}?section=partenaires");
    exit;
}

// AJOUT / MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_partner'])) {
    
    // 🛡️ VÉRIFICATION TOKEN CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash']['danger'] = "❌ Token CSRF invalide. Tentative d'attaque détectée !";
        header("Location: {$adminUrl}?section=partenaires");
        exit;
    }
    
    $id = isset($_POST['id_partner']) && is_numeric($_POST['id_partner']) ? (int)$_POST['id_partner'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $website_url = isset($_POST['website_url']) ? trim($_POST['website_url']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 1;
    $id_media = isset($_POST['id_media']) && !empty($_POST['id_media']) ? (int)$_POST['id_media'] : null;
    
    if (empty($name)) {
        $_SESSION['flash']['danger'] = "Le nom du partenaire est obligatoire.";
    } else {
        if ($id > 0) {
            // Modification
            $stmt = $conn->prepare("UPDATE partners SET company_name=?, redirect_url=?, description=?, is_active=?, display_order=?, id_media=? WHERE id_partner=?");
            $stmt->bind_param('sssiiii', $name, $website_url, $description, $is_active, $display_order, $id_media, $id);
            $msg = "Partenaire mis à jour avec succès.";
        } else {
            // Ajout
            $stmt = $conn->prepare("INSERT INTO partners (company_name, redirect_url, description, is_active, display_order, id_media) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssiii', $name, $website_url, $description, $is_active, $display_order, $id_media);
            $msg = "Partenaire ajouté avec succès.";
        }
        
        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $msg;
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement : " . $stmt->error;
        }
        $stmt->close();
    }
    
    header("Location: {$adminUrl}?section=partenaires");
    exit;
}

// Récupération pour édition
$edit_partner = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM partners WHERE id_partner = ?");
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $edit_partner = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Liste des partenaires
$result = $conn->query("
    SELECT p.*, m.file_path, m.file_name
    FROM partners p
    LEFT JOIN medias m ON p.id_media = m.id_media
    ORDER BY p.display_order ASC, p.company_name ASC
");
$partners = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// ==============================
// 📊 Statistiques
// ==============================
$total = count($partners);

// on vérifie que la clé existe avant de tester sa valeur
$actifs = count(array_filter($partners, fn($p) =>
    isset($p['is_active']) && $p['is_active'] == 1
));

$inactifs = $total - $actifs;


// Liste des médias
$medias_result = $conn->query("SELECT id_media, file_name, file_path FROM medias ORDER BY uploaded_at DESC");
$available_medias = $medias_result ? $medias_result->fetch_all(MYSQLI_ASSOC) : [];

// ========================================
// IMAGE FOOTER ACTUELLE
// ========================================
$footer_image_result = $conn->query("
    SELECT id_media, file_name, file_path 
    FROM medias 
    WHERE usage_type = 'partner_footer' 
    LIMIT 1
");
$current_footer_image = $footer_image_result ? $footer_image_result->fetch_assoc() : null;
?>


    <div class="container">
        <div class="header">
            <div>
                <h1>🤝 Gestion des Partenaires</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <button class="btn btn-primary" onclick="toggleForm()">➕ Nouveau partenaire</button>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
                <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#10b981;"><?= $actifs ?></div>
                <div class="stat-label">Actifs</div>
            </div>
        </div>

        <!-- ========================================
             IMAGE FOOTER "REJOIGNEZ PARTENAIRES"
        ========================================= -->
        <div class="card" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);    border-left: 5px solid #10b981; margin-bottom: 30px;">
            <h2>🖼️ Image Footer "Rejoignez nos Partenaires"</h2>
            <p style="color:#6b7280;margin-bottom:20px;">📍 Cette image s'affiche en bas de la <strong>page Partenaires</strong> côté visiteurs</p>

            <?php if ($current_footer_image): ?>
                <div style="text-align:center;padding:20px;background:#f9fafb;border-radius:10px;margin-bottom:20px;">
                    <img src="<?= asset($current_footer_image['file_path']) ?>" 
                         alt="Image footer actuelle"
                         style="max-width:100%;max-height:200px;object-fit:contain;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    <p style="margin-top:10px;color:#6b7280;font-size:0.9rem;">
                        <strong>Image actuelle :</strong> <?= htmlspecialchars($current_footer_image['file_name']) ?>
                    </p>
                </div>
            <?php else: ?>
                <div style="text-align:center;padding:30px;background:#fef3c7;border-radius:8px;color:#92400e;margin-bottom:20px;">
                    ⚠️ <strong>Aucune image footer définie</strong>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="save_footer_image" value="1">
                
                <!-- 🛡️ CHAMP CSRF CACHÉ -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="form-group-compact">
                    <label for="footer_media_id">Sélectionner une image</label>
                    <select id="footer_media_id" name="footer_media_id" onchange="previewFooterImage(this)">
                        <option value="">-- Aucune image (retirer) --</option>
                        <?php foreach ($available_medias as $m): ?>
                            <option value="<?= $m['id_media'] ?>" 
                                    data-img="<?= asset($m['file_path']) ?>"
                                    <?= ($current_footer_image && $current_footer_image['id_media'] == $m['id_media']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['file_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="display:block;margin-top:8px;color:#6b7280;">
                        💡 Image manquante ? 
                        <a href="<?= $adminUrl ?>?section=medias" target="_blank" style="color:#3b82f6;text-decoration:none;">Ouvrir Médias →</a> 
                        puis rafraîchissez cette page
                    </small>
                </div>

                <div id="footerImagePreview" style="margin-top:15px;"></div>

                <div class="form-actions" style="margin-top:20px;">
                    <button type="submit" class="btn btn-success">💾 Enregistrer l'image footer</button>
                </div>
            </form>
        </div>

        <div class="card" id="formSection" style="<?= $edit_partner ? '' : 'display:none;' ?>">
            <h2><?= $edit_partner ? '✏️ Modifier' : '➕ Nouveau' ?> partenaire</h2>

            <form method="POST" action="">
                <input type="hidden" name="save_partner" value="1">
                
                <!-- 🛡️ CHAMP CSRF CACHÉ -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <?php if ($edit_partner): ?>
                    <input type="hidden" name="id_partner" value="<?= (int)$edit_partner['id_partner'] ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group-compact">
                        <label for="name">Nom du partenaire *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($edit_partner['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group-compact">
                        <label for="display_order">Ordre d'affichage *</label>
                        <input type="number" id="display_order" name="display_order" value="<?= $edit_partner['display_order'] ?? ($total + 1) ?>" min="1" required>
                        <small>Plus petit = apparaît en premier</small>
                    </div>
                </div>

                <div class="form-group-compact">
                    <label for="website_url">Site web</label>
                    <input type="url" id="website_url" name="website_url" value="<?= htmlspecialchars($edit_partner['website_url'] ?? '') ?>" placeholder="https://exemple.com">
                </div>

                <div class="form-group-compact">
                    <label for="description">Description (optionnel)</label>
                    <textarea id="description" name="description" rows="2"><?= htmlspecialchars($edit_partner['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group-compact">
                    <label for="id_media">Logo</label>
                    <select id="id_media" name="id_media" onchange="previewLogo(this)">
                        <option value="">Sans logo</option>
                        <?php foreach ($available_medias as $m): ?>
                            <option value="<?= $m['id_media'] ?>" 
                                    data-img="<?= asset($m['file_path']) ?>"
                                    <?= (isset($edit_partner) && $edit_partner['id_media'] == $m['id_media']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['file_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="logoPreview" style="margin-top:8px;"></div>
                    <small>
                        💡 Logo manquant ? 
                        <a href="<?= $adminUrl ?>?section=medias" target="_blank" style="color:#3b82f6;">Ouvrir Médias</a> 
                        puis rafraîchissez cette page
                    </small>
                </div>

                <div class="form-group-compact">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" <?= (isset($edit_partner) && $edit_partner['is_active']) || !isset($edit_partner) ? 'checked' : '' ?>>
                        <span>Actif (visible sur le site)</span>
                    </label>
                </div>

                <div class="form-actions" style="margin-top:20px;">
                    <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                    <a href="<?= $adminUrl ?>?section=partenaires" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Liste des partenaires (<?= $total ?>)</h2>
            <?php if (empty($partners)): ?>
                <p style="text-align:center;color:#6b7280;padding:30px;">Aucun partenaire.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:80px;">Ordre</th>
                                <th style="width:100px;">Logo</th>
                                <th>Nom</th>
                                <th style="width:80px;">Site</th>
                                <th style="width:100px;">Statut</th>
                                <th style="width:150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partners as $p): ?>
                                <tr>
                                    <td style="text-align:center;"><strong><?= $p['display_order'] ?></strong></td>
                                    <td style="text-align:center;">
                                        <?php if ($p['file_path']): ?>
                                            <img src="<?= asset($p['file_path']) ?>" alt="" style="height:40px;max-width:80px;object-fit:contain;">
                                        <?php else: ?>
                                            <span style="color:#9ca3af;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($p['company_name']) ?></strong></td>
                                    <td style="text-align:center;">
                                        <?php if ($p['redirect_url']): ?>
                                            <a href="<?= htmlspecialchars($p['redirect_url']) ?>" target="_blank" style="color:#3b82f6;">🔗</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['is_active']): ?>
                                            <span class="badge badge-active">Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="?section=partenaires&edit=<?= $p['id_partner'] ?>" class="btn btn-warning">✏️</a>
                                        <?php if ($user_role === 'ROLE_ADMIN'): ?>
                                            <a href="?section=partenaires&delete=<?= $p['id_partner'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer ?')">🗑️</a>
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
        function toggleForm() {
            const form = document.getElementById('formSection');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function previewLogo(select) {
            const opt = select.options[select.selectedIndex];
            const prev = document.getElementById('logoPreview');
            if (opt.dataset.img) {
                prev.innerHTML = '<img src="' + opt.dataset.img + '" style="max-height:80px;max-width:150px;object-fit:contain;border:1px solid #e5e7eb;padding:8px;border-radius:6px;background:white;">';
            } else {
                prev.innerHTML = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('id_media');
            if (select && select.value) {
                previewLogo(select);
            }
        });

        // ✅ Preview image footer
        function previewFooterImage(select) {
            const opt = select.options[select.selectedIndex];
            const prev = document.getElementById('footerImagePreview');
            if (opt.dataset.img) {
                prev.innerHTML = '<div style="text-align:center;padding:20px;background:#f9fafb;border-radius:10px;"><img src="' + opt.dataset.img + '" style="max-height:200px;max-width:100%;object-fit:contain;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1);"><p style="margin-top:10px;color:#6b7280;font-size:0.9rem;">Aperçu de l\'image sélectionnée</p></div>';
            } else {
                prev.innerHTML = '';
            }
        }

        // Initialisation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            // ... vos autres initialisations ...
            
            // Preview image footer
            const footerSelect = document.getElementById('footer_media_id');
            if (footerSelect && footerSelect.value) {
                previewFooterImage(footerSelect);
            }
        });
    </script>
</body>
</html>