<?php
require_once __DIR__ . '/../includes/tracker.php'; // Enregistre la visite
require_once __DIR__ . '/../includes/config.php';

// ==============================
// 📰 RÉCUPÉRATION DE L'ACTUALITÉ
// ==============================
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header('Location: ' . url('actualites'));
    exit;
}

$stmt = $conn->prepare("
    SELECT n.*, m.file_path AS image, u.first_name, u.name AS author_name
    FROM news n
    LEFT JOIN medias m ON n.id_media = m.id_media
    LEFT JOIN users u ON n.id_user = u.id_user
    WHERE n.id_new = ? AND n.status = 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Si l'article n'existe pas ou n'est pas publié
if (!$article) {
    header('Location: ' . url('?page=actualites'));
    exit;
}

// ==============================
// 📚 ARTICLES SIMILAIRES
// ==============================
$stmt = $conn->prepare("
    SELECT n.id_new, n.title, n.published_at, m.file_path AS image
    FROM news n
    LEFT JOIN medias m ON n.id_media = m.id_media
    WHERE n.id_new != ? AND n.status = 1
    ORDER BY n.published_at DESC
    LIMIT 3
");
$stmt->bind_param('i', $id);
$stmt->execute();
$articles_similaires = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Gestion de l'image principale
$has_image = !empty($article['image']) && $article['image'] !== 'NULL';
if ($has_image) {
    if (strpos($article['image'], 'uploads/') === 0) {
        $image_path = asset($article['image']);
    } else {
        $image_path = asset('uploads/' . ltrim($article['image'], '/'));
    }
} else {
    $image_path = asset('img/default-news.jpg');
}

$title = htmlspecialchars($article['title']) . " — ES Moulon";
?>



<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f8f9fa;
        color: #1f2937;
        line-height: 1.8;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* BREADCRUMB */
    .breadcrumb {
        padding: 80px 0;
        font-size: 0.9rem;
    }

    .breadcrumb a {
        color: #3b82f6;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .breadcrumb a:hover {
        color: #1e3a8a;
    }

    .breadcrumb span {
        color: #9ca3af;
        margin: 0 10px;
    }

    /* ARTICLE HEADER */
    .article-header {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }

    .article-image {
        width: 100%;
        height: 500px;
        object-fit: cover;
    }

    .article-header-content {
        padding: 40px;
    }

    .article-meta {
        display: flex;
        gap: 30px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        font-size: 0.95rem;
        color: #6b7280;
    }

    .article-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .article-meta-item strong {
        color: #374151;
    }

    .article-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1f2937;
        line-height: 1.2;
        margin-bottom: 20px;
    }

    /* CONTENU ARTICLE */
    .article-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 40px;
        margin-bottom: 60px;
    }

    .article-content {
        background: white;
        border-radius: 16px;
        padding: 50px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .article-content p {
        margin-bottom: 20px;
        font-size: 1.1rem;
        line-height: 1.8;
        color: #374151;
    }

    .article-content h2 {
        font-size: 1.8rem;
        margin-top: 40px;
        margin-bottom: 20px;
        color: #1e3a8a;
    }

    .article-content h3 {
        font-size: 1.4rem;
        margin-top: 30px;
        margin-bottom: 15px;
        color: #374151;
    }

    .article-content ul,
    .article-content ol {
        margin-left: 30px;
        margin-bottom: 20px;
    }

    .article-content li {
        margin-bottom: 10px;
        color: #374151;
    }

    /* SIDEBAR */
    .sidebar {
        position: sticky;
        top: 20px;
        height: fit-content;
    }

    .sidebar-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .sidebar-card h3 {
        font-size: 1.2rem;
        margin-bottom: 20px;
        color: #1e3a8a;
        border-bottom: 3px solid #3b82f6;
        padding-bottom: 10px;
    }

    .share-buttons {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .share-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .share-btn:hover {
        transform: translateX(5px);
    }

    .share-facebook {
        background: #1877f2;
        color: white;
    }

    .share-twitter {
        background: #1da1f2;
        color: white;
    }

    .share-email {
        background: #6b7280;
        color: white;
    }

    .share-print {
        background: #374151;
        color: white;
    }

    /* ARTICLES SIMILAIRES */
    .related-article {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .related-article:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .related-thumbnail {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .related-info h4 {
        font-size: 1rem;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .related-info h4 a {
        text-decoration: none;
        color: #1f2937;
        transition: color 0.3s ease;
    }

    .related-info h4 a:hover {
        color: #3b82f6;
    }

    .related-date {
        font-size: 0.85rem;
        color: #9ca3af;
    }

    /* BOUTON RETOUR */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 24px;
        background: #3b82f6;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 40px;
    }

    .btn-back:hover {
        background: #2563eb;
        transform: translateX(-5px);
    }

    /* RESPONSIVE */
    @media (max-width: 1024px) {
        .article-layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: static;
        }

        .article-content {
            padding: 30px;
        }

        .article-image {
            height: 350px;
        }
    }

    @media (max-width: 768px) {
        .article-title {
            font-size: 1.8rem;
        }

        .article-header-content {
            padding: 25px;
        }

        .article-content {
            padding: 20px;
        }

        .article-content p {
            font-size: 1rem;
        }

        .article-image {
            height: 250px;
        }
    }

    @media print {

        .breadcrumb,
        .btn-back,
        .sidebar,
        .share-buttons {
            display: none !important;
        }
    }
</style>


<div class="container">

    <!-- BREADCRUMB -->
    <div class="breadcrumb">
        <a href="<?= url('') ?>">Accueil</a>
        <span>›</span>
        <a href="<?= url('?pages=actualites') ?>">Actualités</a>

        <span>›</span>
        <strong><?= htmlspecialchars($article['title']) ?></strong>
    </div>

    <!-- BOUTON RETOUR -->
    <a href="<?= url('?pages=actualites') ?>" class="btn-back">
        ← Retour aux actualités
    </a>

    <!-- HEADER ARTICLE -->
    <article class="article-header">
        <?php if ($has_image): ?>
            <img src="<?= htmlspecialchars($image_path) ?>"
                alt="<?= htmlspecialchars($article['title']) ?>"
                class="article-image">
        <?php endif; ?>

        <div class="article-header-content">
            <div class="article-meta">
                <div class="article-meta-item">
                    <span>📅</span>
                    <strong>Publié le</strong>
                    <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                </div>
                <div class="article-meta-item">
                    <span>✍️</span>
                    <strong>Par</strong>
                    <?= htmlspecialchars($article['first_name'] . ' ' . $article['author_name']) ?>
                </div>
                <?php if (!empty($article['updated_at'])): ?>
                    <div class="article-meta-item">
                        <span>🔄</span>
                        <strong>Mis à jour le</strong>
                        <?= date('d/m/Y à H:i', strtotime($article['updated_at'])) ?>
                    </div>
                <?php endif; ?>
            </div>

            <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
        </div>
    </article>

    <!-- CONTENU + SIDEBAR -->
    <div class="article-layout">

        <!-- CONTENU PRINCIPAL -->
        <div class="article-content">
            <?= nl2br(htmlspecialchars($article['content'])) ?>
        </div>

        <!-- SIDEBAR -->
        <aside class="sidebar">

            <!-- PARTAGE -->
            <div class="sidebar-card">
                <h3>📤 Partager</h3>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('?page=actualite_detail&id=' . $article['id_new'])) ?>"
                        target="_blank"
                        class="share-btn share-facebook">
                        <span>📘</span> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['title']) ?>&url=<?= urlencode(url('?page=actualite_detail&id=' . $article['id_new'])) ?>"
                        target="_blank"
                        class="share-btn share-twitter">
                        <span>🐦</span> Twitter
                    </a>
                    <a href="mailto:?subject=<?= urlencode($article['title']) ?>&body=<?= urlencode($article['title'] . ' - ' . url('?page=actualite_detail&id=' . $article['id_new'])) ?>"
                        class="share-btn share-email">
                        <span>📧</span> Email
                    </a>
                    <a href="javascript:window.print()"
                        class="share-btn share-print">
                        <span>🖨️</span> Imprimer
                    </a>
                </div>
            </div>

            <!-- ARTICLES SIMILAIRES -->
            <?php if (!empty($articles_similaires)): ?>
                <div class="sidebar-card">
                    <h3>📚 À lire aussi</h3>
                    <?php foreach ($articles_similaires as $related): ?>
                        <?php
                        // Gestion image article similaire
                        if (!empty($related['image'])) {
                            if (strpos($related['image'], 'uploads/') === 0) {
                                $related_img = asset($related['image']);
                            } else {
                                $related_img = asset('uploads/' . ltrim($related['image'], '/'));
                            }
                        } else {
                            $related_img = asset('img/default-news.jpg');
                        }
                        ?>
                        <div class="related-article">
                            <img src="<?= htmlspecialchars($related_img) ?>"
                                alt="<?= htmlspecialchars($related['title']) ?>"
                                class="related-thumbnail">
                            <div class="related-info">
                                <h4>
                                    <a href="<?= url('?page=actualite_detail&id=' . $related['id_new']) ?>">
                                        <?= htmlspecialchars($related['title']) ?>
                                    </a>
                                </h4>
                                <div class="related-date">
                                    <?= date('d/m/Y', strtotime($related['published_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- NEWSLETTER (optionnel) -->
            <div class="sidebar-card" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white;">
                <h3 style="color: white; border-color: rgba(255,255,255,0.3);">📬 Newsletter</h3>
                <p style="margin-bottom: 15px; font-size: 0.95rem;">
                    Ne manquez aucune actualité de l'ES Moulon
                </p>
                <a href="<?= url('Rejoignez_nous/nous_contactez') ?>"
                    style="display: block; text-align: center; padding: 12px; background: white; color: #1e3a8a; border-radius: 8px; text-decoration: none; font-weight: 700; transition: all 0.3s ease;">
                    S'inscrire
                </a>
            </div>

        </aside>
    </div>

    <!-- BOUTON RETOUR (bas de page) -->
    <a href="<?= url('?page=actualites') ?>" class="btn-back">
        ← Retour aux actualités
    </a>

</div>

</body>

</html>