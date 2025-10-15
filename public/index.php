
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php
require_once __DIR__ . '/../includes/config.php';


// **********************Récupérer le dernier résultat**********************************
$stmt = $pdo->query("
    SELECT m.*, 
           home.name as home_team, 
           away.name as away_team
    FROM matches m
    LEFT JOIN teams home ON m.id_home_team = home.id_team
    LEFT JOIN teams away ON m.id_away_team = away.id_team
    WHERE m.match_date < NOW() 
    AND m.home_score IS NOT NULL
    ORDER BY m.match_date DESC
    LIMIT 1
");
$dernier_resultat = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer le prochain match
$stmt = $pdo->query("
    SELECT m.*, 
           home.name as home_team, 
           away.name as away_team
    FROM matches m
    LEFT JOIN teams home ON m.id_home_team = home.id_team
    LEFT JOIN teams away ON m.id_away_team = away.id_team
    WHERE m.match_date >= NOW()
    ORDER BY m.match_date ASC
    LIMIT 1
");
$prochain_match = $stmt->fetch(PDO::FETCH_ASSOC);


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
