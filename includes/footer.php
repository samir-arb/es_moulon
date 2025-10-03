<footer class="footer" role="contentinfo">
  <div class="footer-top">
    <div class="logo-footer">
      <img src="<?= asset('img/logo_moulon.jpg') ?>" alt="Logo ES Moulon">
    </div>

    <nav class="socials-footer" aria-label="Réseaux sociaux">
      <a href="https://www.facebook.com/profile.php?id=100070081914815" aria-label="Facebook">
        <img src="<?= asset('picto/icons8-facebook-nouveau-60.png') ?>" alt="Facebook">
      </a>
      <a href="https://www.instagram.com/e.s_moulon/reels/" aria-label="Instagram">
        <img src="<?= asset('picto/icons8-instagram-64.png') ?>" alt="Instagram">
      </a>
      <a href="#" aria-label="YouTube">
        <img src="<?= asset('picto/icons8-youtube-50.png') ?>" alt="YouTube">
      </a>
      <a href="#" aria-label="TikTok">
        <img src="<?= asset('picto/icons8-tic-tac-64.png') ?>" alt="TikTok">
      </a>
      <a href="#" aria-label="Snapchat">
        <img src="<?= asset('picto/icons8-snapchat-50.png') ?>" alt="Snapchat">
      </a>
    </nav>
  </div>

  <div class="footer-columns">
    <!-- Colonne 1 -->
    <section class="footer-col contact" aria-labelledby="ft-contact">
      <h3 class="footer-title green" id="ft-contact">NOUS CONTACTER</h3>
      <ul class="contact-list">
        <li class="contact-item">
          <img src="<?= asset('picto/icons8-téléphone-32.png') ?>" class="contact-icon" alt="" aria-hidden="true">
          <span>02 48 65 28 15</span>
        </li>
        <li class="contact-item">
          <img src="<?= asset('picto/icons8-courrier-32.png') ?>" class="contact-icon" alt="" aria-hidden="true">
          <a href="mailto:contact@esmoulon.fr">contact@esmoulon.fr</a>
        </li>
        <li class="contact-item">
          <img src="<?= asset('picto/icons8-adresse-32.png') ?>" class="contact-icon" alt="" aria-hidden="true">
          <address>Rue de la Sente aux Loups,<br>18000 BOURGES</address>
        </li>
      </ul>
    </section>

    <!-- Colonne 2 -->
    <nav class="footer-col" aria-labelledby="ft-club">
      <h3 class="footer-title green" id="ft-club">LE CLUB</h3>
      <ul class="footer-links">
        <li><a href="<?= url('Le_club/histoire_et_valeurs') ?>">Histoire et valeurs</a></li>
        <li><a href="<?= url('Le_club/infos_pratiques') ?>">Infos pratiques</a></li>
        <li><a href="<?= url('Le_club/organigrammes') ?>">Organigrammes</a></li>
        <li><a href="<?= url('Le_club/nos_arbitres') ?>">Nos arbitres</a></li>
        <li><a href="<?= url('Le_club/nos_benevols') ?>">Nos bénévoles</a></li>
      </ul>
    </nav>

    <!-- Colonne 3 -->
    <nav class="footer-col" aria-labelledby="ft-equipes">
      <h3 class="footer-title green" id="ft-equipes">NOS ÉQUIPES</h3>
      <ul class="footer-links">
        <li><a href="<?= url('Regional1') ?>">Régionale 1</a></li>
        <li><a href="<?= url('Nos_equipes/seniors') ?>">Séniors</a></li>
        <li><a href="<?= url('Nos_equipes/pole_formation') ?>">Pôle formation</a></li>
        <li><a href="<?= url('Nos_equipes/pole_pre_formation') ?>">Pôle pré-formation</a></li>
        <li><a href="<?= url('Nos_equipes/ecole_de_foot') ?>">École de foot</a></li>
      </ul>
    </nav>
  </div>

  <div class="footer-bottom">
    <p>
      ©2025 / ES Moulon —
      <a href="<?= url('droits') ?>">Tous droits réservés</a> |
      <a href="<?= url('mentions') ?>">Mentions légales</a> |
      <a href="<?= url('confidentialite') ?>">Politique de confidentialité</a> |
      Design by sams
    </p>
  </div>
</footer>
