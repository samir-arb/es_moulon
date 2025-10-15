<?php
session_start();

// ===========================================================
// SÉCURITÉ : Redirection si non connecté
// ===========================================================
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: _backoffice/auth/login.php");
    exit;
}

// ===========================================================
// CHARGEMENT DES DÉPENDANCES
// ===========================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/_backoffice/_core/functions.php';
require_once __DIR__ . '/_backoffice/_core/roles.php';
require_once __DIR__ . '/_backoffice/_core/menu.php';

// ===========================================================
// INFORMATIONS UTILISATEUR
// ===========================================================
$user_id     = $_SESSION['user_id'] ?? null;
$user_nom    = $_SESSION['name'] ?? 'Utilisateur';
$user_prenom = $_SESSION['first_name'] ?? '';
$user_role   = $_SESSION['role'] ?? 'ROLE_VISITOR';

// Initiales pour l'affichage (avatar par ex.)
$initiales = strtoupper(substr($user_prenom, 0, 1) . substr($user_nom, 0, 1));
if (trim($initiales) === '') $initiales = 'JD';

// ===========================================================
// DÉTERMINER LA SECTION COURANTE
// ===========================================================
$current_section = $_GET['section'] ?? 'dashboard';

//   : Redirection "resultats" vers "calendrier"
if ($current_section === 'resultats') {
    $current_section = 'calendrier';
}

// Vérification des permissions selon le rôle
if (!hasPermission($user_role, $current_section, $menu_items, $roles)) {
    $current_section = 'dashboard';
}

// Fichier de la section
$sectionFile = __DIR__ . '/_backoffice/_sections/' . $current_section . '.php';

// ===========================================================
// TRAITEMENT DES ACTIONS AVANT AFFICHAGE (POST / DELETE / etc.)
// ===========================================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    || isset($_GET['delete'])
    || isset($_GET['toggle_status'])
    || isset($_GET['reset_password'])
    || isset($_GET['set_status'])
    || isset($_FILES['media_file'])
) {
    include $sectionFile;
    exit;
}

// ===========================================================
// CHARGEMENT DES DONNÉES DU TABLEAU DE BORD (si nécessaire)
// ===========================================================
if ($current_section === 'dashboard') {
    require_once __DIR__ . '/_backoffice/_core/dashboard_data.php';
}

// ===========================================================
// AFFICHAGE DU TEMPLATE ADMIN (header + sidebar + contenu + scripts)
// ===========================================================
require_once __DIR__ . '/_backoffice/_includes/header_back.php';
require_once __DIR__ . '/_backoffice/_includes/sidebar.php';

// Section demandée
if (file_exists($sectionFile)) {
    include $sectionFile;
} else {
    echo "<p style='padding:20px;color:#888;'>Section non trouvée.</p>";
}

require_once __DIR__ . '/_backoffice/_includes/script_back.php';