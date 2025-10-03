<?php
// BO/_backoffice/auth/connexion.php
session_start();

// Config DB
require_once __DIR__ . '/../../../includes/config.php';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Récupération des données
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    $_SESSION['flash']['danger'] = "Veuillez remplir tous les champs.";
    $_SESSION['old_email'] = $email;
    header('Location: login.php');
    exit;
}

// Vérification format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash']['danger'] = "Format d'email invalide.";
    $_SESSION['old_email'] = $email;
    header('Location: login.php');
    exit;
}

// Requête utilisateur
$sql = "
    SELECT 
        u.id_user, 
        u.name, 
        u.first_name, 
        u.email, 
        u.password, 
        u.status, 
        u.id_role, 
        r.role_name
    FROM users u
    INNER JOIN roles r ON u.id_role = r.id_role
    WHERE u.email = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['flash']['danger'] = "Erreur système. Réessayez plus tard.";
    header('Location: login.php');
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Utilisateur non trouvé
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['flash']['danger'] = "Identifiants ou mot de passe incorrects.";
    $_SESSION['old_email'] = $email;
    usleep(500000);
    header('Location: login.php');
    exit;
}

// Compte désactivé
if ((int)$user['status'] !== 1) {
    $_SESSION['flash']['warning'] = "Votre compte est désactivé.";
    header('Location: login.php');
    exit;
}

// Bloquer l'accès d'un rôle particulier
if ($user['role_name'] === 'ROLE_LICENSED') {
    $_SESSION['flash']['warning'] = "Vous n'avez pas accès au back-office.";
    header('Location: login.php');
    exit;
}

// CONNEXION OK
session_regenerate_id(true);

$_SESSION['user_id']    = (int)$user['id_user'];
$_SESSION['name']       = $user['name'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['email']      = $user['email'];
$_SESSION['role']       = $user['role_name'];
$_SESSION['logged_in']  = true;
$_SESSION['login_time'] = time();

$_SESSION['flash']['success'] = "Bienvenue " . htmlspecialchars($user['first_name']) . " !";

// Redirection vers le backoffice principal
header('Location: ../../backoffice.php');
exit;
