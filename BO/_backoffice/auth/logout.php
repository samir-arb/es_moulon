<?php
session_start();

// Supprimer toutes les variables de session
$_SESSION = [];

// Supprimer le cookie de session si nécessaire
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// ⚡ Redémarrer une session uniquement pour stocker le flash
session_start();
$_SESSION['flash']['success'] = "Vous avez été déconnecté avec succès.";

// Redirection vers la page de login
header("Location: login.php");
exit;
