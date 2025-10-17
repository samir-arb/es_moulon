<header class="header">
  <!-- Logo médaille (prend les fichiers depuis /public/assets/...) -->
  <div class="logo-badge">
    <img src="<?= asset('img/logo_moulon.jpg') ?>" alt="ES Moulon">
  </div>

  <!-- Bande noire -->
  <div class="topbar">
    <div class="container topbar-row">
      <p class="slogan">En vert, et contre tous !</p>

      <div class="utils">
        <a href="https://www.intersport-clubs.fr/" class="shop">La Boutique
          <img class="icon" src="<?= asset('picto/icons8-panier-30.png') ?>" alt="Panier">
        </a>

        <span class="sep"></span>

        <span class="follow">Suivez-nous sur</span>
        <div class="socials">
          <a href="https://www.facebook.com/profile.php?id=100070081914815&sk=about&locale=fr_FR" aria-label="Facebook">
            <img src="<?= asset('picto/icons8-facebook-nouveau-60.png') ?>" alt="Facebook">
          </a>
          <a href="https://www.instagram.com/e.s_moulon/reels/" aria-label="Instagram">
            <img src="<?= asset('picto/icons8-instagram-64.png') ?>" alt="Instagram">
          </a>
          <a href="#" aria-label="YouTube">
            <img src="<?= asset('picto/icons8-youtube-50.png') ?>" alt="YouTube">
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== NAVBAR ===== -->
  <nav class="navbar">
    <div class="container">
      <ul class="menu">
        <li><a href="<?= url() ?>">Accueil</a></li>

        <li class="has-submenu">
          <a href="<?= url() ?>">Le club <span class="chevron">▼</span></a>
          <ul class="submenu submenu--stacked">
            <li><a href="<?= url('Le_club/histoire_et_valeurs') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Histoire et valeurs
              </a></li>
            <li><a href="<?= url('Le_club/infos_pratiques') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Infos pratiques
              </a></li>
            <li><a href="<?= url('Le_club/organigrammes') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Organigrammes
              </a></li>
            <li><a href="<?= url('Le_club/nos_arbitres') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Nos arbitres
              </a></li>
            <li><a href="<?= url('Le_club/nos_benevols') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Nos bénévoles
              </a></li>
          </ul>
        </li>

        <li class="has-submenu">
          <a href="<?= url() ?>">Régional 1 <span class="chevron">▼</span></a>
          <ul class="submenu submenu--stacked">
            <li><a href="<?= url('Regional1/effectif_et_staff') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-foot-de-plage-30.png') ?>" alt=""> Effectif et staff
              </a></li>
            <li><a href="<?= url('Regional1/calendrier') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-foot-de-plage-30.png') ?>" alt=""> Calendrier & Résultats
              </a></li>
            <li><a href="<?= url('Regional1/classement') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-foot-de-plage-30.png') ?>" alt=""> Classement
              </a></li>
          </ul>
        </li>

        <li class="has-submenu">
          <a href="<?= url() ?>">Nos équipes <span class="chevron">▼</span></a>
          <ul class="submenu submenu--stacked">
            <li><a href="<?= url('Nos_equipes/ecole_de_foot') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-foot-de-plage-30.png') ?>" alt=""> École de foot
              </a></li>
            <li><a href="<?= url('Nos_equipes/pole_pre_formation') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-foot-de-plage-30.png') ?>" alt=""> Pôle pré-formation
              </a></li>
            <li><a href="<?= url('Nos_equipes/pole_formation') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-foot-de-plage-30.png') ?>" alt=""> Pôle formation
              </a></li>
            <li><a href="<?= url('Nos_equipes/seniors') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-foot-de-plage-30.png') ?>" alt=""> Séniors
              </a></li>
          </ul>
        </li>

        <li><a href="<?= url('partenaires') ?>">Partenaires</a></li>
        <li><a href="<?= url('actualites') ?>">Actualités</a></li>

        <li class="has-submenu">
          <a href="<?= url() ?>">Rejoignez-nous <span class="chevron">▼</span></a>
          <ul class="submenu submenu--stacked">
            <li><a href="<?= url('Rejoignez_nous/devenir_arbitre') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Devenir arbitre
              </a></li>
            <li><a href="<?= url('Rejoignez_nous/devenir_benevole') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Devenir bénévole
              </a></li>
            <li><a href="<?= url('Rejoignez_nous/devenir_partenaire') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Devenir partenaire
              </a></li>
            <li><a href="<?= url('Rejoignez_nous/nous_contactez') ?>">
                <img class="bullet" src="<?= asset('picto/icons8-football-2-32.png') ?>" alt=""> Nous contactez
              </a></li>
          </ul>
        </li>
      </ul>
    </div>
    <!-- Bouton burger -->
    <div class="burger" id="burger">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </nav>
</header>