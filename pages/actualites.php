<?php
require_once __DIR__ . '/../includes/tracker.php'; // Enregistre la visite
require_once __DIR__ . '/../includes/config.php';

// ==============================
// 📄 PAGINATION
// ==============================
$articles_par_page = 9;
$page_actuelle = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page_actuelle - 1) * $articles_par_page;

// ==============================
// 🔍 FILTRES
// ==============================
$filtre_mois = isset($_GET['mois']) ? $_GET['mois'] : '';
$filtre_annee = isset($_GET['annee']) ? (int)$_GET['annee'] : '';

// Construction de la requête avec filtres
$where_conditions = ["n.status = 1"];
$params = [];
$types = '';

if ($filtre_mois && $filtre_annee) {
    $where_conditions[] = "MONTH(n.published_at) = ? AND YEAR(n.published_at) = ?";
    $params[] = (int)$filtre_mois;
    $params[] = $filtre_annee;
    $types .= 'ii';
} elseif ($filtre_annee) {
    $where_conditions[] = "YEAR(n.published_at) = ?";
    $params[] = $filtre_annee;
    $types .= 'i';
}

$where_clause = implode(' AND ', $where_conditions);

// ==============================
// 📊 COMPTAGE TOTAL
// ==============================
$count_query = "SELECT COUNT(*) as total FROM news n WHERE $where_clause";
if (!empty($params)) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_articles = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
} else {
    $total_articles = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_articles / $articles_par_page);

// ==============================
// 📰 RÉCUPÉRATION DES ACTUALITÉS
// ==============================
$query = "
    SELECT n.id_new, n.title, n.content, n.published_at, m.file_path AS image, 
           u.first_name, u.name AS author_name
    FROM news n
    LEFT JOIN medias m ON n.id_media = m.id_media
    LEFT JOIN users u ON n.id_user = u.id_user
    WHERE $where_clause
    ORDER BY n.published_at DESC
    LIMIT ? OFFSET ?
";

$params[] = $articles_par_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$actualites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ==============================
// 📅 ARCHIVES DISPONIBLES
// ==============================
$archives_query = "
    SELECT DISTINCT 
        YEAR(published_at) as annee, 
        MONTH(published_at) as mois,
        COUNT(*) as nb_articles
    FROM news 
    WHERE status = 1
    GROUP BY annee, mois
    ORDER BY annee DESC, mois DESC
";
$archives = $conn->query($archives_query)->fetch_all(MYSQLI_ASSOC);

// ==============================
// 📌 ACTUALITÉ À LA UNE (dernière publiée)
// ==============================
$une_query = "
    SELECT n.id_new, n.title, n.content, n.published_at, m.file_path AS image,
           u.first_name, u.name AS author_name
    FROM news n
    LEFT JOIN medias m ON n.id_media = m.id_media
    LEFT JOIN users u ON n.id_user = u.id_user
    WHERE n.status = 1
    ORDER BY n.published_at DESC
    LIMIT 1
";
$actualite_une = $conn->query($une_query)->fetch_assoc();

$title = "Toutes nos actualités — ES Moulon";

// Fonction helper pour formater les mois
$mois_fr = [
    1 => 'Janvier',
    2 => 'Février',
    3 => 'Mars',
    4 => 'Avril',
    5 => 'Mai',
    6 => 'Juin',
    7 => 'Juillet',
    8 => 'Août',
    9 => 'Septembre',
    10 => 'Octobre',
    11 => 'Novembre',
    12 => 'Décembre'
];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= asset('_front.css/actualites.css') ?>">
   
</head>

