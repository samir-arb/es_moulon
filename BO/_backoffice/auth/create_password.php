<?php
session_start();
require '../../includes/config.php';

$error = '';
$success = '';
$user = null;

// Vérifier le token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        $error = "Ce lien est invalide ou a expiré.";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if (empty($password) || empty($password_confirm)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL, is_password_set = 1 WHERE reset_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $hashed_password, $token);
        
        if ($stmt->execute()) {
            $success = "Votre mot de passe a été créé avec succès ! Vous pouvez maintenant vous connecter.";
            // Redirection après 3 secondes
            header("refresh:3;url=login.php");
        } else {
            $error = "Erreur lors de la création du mot de passe.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer votre mot de passe - ES Moulon</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
        }
        h1 { color: #1e3a8a; margin-bottom: 8px; font-size: 1.8rem; }
        h2 { color: #6b7280; margin-bottom: 30px; font-size: 1rem; font-weight: normal; }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #1e40af;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #1e40af; }
        .info {
            background: #eff6ff;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #1e40af;
            margin-bottom: 20px;
        }
        .info strong { color: #1e40af; }
    </style>
</head>
<body>
    <div class="container">
        <h1>ES Moulon</h1>
        <h2>Créer votre mot de passe</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?><br><small>Redirection automatique...</small></div>
        <?php elseif ($user): ?>
            <div class="info">
                <strong>Bienvenue <?= htmlspecialchars($user['first_name'] . ' ' . $user['name']) ?> !</strong><br>
                Créez votre mot de passe pour accéder au back-office.
            </div>

            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input type="password" id="password" name="password" required minlength="6" 
                           placeholder="Minimum 6 caractères">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6"
                           placeholder="Retapez votre mot de passe">
                </div>

                <button type="submit" name="create_password">Créer mon mot de passe</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                Ce lien est invalide ou a expiré.<br>
                <a href="login.php" style="color: #991b1b; font-weight: 600;">Retour à la connexion</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>