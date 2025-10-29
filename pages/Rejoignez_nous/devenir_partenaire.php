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
    <h1 class="title">DEVENEZ PARTENAIRE DU CLUB</h1>
    <p class="subtitle">
      Associez votre entreprise aux valeurs de l’ES Moulon. Soutenez le club tout en gagnant en visibilité locale et régionale.
    </p>
  </div>
</section>

<section class="content contain">
  <h2 class="section-title">Les avantages partenaires</h2>
  <ul class="list-advantages">
    <li>Visibilité sur nos supports de communication</li>
    <li>Logo sur les maillots et panneaux publicitaires</li>
    <li>Présence sur notre site internet</li>
    <li>Invitations aux événements du club</li>
    <li>Packages adaptés à tous les budgets</li>
  </ul>
</section>

<section class="form-section contain">
  <h2 class="section-title">Formulaire de partenariat</h2>

  <form method="POST" action="/es_moulon/public/traitement_contact.php" class="contact-form">
    <input type="hidden" name="type_form" value="partenaire">
    
    <!-- 🛡️ CHAMP CSRF CACHÉ -->
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <!-- Honeypot anti-bot -->
    <input type="text" name="website" style="display:none">

    <div class="form-grid">
      <input type="text" name="entreprise" placeholder="Nom de l'entreprise *" required>
      <input type="text" name="prenom" placeholder="Nom du contact *" required>
      <input type="email" name="email" placeholder="Email *" required>
      <input type="tel" name="telephone" placeholder="Téléphone *" required>
    </div>

    <label>Type de partenariat souhaité *</label>
    <select name="type_partenariat" required>
      <option value="">Sélectionnez...</option>
      <option value="bronze">Bronze (500€/an)</option>
      <option value="argent">Argent (1000€/an)</option>
      <option value="or">Or (2000€/an)</option>
      <option value="premium">Premium (sur mesure)</option>
    </select>

    <textarea name="message" placeholder="Message (facultatif)"></textarea>

    <div class="checkbox-group">
      <label><input type="checkbox" name="accepte_conditions" required>
        J’accepte d’être recontacté concernant un partenariat avec l’ES Moulon.</label>
    </div>

    <button type="submit" class="btn-submit">Envoyer ma demande</button>
  </form>
</section>