<body>

    <!-- HEADER -->
    <div class="page-header">
        <div class="container">
            <h1>📰 Toutes nos Actualités</h1>
            <p>Suivez toute l'actualité de l'ES Moulon</p>
        </div>
    </div>

    <div class="container">
        <div class="main-layout">

            <!-- CONTENU PRINCIPAL -->
            <div class="main-content">

                <!-- COMPTEUR -->
                <div style="margin-bottom:30px;font-size:1.1rem;color:#6b7280;">
                    <strong style="color:#1e3a8a;"><?= $total_articles ?></strong> article<?= $total_articles > 1 ? 's' : '' ?> trouvé<?= $total_articles > 1 ? 's' : '' ?>
                    <?php if ($filtre_annee): ?>
                        en <strong><?= $filtre_annee ?></strong>
                        <?php if ($filtre_mois): ?>
                            - <strong><?= $mois_fr[$filtre_mois] ?></strong>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- GRILLE D'ACTUALITÉS -->
                <?php if (!empty($actualites)): ?>
                    <div class="news-grid">
                        <?php foreach ($actualites as $news): ?>
                            <?php
                            // Gestion du chemin image
                            $has_image = !empty($news['image']) && $news['image'] !== 'NULL';

                            if ($has_image) {
                                if (strpos($news['image'], 'uploads/') === 0) {
                                    $image_path = asset($news['image']);
                                } else {
                                    $image_path = asset('uploads/' . ltrim($news['image'], '/'));
                                }
                            } else {
                                $image_path = asset('img/default-news.jpg');
                            }
                            ?>

                            <article class="news-item">
                                <div class="news-thumbnail">
                                    <img src="<?= htmlspecialchars($image_path) ?>"
                                        alt="<?= htmlspecialchars($news['title']) ?>"
                                        loading="lazy">
                                    <div class="news-date-badge">
                                        <?= date('d M Y', strtotime($news['published_at'])) ?>
                                    </div>
                                </div>

                                <div class="news-content">
                                    <h2 class="news-title">
                                        <a href="<?= url('actualite?id=' . $news['id_new']) ?>">
                                            <?= htmlspecialchars($news['title']) ?>
                                        </a>
                                    </h2>

                                    <p class="news-excerpt">
                                        <?= nl2br(htmlspecialchars(substr($news['content'], 0, 180))) ?>...
                                    </p>

                                    <div class="news-meta">
                                        <span class="news-author">
                                            Par <?= htmlspecialchars($news['first_name'] . ' ' . $news['author_name']) ?>
                                        </span>
                                    </div>

                                    <a href="<?= url('actualite?id=' . $news['id_new']) ?>" class="btn-read-more">
                                        Lire la suite →
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page_actuelle > 1): ?>
                                <a href="?page=<?= $page_actuelle - 1 ?><?= $filtre_annee ? '&annee=' . $filtre_annee : '' ?><?= $filtre_mois ? '&mois=' . $filtre_mois : '' ?>">
                                    ← Précédent
                                </a>
                            <?php else: ?>
                                <span class="disabled">← Précédent</span>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page_actuelle): ?>
                                    <span class="active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?><?= $filtre_annee ? '&annee=' . $filtre_annee : '' ?><?= $filtre_mois ? '&mois=' . $filtre_mois : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page_actuelle < $total_pages): ?>
                                <a href="?page=<?= $page_actuelle + 1 ?><?= $filtre_annee ? '&annee=' . $filtre_annee : '' ?><?= $filtre_mois ? '&mois=' . $filtre_mois : '' ?>">
                                    Suivant →
                                </a>
                            <?php else: ?>
                                <span class="disabled">Suivant →</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-results">
                        <h3>😕 Aucune actualité trouvée</h3>
                        <p>Essayez de modifier vos filtres de recherche</p>
                        <a href="<?= url('actualites') ?>" class="btn-read-more" style="display:inline-block;margin-top:20px;">
                            Voir toutes les actualités
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SIDEBAR -->
            <aside class="sidebar">

                <!-- FILTRES -->
                <div class="sidebar-card">
                    <h3>🔍 Filtrer</h3>
                    <form method="GET" action="">
                        <div class="filter-group">
                            <label for="annee">Année</label>
                            <select name="annee" id="annee">
                                <option value="">Toutes les années</option>
                                <?php
                                $annees_disponibles = array_unique(array_column($archives, 'annee'));
                                foreach ($annees_disponibles as $annee):
                                ?>
                                    <option value="<?= $annee ?>" <?= $filtre_annee == $annee ? 'selected' : '' ?>>
                                        <?= $annee ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="mois">Mois</label>
                            <select name="mois" id="mois">
                                <option value="">Tous les mois</option>
                                <?php foreach ($mois_fr as $num => $nom): ?>
                                    <option value="<?= $num ?>" <?= $filtre_mois == $num ? 'selected' : '' ?>>
                                        <?= $nom ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn-filter">Appliquer les filtres</button>
                        <a href="<?= url('actualites') ?>" class="btn-reset">Réinitialiser</a>
                    </form>
                </div>

                <!-- ARCHIVES -->
                <div class="sidebar-card">
                    <h3>📅 Archives</h3>
                    <ul class="archive-list">
                        <?php foreach ($archives as $archive): ?>
                            <li class="archive-item">
                                <a href="?annee=<?= $archive['annee'] ?>&mois=<?= $archive['mois'] ?>">
                                    <span><?= $mois_fr[$archive['mois']] ?> <?= $archive['annee'] ?></span>
                                    <span class="archive-count"><?= $archive['nb_articles'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </aside>
        </div>
    </div>

</body>

</html>