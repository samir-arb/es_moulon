<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Détecte le schéma et l'hôte
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Nom du dossier projet quand tu es sur localhost
$APP_DIR = '/es_moulon';

// Sommes-nous en accès localhost ?
$isLocal = ($host === 'localhost' || $host === '127.0.0.1');

// BASE_URL sert à générer les liens de pages (front)
define('BASE_URL', $scheme . '://' . $host . ($isLocal ? $APP_DIR : ''));

// ASSETS_URL doit pointer vers /public/assets
// - en local:  http://localhost/es_moulon/public/assets
// - en vhost:  http://es_moulon.test/assets   (car docroot = /public)
define('ASSETS_URL', $scheme . '://' . $host . ($isLocal ? $APP_DIR . '/public' : '') . '/assets');

define('ROOT',  dirname(__DIR__));        // Racine du projet
define('INC',   ROOT . '/includes');      // Dossier includes
define('PAGES', ROOT . '/pages');         // Dossier pages (ajoute ça !)


// Fonctions helpers
function url(string $path=''): string   { return rtrim(BASE_URL,'/')   . '/' . ltrim($path,'/'); }
function asset(string $path=''): string { return rtrim(ASSETS_URL,'/') . '/' . ltrim($path,'/'); }


// --------------------
// Connexion MySQL
// --------------------
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli('localhost', 'root', '1508Samir@bien', 'es_moulon');
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
