<?php
session_start();
require __DIR__ . '/../../../includes/config.php';

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $pwd   = $_POST['password'] ?? '';

    if ($token && $pwd) {
        $sql = "SELECT id_user FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $hash = password_hash($pwd, PASSWORD_BCRYPT);

            $sql = "UPDATE users 
                    SET password = ?, reset_token = NULL, reset_token_expiry = NULL 
                    WHERE id_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hash, $user['id_user']);
            $stmt->execute();

            $_SESSION['flash']['success'] = "Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.";
            header("Location: login.php");
            exit;
        } else {
            $_SESSION['flash']['danger'] = "Lien invalide ou expiré.";
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
    <title>Réinitialiser le mot de passe</title>
</head>
<body>
    <div class="auth-box">
        <h1>ES Moulon</h1>
        <h2>Nouveau mot de passe</h2>

        <?php if (!empty($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
                <div class="alert alert-<?= $type ?>"><?= $msg ?></div>
            <?php endforeach; unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="form-group">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>

            <button type="submit">Réinitialiser</button>
        </form>

        <a href="login.php" class="link">← Retour connexion</a>
    </div>
</body>
</html>
