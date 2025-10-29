<?php
// --- Sécurité & Configuration ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';

// 🛡️ GÉNÉRATION TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

 <!--  Message de confirmation (si redirection après envoi) -->
  <?php if (isset($_SESSION['flash']['success'])): ?>
    <div id="confirmation" style="
        background:#d1fae5;
        color:#065f46;
        border:1px solid #6ee7b7;
        padding:15px;
        border-radius:8px;
        margin:20px auto;
        max-width:800px;
        text-align:center;
        font-weight:500;
    ">
      <?= $_SESSION['flash']['success'] ?>
    </div>
    <script>
      setTimeout(() => {
        const msg = document.getElementById('confirmation');
        if (msg) msg.style.opacity = '0';
        setTimeout(() => msg?.remove(), 600);
      }, 4000);
    </script>
    <?php unset($_SESSION['flash']['success']); ?>
  <?php endif; ?>


  <section class="hero">
    <div class="contain text-center" style="padding:80px 20px;">
      <h1 class="title">TOI AUSSI DEVIENS ARBITRE !</h1>
      <p class="subtitle">
        L’ES Moulon accueille de nouveaux arbitres pour faire vivre le jeu avec passion
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

    <form method="POST" action="/es_moulon/public/traitement_contact.php" class="contact-form">
      <input type="hidden" name="type_form" value="arbitre">
      
      <!-- 🛡️ CHAMP CSRF CACHÉ -->
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      
      <!-- Honeypot anti-bot -->
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