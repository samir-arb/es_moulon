
<!-- "J’ai structuré mon dashboard en deux blocs : les statistiques simples et les statistiques avancées.
Seuls les administrateurs accèdent aux statistiques avancées, calculées dynamiquement à partir de la table des visites.
Chaque sous-bloc (évolution, top pages, moyenne, navigateurs, etc.) est encapsulé dans un try/catch global pour éviter tout plantage du tableau de bord en cas d’erreur SQL.
Le code est compatible avec MySQL strict (only_full_group_by) et respecte une approche MVC : le calcul se fait dans dashboard_data.php et l’affichage dans dashboard.php." -->
<?php

/**
 * _backoffice/_core/dashboard_data.php
 * 
 * Ce fichier prépare toutes les données du tableau de bord :
 * - Statistiques simples (licenciés, matchs, actualités, etc.)
 * - Statistiques avancées (visites, top pages, navigateurs...)
 * 
 * Un cache JSON de 10 minutes est utilisé pour éviter de recalculer
 * toutes les requêtes SQL à chaque affichage.
 */

global $pdo, $user_role;

// ============================================================
// 🔄 RAFRAÎCHIR LE CACHE MANUELLEMENT (admin uniquement)
// ============================================================
if (isset($_GET['refresh']) && $_GET['refresh'] == '1' && $user_role === 'ROLE_ADMIN') {
    $cacheFile = __DIR__ . '/dashboard_cache.json';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
        $_SESSION['flash']['success'] = "✅ Les statistiques ont été rafraîchies avec succès !";
    } else {
        $_SESSION['flash']['success'] = "✅ Les statistiques sont à jour !";
    }
    // Recharge la page pour recalculer les stats
    header("Location: admin.php?section=dashboard");
    exit;
}


// ===========================================================
// 🕒 SYSTÈME DE CACHE (10 minutes)
// ===========================================================
$cacheFile = __DIR__ . '/dashboard_cache.json';
$cacheDuration = 600; // en secondes (600 = 10 minutes)

if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheDuration)) {
    $cachedData = json_decode(file_get_contents($cacheFile), true);
    if (is_array($cachedData)) {
        $realStats     = $cachedData['realStats'];
        $stats         = $cachedData['stats'];
        $activities    = $cachedData['activities'];
        $statsAvancees = $cachedData['statsAvancees'];
        return; // ✅ On arrête ici : les données proviennent du cache
    }
}

// ===========================================================
// 🧮 CALCUL DES STATISTIQUES (si pas de cache)
// ===========================================================

// Fonction utile
function formatTime($datetime) {
    if (empty($datetime)) return 'Date inconnue';
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return "Il y a {$diff}s";
    if ($diff < 3600) return "Il y a " . floor($diff / 60) . " min";
    if ($diff < 86400) return "Il y a " . floor($diff / 3600) . " h";
    if ($diff < 604800) return "Il y a " . floor($diff / 86400) . " j";
    return date('d/m/Y', $timestamp);
}

$realStats = [];
$messagesNonLus = 0;
$convocationsEnAttente = 0;
$activities = [];

