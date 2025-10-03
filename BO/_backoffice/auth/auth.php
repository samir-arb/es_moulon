<?php

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: login.php');
    exit;
}

// Expiration de session après 1 heures d'inactivité
$timeout = 3600; // 1 heures en secondes
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
    session_destroy();
    $_SESSION['flash']['warning'] = "Votre session a expiré. Veuillez vous reconnecter.";
    header('Location: login.php');
    exit;
}

// Mettre à jour le temps d'activité
$_SESSION['login_time'] = time();
