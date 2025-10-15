<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =============================
// Démarrage de la session
// =============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// =============================
// Détection du contexte
// =============================
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Nom du dossier projet (à adapter si besoin)
$APP_DIR = '/es_moulon';

// Environnement local ou distant
$isLocal = ($host === 'localhost' || $host === '127.0.0.1');

// =============================
// URL de base du site
// =============================
// En local → http://localhost/es_moulon/public
// En ligne → https://esmoulon.fr
define('BASE_URL', $scheme . '://' . $host . ($isLocal ? $APP_DIR . '/public' : ''));

// =============================
// URL des assets
// =============================
// En local → http://localhost/es_moulon/public/assets
// En ligne → https://esmoulon.fr/assets
define('ASSETS_URL', rtrim(BASE_URL, '/') . '/assets');

// =============================
// Chemins serveurs
// =============================
define('ROOT',  dirname(__DIR__));
define('INC',   ROOT . '/includes');
define('PAGES', ROOT . '/pages');

// =============================
// Fonctions helpers
// =============================

function asset(string $path = ''): string {
    // Si le chemin commence déjà par 'assets/', on ne rajoute rien
    if (strpos($path, 'assets/') === 0) {
        return BASE_URL . '/' . ltrim($path, '/');
    }

    // Si c’est un chemin d’upload (sans 'assets/')
    if (strpos($path, 'uploads/') === 0) {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }

    // Sinon, comportement par défaut
    return rtrim(ASSETS_URL, '/') . '/' . ltrim($path, '/');
}


// Pour les fichiers uploadés dynamiquement (photos joueurs, logos, etc.)
define('UPLOADS_URL', rtrim(BASE_URL, '/') . '/uploads');

/**
 * Retourne une URL correcte vers un fichier (upload ou asset)
 * - "uploads/photo.jpg" → /uploads/photo.jpg
 * - "img/logo.png"      → /assets/img/logo.png
 * - "https://..."       → inchangé
 */
function file_url(string $path=''): string {
    // Si c’est déjà une URL complète
    if (preg_match('#^https?://#i', $path)) return $path;

    // Si le chemin commence par "uploads/"
    if (strpos($path, 'uploads/') === 0) {
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }

    // Sinon on considère que c’est un fichier statique
    return asset($path);
}

// Helper de base pour générer des liens internes
function url(string $path=''): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

// =============================
// Connexion MySQLi
// =============================
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli('localhost', 'root', '1508Samir@bien', 'es_moulon');
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    die('Erreur de connexion MySQL : ' . $e->getMessage());
}

// =============================
// Connexion PDO
// =============================
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=es_moulon;charset=utf8mb4',
        'root',
        '1508Samir@bien',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion PDO : ' . $e->getMessage());
}
