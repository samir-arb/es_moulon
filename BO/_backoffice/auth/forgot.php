<?php
session_start();
require __DIR__ . '/../../../includes/config.php';
require __DIR__ . '/../../../includes/EmailService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $sql = "SELECT id_user, name, first_name FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Générer un token
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update = $conn->prepare("UPDATE users SET reset_token=?, reset_token_expiry=? WHERE id_user=?");
            $update->bind_param("ssi", $token, $expires, $user['id_user']);
            $update->execute();

            // Lien de reset (sans /public car le BO est à la racine)
            $resetLink = $scheme . '://' . $host . $APP_DIR . "/BO/_backoffice/auth/reset.php?token=" . $token;

            // Envoyer l'email via Mailpit
            $emailService = new EmailService();
            $subject = "Réinitialisation de votre mot de passe - ES Moulon";
            
            $message = "Bonjour {$user['first_name']} {$user['name']},\n\n";
            $message .= "Vous avez demandé la réinitialisation de votre mot de passe.\n\n";
            $message .= "Cliquez sur ce lien pour créer un nouveau mot de passe :\n";
            $message .= $resetLink . "\n\n";
            $message .= "Ce lien est valide pendant 1 heure.\n\n";
            $message .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
            $message .= "Cordialement,\n";
            $message .= "L'équipe ES Moulon";
            
            $result = $emailService->send($email, $subject, $message);
            
            if ($result['success']) {
                $_SESSION['flash']['success'] = "Un email de réinitialisation a été envoyé à votre adresse email.";
                
                // En dev, afficher le lien Mailpit
                if ($mailpitUrl = $emailService->getMailpitUrl()) {
                    $_SESSION['flash']['info'] = "📧 Consultez vos emails sur <a href='$mailpitUrl' target='_blank'>Mailpit</a>";
                }
            } else {
                $_SESSION['flash']['warning'] = "Le lien a été généré mais l'envoi de l'email a échoué. Contactez l'administrateur.";
            }
        } else {
            // Email n'existe pas
            $_SESSION['flash']['danger'] = "❌ Aucun compte n'a été trouvé avec cet email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="<?= asset('_back.css/login.css') ?>">
  <link rel="stylesheet" href="<?= asset('_back.css/dashboard.css') ?>">
  <meta charset="UTF-8">
  <title>Mot de passe oublié</title>
</head>
<body>
  <div class="auth-box">
    <h1>ES Moulon</h1>
    <h2>Réinitialisation du mot de passe</h2>

    <?php if (!empty($_SESSION['flash'])): ?>
        <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
            <div class="alert alert-<?= $type ?>"><?= $msg ?></div>
        <?php endforeach; unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form action="forgot.php" method="POST"> <!-- ✅ corrigé -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit">Envoyer</button>
    </form>

    <a href="login.php" class="link">← Retour connexion</a>
  </div>
</body>
</html>
