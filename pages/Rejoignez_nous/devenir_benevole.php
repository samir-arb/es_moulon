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

  <form method="POST" action="/es_moulon/public/traitement_contact.php" class="contact-form">
      <input type="hidden" name="type_form" value="benevole">
      
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


