<?php
session_start();
require_once __DIR__ . '/../../../includes/config.php';

// Vérifie d'abord si connecté avant d'afficher quoi que ce soit
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../../admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_back.css/login.css') ?>">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>ES Moulon</h1>
                <h2>Connexion Back-Office</h2>
            </div>

            <?php
            // Affichage des messages flash
            if (isset($_SESSION['flash'])) {
                foreach ($_SESSION['flash'] as $type => $message) {
                    echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($message) . '</div>';
                }
                unset($_SESSION['flash']);
            }
            ?>

            <form action="connexion.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= isset($_SESSION['old_email']) ? htmlspecialchars($_SESSION['old_email']) : '' ?>"
                        required
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn-login">Se connecter</button>
            </form>

            <div class="login-footer">
                <a href="forgot.php" class="link">Mot de passe oublié ?</a>

            </div>
        </div>
    </div>
    <?php
    // Nettoyer old_email APRÈS affichage du formulaire
    if (isset($_SESSION['old_email'])) {
        $old_email_value = $_SESSION['old_email'];
        unset($_SESSION['old_email']);
    }
    ?>
</body>

</html>