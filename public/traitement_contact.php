<?php
// ================================
// 🔒 Sécurité & Configuration
// ================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';

// 🛡️ GÉNÉRATION TOKEN CSRF (si pas déjà fait)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Configuration Mailpit (envoi local)
ini_set('SMTP', 'localhost');
ini_set('smtp_port', 1025);
ini_set('sendmail_from', 'noreply@es-moulon.local');

// ================================
// 🛡️ Sécurité : anti-bot + CSRF
// ================================
// Honeypot anti-bot
if (!empty($_POST['website'])) {
    header("Location: /es_moulon/public/index.php?page=accueil");
    exit;
}

// 🛡️ VÉRIFICATION TOKEN CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['flash']['error'] = "❌ Token CSRF invalide. Tentative d'attaque détectée !";
    header("Location: /es_moulon/public/index.php?page=accueil");
    exit;
}

// ================================
// 📋 Validation basique
// ================================
if (empty($_POST['type_form'])) {
    $_SESSION['flash']['error'] = "Type de formulaire inconnu.";
    header("Location: /es_moulon/public/index.php?page=accueil");
    exit;
}

$contact_type = htmlspecialchars($_POST['type_form']); // arbitre, benevole, partenaire, contactez
$first_name = htmlspecialchars($_POST['prenom'] ?? $_POST['entreprise'] ?? '');
$last_name = htmlspecialchars($_POST['nom'] ?? $_POST['contact_nom'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$phone = htmlspecialchars($_POST['telephone'] ?? '');
$message = htmlspecialchars($_POST['motivation'] ?? $_POST['message'] ?? '');
$sent_at = date('Y-m-d H:i:s');

// Vérification des champs requis
if (empty($email) || empty($first_name)) {
    $_SESSION['flash']['error'] = "Merci de remplir tous les champs obligatoires.";
    header("Location: /es_moulon/public/Rejoignez_nous/devenir_{$contact_type}");
    exit;
}

// ================================
// 💾 Insertion en base
// ================================
try {
    $stmt = $pdo->prepare("
        INSERT INTO contacts (first_name, name, email, phone, message, contact_type, sent_at, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'en attente')
    ");
    $stmt->execute([$first_name, $last_name, $email, $phone, $message, $contact_type, $sent_at]);
} catch (PDOException $e) {
    $_SESSION['flash']['error'] = "Erreur lors de l'enregistrement du message : " . $e->getMessage();
    header("Location: /es_moulon/public/Rejoignez_nous/devenir_{$contact_type}");
    exit;
}

// ================================
// 📧 Envoi de l’email (Mailpit)
// ================================
$to = "contact@es-moulon.fr"; // adresse de réception (mail du club)
$subject = "Nouvelle demande de contact : " . ucfirst($contact_type);

$message_mail = "
    <h2>Nouvelle demande via le site ES Moulon</h2>
    <p><strong>Type :</strong> " . ucfirst($contact_type) . "</p>
    <p><strong>Nom :</strong> {$last_name}</p>
    <p><strong>Prénom / Entreprise :</strong> {$first_name}</p>
    <p><strong>Email :</strong> {$email}</p>
    <p><strong>Téléphone :</strong> {$phone}</p>
    <p><strong>Message :</strong><br>" . nl2br($message) . "</p>
    <hr>
    <p style='font-size:0.9em;color:#666;'>Message envoyé automatiquement depuis le site de l'ES Moulon.</p>
";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: ES Moulon <noreply@es-moulon.local>\r\n";

mail($to, $subject, $message_mail, $headers);

// ================================
// ✅ Confirmation + redirection
// ================================
$_SESSION['flash']['success'] = "✅ Merci $first_name ! Votre message a bien été envoyé.";

// Cas particulier pour le formulaire "Nous contacter"
if ($contact_type === 'contact') {
    header("Location: /es_moulon/public/Rejoignez_nous/nous_contactez#confirmation");
} else {
    header("Location: /es_moulon/public/Rejoignez_nous/devenir_{$contact_type}#confirmation");
}
exit;

?>
