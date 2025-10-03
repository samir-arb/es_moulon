<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: _backoffice/auth/login.php");
    exit;
}

// Connexion DB
require_once __DIR__ . '/../includes/config.php';

// Fonctions utilitaires
require_once __DIR__ . '/_backoffice/_core/functions.php';

// Vérification login (dans backoffice.php)
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: _backoffice/auth/login.php');
    exit;
}


// Infos utilisateur
$user_id     = $_SESSION['user_id'];
$user_nom    = $_SESSION['name'] ?? 'Utilisateur';
$user_prenom = $_SESSION['first_name'] ?? '';
$user_role   = $_SESSION['role'] ?? 'ROLE_VISITOR';

// Initiales utilisateur
$initiales = strtoupper(substr($user_prenom, 0, 1) . substr($user_nom, 0, 1));
if (trim($initiales) === '') $initiales = 'JD';

// Rôles et menu
require_once __DIR__ . '/_backoffice/_core/roles.php';
require_once __DIR__ . '/_backoffice/_core/menu.php';

// Section demandée
$current_section = $_GET['section'] ?? 'dashboard';

// Vérification des permissions
if (!hasPermission($user_role, $current_section, $menu_items, $roles)) {
    $current_section = 'dashboard';
}

// Layout
require_once __DIR__ . '/_backoffice/_includes/header_back.php';
require_once __DIR__ . '/_backoffice/_includes/sidebar.php';

// Charger les données spécifiques au dashboard si nécessaire
if ($current_section === 'dashboard') {
    require_once __DIR__ . '/_backoffice/_core/dashboard_data.php';
}

// Chargement de la section
$sectionFile = __DIR__ . '/_backoffice/_sections/' . $current_section . '.php';
if (file_exists($sectionFile)) {
    include $sectionFile;
} else {
    echo "<p>Section non trouvée : " . htmlspecialchars($current_section) . "</p>";
}

// Scripts
require_once __DIR__ . '/_backoffice/_includes/script_back.php';





