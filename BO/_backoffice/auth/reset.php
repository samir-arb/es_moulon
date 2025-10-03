<?php
session_start();
require '../../includes/config.php';

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $pwd   = $_POST['password'] ?? '';

    if ($token && $pwd) {
        $sql = "SELECT id_user FROM users WHERE token_reset = ? AND token_reset_expires_at > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $hash = password_hash($pwd, PASSWORD_BCRYPT);

            $sql = "UPDATE users 
                    SET password = ?, token_reset = NULL, token_reset_expires_at = NULL 
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
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
    <style><?php include '../public/assets/css/login.css'; ?></style>
</head>
<body>
    <div class="login-box">
        <h1>Réinitialisation du mot de passe</h1>

        <?php if (isset($_SESSION['flash'])) { 
            foreach ($_SESSION['flash'] as $t => $m) echo "<div class='alert alert-$t'>$m</div>"; 
            unset($_SESSION['flash']); 
        } ?>

        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="form-group">
                <label>Nouveau mot de passe :</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Réinitialiser</button>
        </form>
    </div>
</body>
</html>