try {
    // Messages non lus
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM contacts WHERE status = 'en attente'");
    $messagesNonLus = $stmt->fetchColumn();

    // Convocations en attente
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM convocations");
    $convocationsEnAttente = $stmt->fetchColumn();

    // Licenciés
    $stmt = $pdo->query("SELECT COUNT(DISTINCT id_user) FROM users_club_functions");
    $realStats['licencies'] = $stmt->fetchColumn();

    // Joueurs
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT ucf.id_user)
        FROM users_club_functions ucf
        INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
        WHERE LOWER(cf.function_name) LIKE '%joueur%' OR LOWER(cf.function_name) LIKE '%player%'
    ");
    $realStats['joueurs'] = $stmt->fetchColumn();

    // Actualités
    $stmt = $pdo->query("SELECT COUNT(id_new) FROM news WHERE published_at IS NOT NULL");
    $realStats['actualites'] = $stmt->fetchColumn();

    // Matchs à venir
    $stmt = $pdo->query("SELECT COUNT(*) FROM matches WHERE match_date >= NOW()");
    $realStats['matchs'] = $stmt->fetchColumn();

    // Médias
    $stmt = $pdo->query("SELECT COUNT(*) FROM medias");
    $realStats['medias'] = $stmt->fetchColumn();

    // Équipes
    $stmt = $pdo->query("SELECT COUNT(*) FROM teams");
    $realStats['equipes'] = $stmt->fetchColumn();

    // Partenaires
    $stmt = $pdo->query("SELECT COUNT(*) FROM partners");
    $realStats['partenaires'] = $stmt->fetchColumn();

    // Visites aujourd'hui
    $stmt = $pdo->query("SELECT COUNT(*) FROM visites WHERE DATE(date_visite) = CURDATE()");
    $realStats['visites_aujourdhui'] = $stmt->fetchColumn();

    // Visites ce mois
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM visites
        WHERE MONTH(date_visite) = MONTH(CURDATE()) AND YEAR(date_visite) = YEAR(CURDATE())
    ");
    $realStats['visites_mois'] = $stmt->fetchColumn();

    // Total visites
    $stmt = $pdo->query("SELECT COUNT(*) FROM visites");
    $realStats['visites_total'] = $stmt->fetchColumn();

    // Visiteurs uniques
    $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM visites");
    $realStats['visiteurs_uniques'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Erreur SQL (dashboard) : " . $e->getMessage());
    $realStats = array_fill_keys([
        'licencies','joueurs','actualites','matchs','medias',
        'equipes','partenaires','visites_aujourdhui','visites_mois',
        'visites_total','visiteurs_uniques'
    ], 0);
}

// ===========================================================
// 🎯 STATISTIQUES PAR RÔLE
// ===========================================================
$stats = [];

switch ($user_role) {
    case 'ROLE_ADMIN':
        $stats = [
            ['label' => 'Visites aujourd\'hui', 'value' => $realStats['visites_aujourdhui'], 'color' => '#3b82f6', 'icon' => '👥'],
            ['label' => 'Visites ce mois', 'value' => $realStats['visites_mois'], 'color' => '#9b59b6', 'icon' => '📊'],
            ['label' => 'Visiteurs uniques', 'value' => $realStats['visiteurs_uniques'], 'color' => '#1abc9c', 'icon' => '🌐'],
            ['label' => 'Messages en attente', 'value' => $messagesNonLus, 'color' => '#ef4444', 'icon' => '📧', 'notification' => $messagesNonLus > 0],
            ['label' => 'Convocations', 'value' => $convocationsEnAttente, 'color' => '#f59e0b', 'icon' => '📝'],
            ['label' => 'Licenciés', 'value' => $realStats['licencies'], 'color' => '#10b981', 'icon' => '👥'],
            ['label' => 'Joueurs', 'value' => $realStats['joueurs'], 'color' => '#22c55e', 'icon' => '⚽'],
            ['label' => 'Actualités', 'value' => $realStats['actualites'], 'color' => '#6366f1', 'icon' => '📄'],
            ['label' => 'Matchs à venir', 'value' => $realStats['matchs'], 'color' => '#f97316', 'icon' => '🏆'],
            ['label' => 'Équipes', 'value' => $realStats['equipes'], 'color' => '#14b8a6', 'icon' => '👕'],
            ['label' => 'Médias', 'value' => $realStats['medias'], 'color' => '#8b5cf6', 'icon' => '📸'],
            ['label' => 'Partenaires', 'value' => $realStats['partenaires'], 'color' => '#ec4899', 'icon' => '🤝']
        ];
        break;
    default:
        $stats = [['label' => 'Aucune donnée disponible', 'value' => '-', 'color' => '#999', 'icon' => '❌']];
}

// ===========================================================
// 📈 STATISTIQUES AVANCÉES (ADMIN UNIQUEMENT)
// ===========================================================
$statsAvancees = [];

