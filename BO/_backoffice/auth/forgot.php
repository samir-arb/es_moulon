<?php
session_start();
require __DIR__ . '/../../../includes/config.php'; // ajuste si besoin (2 ou 3 niveaux)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $sql = "SELECT id_user FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Générer un token
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update = $conn->prepare("UPDATE users SET valid_token=?, token_reset_expires_at=? WHERE id_user=?");
            $update->bind_param("ssi", $token, $expires, $user['id_user']);
            $update->execute();

            // Lien de reset
            $resetLink = BASE_URL . "/BO/_backoffice/auth/reset.php?token=" . $token;

            $_SESSION['flash']['success'] = "Un lien de réinitialisation a été généré : <a href='$resetLink'>$resetLink</a>";
        } else {
            $_SESSION['flash']['danger'] = "Aucun compte trouvé avec cet email.";
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
