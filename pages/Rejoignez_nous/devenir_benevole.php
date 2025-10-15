<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Devenir Bénévole - ES Moulon</title>
  <link rel="stylesheet" href="<?= asset('_front.css/formulaires.css') ?>">
</head>
<body>

<section class="hero">
  <div class="contain text-center" style="padding:80px 20px;">
    <h1 class="title">DEVIENS BÉNÉVOLE AU CLUB !</h1>
    <p class="subtitle">
      Tu veux t’investir dans ton club ? L’ES Moulon est toujours à la recherche de personnes motivées pour aider à organiser les matchs, les événements ou les actions locales.  
    </p>
  </div>
</section>

<section class="content contain">
  <h2 class="section-title">Pourquoi devenir bénévole ?</h2>
  <ul class="list-advantages">
    <li>Participer activement à la vie du club</li>
    <li>Rencontrer des passionnés</li>
    <li>Découvrir les coulisses du football</li>
    <li>Développer de nouvelles compétences</li>
    <li>Ambiance conviviale garantie !</li>
  </ul>
</section>

<section class="form-section contain">
  <h2 class="section-title">Remplis le formulaire pour devenir bénévole</h2>

  <form method="POST" action="/es_moulon/pages/traitement_formulaire.php" class="contact-form">
      <input type="hidden" name="type_form" value="benevole">
      <?php 
          if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
      ?>
      <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
      <input type="text" name="website" style="display:none">

      <div class="form-grid">
          <input type="text" name="prenom" placeholder="Prénom *" required>
          <input type="text" name="nom" placeholder="Nom *" required>
          <input type="email" name="email" placeholder="Email *" required>
          <input type="tel" name="telephone" placeholder="Téléphone *" required>
      </div>

      <label>Disponibilités *</label>
      <select name="disponibilite" required>
          <option value="">Choisir...</option>
          <option value="weekends">Week-ends uniquement</option>
          <option value="semaine">En semaine</option>
          <option value="flexible">Flexible</option>
      </select>

      <textarea name="motivation" placeholder="Pourquoi veux-tu devenir bénévole ?" required></textarea>

      <div class="checkbox-group">
          <label><input type="checkbox" name="accepte_conditions" required> 
          J’accepte que mes données soient utilisées pour traiter ma demande.</label>
      </div>

      <button type="submit" class="btn-submit">Envoyer ma candidature</button>
  </form>
</section>


</body>
</html>
