<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Devenir Arbitre - ES Moulon</title>
  <link rel="stylesheet" href="<?= asset('_front.css/formulaires.css') ?>">
</head>

<body>

  

  <section class="hero">
    <div class="contain text-center" style="padding:80px 20px;">
      <h1 class="title">TOI AUSSI DEVIENS ARBITRE !</h1>
      <p class="subtitle">
        À l’ES Moulon, nous sommes toujours ouverts à accueillir de nouveaux passionnés pour contribuer à la vie du club.<br>
        Si tu aimes le football et souhaites le vivre autrement, l’arbitrage est une voie passionnante et essentielle au bon déroulement du jeu.
      </p>
    </div>
  </section>

  <section class="content contain">
    <h2 class="section-title">Pourquoi devenir arbitre chez nous ?</h2>
    <ul class="list-advantages">
      <li>Formation officielle prise en charge</li>
      <li>Indemnité pour chaque match arbitré</li>
      <li>Équipement fourni</li>
      <li>Affiliation UNAF couverte par le club</li>
      <li>Suivi régulier et accompagnement</li>
    </ul>
  </section>

  <section class="form-section contain">
    <h2 class="section-title">Remplis le formulaire pour rejoindre notre équipe d’arbitres</h2>

    <form method="POST" action="/es_moulon/pages/traitement_formulaire.php" class="contact-form">
      <input type="hidden" name="type_form" value="arbitre">
      <?php
      if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
      }
      ?>
      <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
      <input type="text" name="website" style="display:none">

      <div class="form-grid">
        <input type="text" name="prenom" placeholder="Prénom *" required>
        <input type="text" name="nom" placeholder="Nom *" required>
        <input type="email" name="email" placeholder="Email *" required>
        <input type="tel" name="telephone" placeholder="Téléphone *" required>
      </div>

      <label>As-tu déjà arbitré ? *</label>
      <div class="radio-group">
        <label><input type="radio" name="experience" value="jamais" required> Jamais</label>
        <label><input type="radio" name="experience" value="moins3"> Moins de 3 ans</label>
        <label><input type="radio" name="experience" value="plus3"> 3 ans ou plus</label>
      </div>

      <textarea name="motivation" placeholder="Parle-nous de tes motivations *" required></textarea>

      <div class="checkbox-group">
        <label><input type="checkbox" name="accepte_conditions" required>
          J’accepte que mes données soient utilisées pour traiter ma candidature.</label>
      </div>

      <button type="submit" class="btn-submit">Envoyer ma candidature</button>
    </form>
  </section>

  
</body>

</html>