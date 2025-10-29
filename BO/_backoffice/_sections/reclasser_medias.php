<?php
require_once __DIR__ . '/../../../includes/config.php';

// Protection
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['ROLE_ADMIN', 'ROLE_EDITOR'])) {
    header("Location: ../../admin.php?section=dashboard");
    exit;
}

// Récupérer les catégories
$categories_query = $conn->query("SELECT * FROM categories_medias ORDER BY display_order ASC");
$categories = [];
while ($cat = $categories_query->fetch_assoc()) {
    $categories[] = $cat;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reclasser'])) {
    $success_count = 0;
    $error_count = 0;
    $skipped_count = 0;

    foreach ($_POST['media_category'] as $media_id => $category_id) {
        $media_id = (int)$media_id;

        // Si aucune catégorie n'est sélectionnée, on passe au suivant
        if (empty($category_id)) {
            $skipped_count++;
            continue;
        }

        $category_id = (int)$category_id;

        // CORRECTION : Mettre à jour medias.id_category_media (et non categories_medias.media_id)
        $stmt = $conn->prepare("UPDATE medias SET id_category_media = ? WHERE id_media = ?");
        $stmt->bind_param('ii', $category_id, $media_id);

        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
            error_log("Erreur reclassement media $media_id : " . $stmt->error);
        }

        $stmt->close();
    }

    if ($success_count > 0) {
        $_SESSION['flash']['success'] = "✅ $success_count média(s) reclassé(s) avec succès !";
    }
    if ($error_count > 0) {
        $_SESSION['flash']['error'] = "❌ $error_count erreur(s) lors du reclassement.";
    }
    if ($skipped_count > 0 && $success_count == 0 && $error_count == 0) {
        $_SESSION['flash']['warning'] = "⚠️ Aucune modification effectuée. Sélectionnez au moins une catégorie.";
    }

    header("Location: ?section=medias");
    exit;
}

// CORRECTION : Jointure correcte pour récupérer la catégorie de chaque média
$medias_query = $conn->query("
    SELECT 
        m.*,
        c.id_category_media,
        c.name as category_name, 
        c.icon as category_icon, 
        c.color as category_color
    FROM medias m
    LEFT JOIN categories_medias c ON m.id_category_media = c.id_category_media
    ORDER BY m.uploaded_at DESC
");

$medias = [];
while ($media = $medias_query->fetch_assoc()) {
    $medias[] = $media;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclasser les médias - ES Moulon</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            margin: 0 0 10px 0;
            color: #1f2937;
        }

        .header a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }

        .info-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #1e3a8a;
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .media-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .media-preview {
            height: 150px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .media-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .media-preview .file-icon {
            font-size: 3rem;
        }

        .media-info {
            padding: 15px;
        }

        .media-title {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .current-category {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            margin-bottom: 10px;
            color: white;
        }

        .category-select {
            width: 100%;
            padding: 8px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.85rem;
            background: white;
            cursor: pointer;
        }

        .category-select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .submit-bar {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #10b981;
            color: white;
        }

        .btn-primary:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .stats {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .legend {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .legend h3 {
            margin: 0 0 10px 0;
            font-size: 1rem;
            color: #1f2937;
        }

        .legend-items {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🗂️ Reclasser les médias</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=medias" style="color: #1e40af; text-decoration: none;">← Retour à la galerie</a>
                </p>
            </div>
        </div>

        <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['flash']['success'] ?>
            </div>
            <?php unset($_SESSION['flash']['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash']['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['flash']['error'] ?>
            </div>
            <?php unset($_SESSION['flash']['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash']['warning'])): ?>
            <div class="alert alert-warning">
                <?= $_SESSION['flash']['warning'] ?>
            </div>
            <?php unset($_SESSION['flash']['warning']); ?>
        <?php endif; ?>

        <div class="info-box">
            ℹ️ <strong>Classez vos médias par catégorie.</strong> Vous pouvez classer un seul média ou plusieurs à la fois. Seuls les médias avec une catégorie sélectionnée seront modifiés.
        </div>

        <!-- LÉGENDE DES CATÉGORIES -->
        <div class="legend">
            <h3>📋 Légende des catégories :</h3>
            <div class="legend-items">
                <?php foreach ($categories as $cat): ?>
                    <div class="legend-item">
                        <div class="legend-color" style="background: <?= $cat['color'] ?>;"></div>
                        <span><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="reclasser" value="1">

            <div class="media-grid">
                <?php foreach ($medias as $media): ?>
                    <div class="media-item">
                        <div class="media-preview">
                            <?php if (strpos($media['file_type'], 'image') !== false): ?>
                                <img src="<?= asset($media['file_path']) ?>" alt="<?= htmlspecialchars($media['file_name']) ?>">
                            <?php else: ?>
                                <div class="file-icon">
                                    <?php
                                    $ext = strtolower(pathinfo($media['file_name'], PATHINFO_EXTENSION));
                                    echo $ext === 'pdf' ? '📄' : '📁';
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="media-info">
                            <div class="media-title" title="<?= htmlspecialchars($media['file_name']) ?>">
                                <?= htmlspecialchars($media['file_name']) ?>
                            </div>

                            <?php if ($media['category_name']): ?>
                                <div class="current-category" style="background: <?= $media['category_color'] ?>;">
                                    <?= $media['category_icon'] ?> <?= htmlspecialchars($media['category_name']) ?>
                                </div>
                            <?php else: ?>
                                <div class="current-category" style="background: #ef4444;">
                                    ⚠️ Non classé
                                </div>
                            <?php endif; ?>

                            <select name="media_category[<?= $media['id_media'] ?>]" class="category-select">
                                <option value="">-- Choisir une catégorie --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id_category_media'] ?>"
                                        <?= isset($media['id_category_media']) && $media['id_category_media'] == $cat['id_category_media'] ? 'selected' : '' ?>>
                                        <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="submit-bar">
                <div class="stats">
                    📊 <strong><?= count($medias) ?></strong> médias à classer
                </div>
                <div>
                    <a href="?section=medias" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">💾 Enregistrer tous les changements</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Compte les médias modifiés
        let changedCount = 0;
        const selects = document.querySelectorAll('.category-select');
        const originalValues = new Map();

        selects.forEach(select => {
            originalValues.set(select.name, select.value);

            select.addEventListener('change', function() {
                const original = originalValues.get(this.name);
                if (this.value !== original) {
                    this.style.borderColor = '#10b981';
                    this.style.background = '#f0fdf4';
                } else {
                    this.style.borderColor = '#e5e7eb';
                    this.style.background = 'white';
                }

                // Compte les changements
                changedCount = Array.from(selects).filter(s =>
                    s.value !== originalValues.get(s.name)
                ).length;

                document.querySelector('.stats').innerHTML =
                    `📊 <strong>${changedCount}</strong> média(s) modifié(s) sur ${selects.length}`;
            });
        });

        // Confirmation avant de quitter si changements non sauvegardés
        let formSubmitted = false;

        document.querySelector('form').addEventListener('submit', function() {
            formSubmitted = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if (changedCount > 0 && !formSubmitted) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>

</html>