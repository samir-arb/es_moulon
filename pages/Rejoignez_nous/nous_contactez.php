<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact - ES Moulon</title>
  <link rel="stylesheet" href="<?= asset('_front.css/formulaires.css') ?>">
</head>
<body>


<section class="hero">
  <div class="contain text-center" style="padding:80px 20px;">
    <h1 class="title">CONTACTEZ-NOUS</h1>
    <p class="subtitle">
      Une question ? Une remarque ? N’hésitez pas à nous écrire, nous vous répondrons dans les plus brefs délais.
    </p>
  </div>
</section>

<section class="form-section contain">
  <h2 class="section-title">Formulaire de contact</h2>

  <form method="POST" action="/es_moulon/pages/traitement_formulaire.php" class="contact-form">
      <input type="hidden" name="type_form" value="contact">
      <?php 
          if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
      ?>
      <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
      <input type="text" name="website" style="display:none">

      <div class="form-grid">
          <input type="text" name="prenom" placeholder="Prénom *" required>
          <input type="text" name="nom" placeholder="Nom *" required>
          <input type="email" name="email" placeholder="Email *" required>
          <input type="tel" name="telephone" placeholder="Téléphone">
      </div>

      <select name="sujet" required>
          <option value="">Sélectionnez un sujet...</option>
          <option value="inscription">Inscription</option>
          <option value="info">Demande d'information</option>
          <option value="reclamation">Réclamation</option>
          <option value="autre">Autre</option>
      </select>

      <textarea name="message" placeholder="Votre message *" required></textarea>

      <div class="checkbox-group">
          <label><input type="checkbox" name="accepte_conditions" required> 
          J’accepte que mes données soient utilisées pour traiter ma demande.</label>
      </div>

      <button type="submit" class="btn-submit">Envoyer le message</button>
  </form>
</section>

</body>
</html>