if ($user_role === 'ROLE_ADMIN') {
    try {
        // Évolution sur 6 mois
        $stmt = $pdo->query("
            SELECT DATE_FORMAT(date_visite, '%Y-%m') AS mois,
                   DATE_FORMAT(MIN(date_visite), '%M %Y') AS mois_nom,
                   COUNT(*) AS total
            FROM visites
            WHERE date_visite >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(date_visite, '%Y-%m')
            ORDER BY mois ASC
        ");
        $visitesMois = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $moisFr = [
            'January'=>'Janvier','February'=>'Février','March'=>'Mars','April'=>'Avril','May'=>'Mai','June'=>'Juin',
            'July'=>'Juillet','August'=>'Août','September'=>'Septembre','October'=>'Octobre','November'=>'Novembre','December'=>'Décembre'
        ];

        $maxVisites = !empty($visitesMois) ? max(array_column($visitesMois, 'total')) : 1;
        $precedent = null;
        $statsAvancees['visites_par_mois'] = [];

        foreach ($visitesMois as $v) {
            foreach ($moisFr as $en=>$fr) $v['mois_nom'] = str_replace($en,$fr,$v['mois_nom']);
            $v['pourcentage'] = ($v['total'] / $maxVisites) * 100;
            $v['evolution'] = $precedent ? round((($v['total'] - $precedent) / $precedent) * 100) : null;
            $precedent = $v['total'];
            $statsAvancees['visites_par_mois'][] = $v;
        }

        // Top pages
        $stmt = $pdo->query("
            SELECT page_url AS page, COUNT(*) AS total
            FROM visites
            WHERE page_url IS NOT NULL AND page_url <> ''
            GROUP BY page_url
            ORDER BY total DESC
            LIMIT 5
        ");
        $statsAvancees['top_pages'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Moyenne jour
        $stmt = $pdo->query("
            SELECT ROUND(COUNT(*) / 30, 1) AS moyenne
            FROM visites
            WHERE date_visite >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $statsAvancees['moyenne_jour'] = $stmt->fetchColumn();

        // Total semaine
        $stmt = $pdo->query("
            SELECT COUNT(*) FROM visites
            WHERE YEARWEEK(date_visite, 1) = YEARWEEK(CURDATE(), 1)
        ");
        $statsAvancees['total_semaine'] = $stmt->fetchColumn();

        // Jour le plus visité
        $stmt = $pdo->query("
            SELECT DAYNAME(date_visite) AS jour, COUNT(*) AS total
            FROM visites
            WHERE date_visite >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY jour
            ORDER BY total DESC
            LIMIT 1
        ");
        $jour = $stmt->fetch(PDO::FETCH_ASSOC);
        $joursFr = ['Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi','Sunday'=>'Dimanche'];
        $statsAvancees['meilleur_jour'] = $jour ? ['nom'=>$joursFr[$jour['jour']] ?? $jour['jour'], 'total'=>$jour['total']] : ['nom'=>'N/A','total'=>0];

        // Navigation moyenne
        $stmt = $pdo->query("SELECT ROUND(COUNT(*) / COUNT(DISTINCT ip_address), 1) FROM visites");
        $statsAvancees['navigation_moyenne'] = $stmt->fetchColumn();

        // Navigateurs
        $stmt = $pdo->query("
            SELECT browser, COUNT(*) AS total,
            ROUND((COUNT(*) * 100 / (SELECT COUNT(*) FROM visites)), 1) AS pourcentage
            FROM visites
            WHERE browser IS NOT NULL AND browser <> ''
            GROUP BY browser
            ORDER BY total DESC
            LIMIT 5
        ");
        $statsAvancees['navigateurs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Erreur SQL (stats avancées) : " . $e->getMessage());
    }
}

// ===========================================================
// 💾 SAUVEGARDE DU CACHE
// ===========================================================
file_put_contents($cacheFile, json_encode([
    'realStats'     => $realStats,
    'stats'         => $stats,
    'activities'    => $activities,
    'statsAvancees' => $statsAvancees
], JSON_PRETTY_PRINT));

