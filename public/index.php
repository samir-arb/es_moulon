<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php
require_once __DIR__ . '/../includes/config.php';

// ========== RÉCUPÉRATION DES DONNÉES POUR LES CARDS AVEC LOGOS ==========

// 1. DERNIER RÉSULTAT (match déjà joué avec score + logos)
$stmt = $pdo->query("
    SELECT 
        m.id_match,
        m.match_date,
        m.home_score,
        m.away_score,
        m.match_type,
        m.location,
        
        -- Équipe domicile
        th.name AS home_team_name,
        th.id_club_team AS home_is_club,
        th.level AS home_level,
        mh.file_path AS home_logo,
        
        -- Équipe extérieur
        ta.name AS away_team_name,
        ta.id_club_team AS away_is_club,
        ta.level AS away_level,
        ma.file_path AS away_logo
        
    FROM matches m
    INNER JOIN teams th ON m.id_home_team = th.id_team
    INNER JOIN teams ta ON m.id_away_team = ta.id_team
    LEFT JOIN medias mh ON th.id_media = mh.id_media
    LEFT JOIN medias ma ON ta.id_media = ma.id_media
    
    WHERE (th.id_club_team = 1 OR ta.id_club_team = 1)
      AND m.home_score IS NOT NULL
      AND m.away_score IS NOT NULL
      AND m.match_date < NOW()
    
    ORDER BY m.match_date DESC
    LIMIT 1
");
$dernier_resultat = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. PROCHAIN MATCH (match à venir sans score + logos)
$stmt = $pdo->query("
    SELECT 
        m.id_match,
        m.match_date,
        m.match_type,
        m.location,
        
        -- Équipe domicile
        th.name AS home_team_name,
        th.id_club_team AS home_is_club,
        th.level AS home_level,
        mh.file_path AS home_logo,
        
        -- Équipe extérieur
        ta.name AS away_team_name,
        ta.id_club_team AS away_is_club,
        ta.level AS away_level,
        ma.file_path AS away_logo
        
    FROM matches m
    INNER JOIN teams th ON m.id_home_team = th.id_team
    INNER JOIN teams ta ON m.id_away_team = ta.id_team
    LEFT JOIN medias mh ON th.id_media = mh.id_media
    LEFT JOIN medias ma ON ta.id_media = ma.id_media
    
    WHERE (th.id_club_team = 1 OR ta.id_club_team = 1)
      AND m.match_date >= NOW()
    
    ORDER BY m.match_date ASC
    LIMIT 1
");
$prochain_match = $stmt->fetch(PDO::FETCH_ASSOC);

/**
 * Fonction pour afficher le logo d'une équipe
 */
function displayTeamLogo($logo_path, $team_name, $is_club) {
    if (!empty($logo_path)) {
        // Logo existe dans la BDD
        return '<img src="' . asset($logo_path) . '" alt="' . htmlspecialchars($team_name) . '">';
    } else {
        // Pas de logo : afficher emoji
        $emoji = $is_club ? '⚽' : '🔴';
        return '<span class="team__logo-emoji">' . $emoji . '</span>';
    }
}

//******************************************************************************************************************** */

// Chemin de la requête (ex: /es_moulon/public/accueil)
$uriPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Base de l'app (ex: /es_moulon/public)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // /es_moulon/public

// On enlève la base du chemin (pour ne garder que "accueil", "Regional1/calendrier", etc.)
if (strpos($uriPath, $basePath) === 0) {
  $path = substr($uriPath, strlen($basePath));
} else {
  $path = $uriPath;
}
$path = trim($path, '/');

// Page par défaut
if ($path === '' || $path === 'index.php') { 
  $path = 'accueil'; 
}

// table de routage
$map = [
  'accueil'                        => PAGES.'/accueil.php',
  'actualites'                     => PAGES.'/actualites.php',
  'actualite'                      => PAGES.'/actualite_detail.php',  
  'partenaires'                    => PAGES.'/partenaires.php',
  'Le_club/histoire_et_valeurs'    => PAGES.'/Le_club/histoire_et_valeurs.php',
  'Le_club/infos_pratiques'        => PAGES.'/Le_club/infos_pratiques.php',
  'Le_club/organigrammes'          => PAGES.'/Le_club/organigrammes.php',
  'Le_club/nos_arbitres'           => PAGES.'/Le_club/nos_arbitres.php',
  'Le_club/nos_benevols'           => PAGES.'/Le_club/nos_benevols.php',
  'Nos_equipes/seniors'            => PAGES.'/Nos_equipes/seniors.php',
  'Nos_equipes/ecole_de_foot'      => PAGES.'/Nos_equipes/ecole_de_foot.php',
  'Nos_equipes/pole_pre_formation' => PAGES.'/Nos_equipes/pole_pre_formation.php',
  'Nos_equipes/pole_formation'     => PAGES.'/Nos_equipes/pole_formation.php',
  'Regional1/calendrier'           => PAGES.'/Regional1/calendrier.php',
  'Regional1/classement'           => PAGES.'/Regional1/classement.php',
  'Regional1/effectif_et_staff'    => PAGES.'/Regional1/effectif_et_staff.php',
  'Rejoignez_nous/devenir_arbitre' => PAGES.'/Rejoignez_nous/devenir_arbitre.php',
  'Rejoignez_nous/devenir_benevole'=> PAGES.'/Rejoignez_nous/devenir_benevole.php',
  'Rejoignez_nous/devenir_partenaire'=> PAGES.'/Rejoignez_nous/devenir_partenaire.php',
  'Rejoignez_nous/nous_contactez'  => PAGES.'/Rejoignez_nous/nous_contactez.php',
  'mentions'                       => PAGES.'/mentions.php',
  'confidentialite'                => PAGES.'/confidentialite.php',
  'droits'                         => PAGES.'/droits.php',
];

$file = $map[$path] ?? null;

// rendu avec header/navbar/footer automatiques
$title = 'ES Moulon';
ob_start();
if ($file && is_file($file)) {
  include $file;
} else {
  http_response_code(404);
  echo '<h1>404</h1><p>Page introuvable.</p>';
}
$content = ob_get_clean();

include INC.'/header.php';
include INC.'/navbar.php';
echo '<main>'.$content.'</main>';
include INC.'/footer.php';
include INC.'/scripts.php';