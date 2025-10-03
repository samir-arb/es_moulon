<?php $title = "Accueil — ES Moulon"; ?>


<section class="home-hero" aria-label="Présentation du club">
  <div class="home-hero__bg" style="--hero:url('<?= asset('img/photo_accueil.jpg') ?>');">
  </div>

  <div class="home-hero__content container">
    <p class="home-hero__eyebrow">Bienvenue à</p>
    <h1 class="home-hero__title">L’ES <span>Moulon</span></h1>
    <p class="home-hero__lead">Depuis 1940, notre club s’engage à développer le football local
      et à promouvoir les valeurs du sport au&nbsp;quotidien.</p>
    <div class="home-hero__cta">
      <a href="<?= url('Rejoignez_nous/nous_contactez') ?>" class="btn btn-gradient">Rejoignez-nous</a>
    </div>
  </div>
</section>
<!-- ===== FIN HERO ===== -->

<!-- ===== DEBUT CARTES ===== -->

<section class="cards-grid">

  <!-- CARTE 1 : Derniers résultats -->
  <div class="card card--gradient">
    <header class="card__head">
      <img src="<?= asset('picto\icons8-coup-de-pied-de-football-64.png') ?>" alt="National 3" class="badge-league">
      <h3>DERNIERS RÉSULTATS</h3>
    </header>

    <p class="card__sub">CHAMPIONNAT</p>
    <p class="card__date">SAM 17 MAI · 18 h</p>
    <p class="card__place"><a href="https://www.google.com/maps/dir//74+Rue+de+Turly,+18000+Bourges/@47.0996187,2.3381636,12z/data=!4m8!4m7!1m0!1m5!1m1!1s0x47fa974a70888763:0xd776182e06bacdd8!2m2!1d2.420571!2d47.0996388?hl=fr&entry=ttu&g_ep=EgoyMDI1MDkwMy4wIKXMDSoASAFQAw%3D%3D" target="_blank" class="link">Stade Yves du Manoir</a></p>

    <div class="card__teams">
      <div class="team">
        <img src="<?= asset('img/logo_moulon.jpg') ?>" alt="ES Moulon Bourges" class="team__logo">
        <div class="team__name">ES Moulon<br>Bourges</div>
      </div>

      <div class="score-box">
        <span>2</span><span>1</span>
      </div>

      <div class="team">
        <img src="<?= asset('img/us-orleans-loiret-vector-logo-115738423260qa4jjtuck.png') ?>" alt="US Orléans Loiret 2" class="team__logo">
        <div class="team__name">US Orléans<br>Loiret 2</div>
      </div>
    </div>

    <div class="card__foot">
      <a class="btn-pill" href="https://scorenco.com/football/clubs/es-moulon-bourges-2htd/1-q3o" target="_blank">Tous les Résultats →</a>
    </div>
  </div>

  <!-- CARTE 2 : Prochaines rencontres -->
  <div class="card card--gradient">
    <header class="card__head">
      <h3>PROCHAINES RENCONTRES</h3>
    </header>

    <p class="card__sub">AMICAL</p>
    <p class="card__date">SAM 27 JUIL · 18 h</p>
    <p class="card__place"><a href="<?= url('') ?>" target="_blank" class="link">Stade municipal de Vineuil</a></p>

    <div class="card__teams">
      <div class="team">
        <img src="<?= asset('img/logo_vineuil.png') ?>" alt="Vineuil Sport Football" class="team__logo">
        <div class="team__name">Vineuil Sport<br>Football</div>
      </div>

      <div class="score-box">
        <span>-</span><span>-</span>
      </div>

      <div class="team">
        <img src="<?= asset('img/logo_moulon.jpg') ?>" alt="ES Moulon Bourges" class="team__logo">
        <div class="team__name">ES Moulon<br>Bourges</div>
      </div>
    </div>

    <div class="card__foot">
      <a class="btn-pill" href="https://scorenco.com/football/clubs/es-moulon-bourges-2htd/1-q3o" target="_blank">Tous les Calendriers →</a>
    </div>
  </div>

  <!-- CARTE 3 : Classement  -->
  <div class="card card--gradient">
    <header class="card__head">
      <img src="<?= asset('img\logo R1.webp') ?>" alt="Régional 1" class="badge-league">
      <h3>CLASSEMENT</h3>
    </header>

    <div class="standings">
      <div class="row">
        <span class="pos">1</span>
        <span class="club">US Exemple</span>
        <span class="pts">19 pts</span>
      </div>
      <div class="row esm">
        <span class="pos">2</span>
        <span class="club">ES Moulon</span>
        <span class="pts">17 pts</span>
      </div>
      <div class="row">
        <span class="pos">3</span>
        <span class="club">FC Démo</span>
        <span class="pts">15 pts</span>
      </div>
      <div class="row">
        <span class="pos">4</span>
        <span class="club">AS Test</span>
        <span class="pts">13 pts</span>
      </div>
      <div class="row">
        <span class="pos">5</span>
        <span class="club">US Ville</span>
        <span class="pts">10 pts</span>
      </div>
    </div>

    <div class="card__foot">
      <a class="btn-pill" href="https://scorenco.com/football/clubs/es-moulon-bourges-2htd/1-q3o" target="_blank">Tous les Classements →</a>
    </div>
  </div>

</section>

<!-- ===== FIN CARTES ===== -->

<!-- ===== ACTUALITÉS ===== -->

<section class="news-block" aria-labelledby="news-title">
  <div class="container">

    <header class="news-head">
      <h2 id="news-title">AU COEUR DE L’ESM</h2>
      <p class="news-sub">Découvrez nos actualités pour suivre l’ESM</p>
      <div class="news-sep">
        <span class="dash"></span>
        <img src="<?= asset('img\logo_moulon.jpg') ?>" alt="" aria-hidden="true">
        <span class="dash_2"></span>
      </div>
    </header>

    <div class="news-grid">
      <!-- Article mis en avant -->
      <article class="news-feature">
        <a class="thumb" href="<?= url('actualites') ?>">
          <img src="<?= asset('img\U13.jpg') ?>" alt="Vainqueurs des finales départementales">
        </a>
        <div class="content">
          <h3 class="title">
            <a href="<?= url('actualites') ?>">🏆 Vainqueurs des finales départementales et consolante de nos U13.</a>
          </h3>
          <p class="excerpt">
            Sous le soleil d’Aubigny-sur-Nère, nos jeunes loups ont brillé par leur travaille, leur esprit d’équipe et
            leur détermination. Félicitations aux deux équipes pour cette double victoire bien méritée ! 🐺💚
          </p>
          <time class="date" datetime="2025-06-21">21 juin 2025</time>
        </div>
      </article>

      <!-- 4 petites cartes -->
      <article class="news-card">
        <a class="thumb" href="<?= url('actualites') ?>">
          <img src="<?= asset('img\detection.jpg') ?>" alt="Détection Sénior R1">
        </a>
        <div class="content">
          <h4 class="title"><a href="<?= url('actualites') ?>">🔍 Détection Sénior R1</a></h4>
          <p class="description">
            L'ES Moulon organise une journée de détection pour son équipe sénior, qui évoluera en Régional 1 avec l'ambition de remonter en National 3 ...
          </p>
          <time class="date" datetime="2025-06-18">18 juin 2025</time>
        </div>
      </article>

      <article class="news-card">
        <a class="thumb" href="<?= url('actualites') ?>">
          <img src="<?= asset('img\D1_vainqueur_coupe.jpg') ?>" alt="Le moulon champion de la coupe">
        </a>
        <div class="content">
          <h4 class="title"><a href="<?= url('actualites') ?>">🏆 Le Moulon champion de la coupe Robert Feigenblum</a></h4>
          <p class="description">
            Face à une belle équipe de Trouy, Nos loups s'imposent 3-1 grâce à des buts de N.Berger, F. Cherkaoui et R. Farhan ...
          </p>
          <time class="date" datetime="2025-06-14">14 juin 2025</time>
        </div>
      </article>

      <article class="news-card">
        <a class="thumb" href="<?= url('actualites') ?>">
          <img src="<?= asset('img\U18.jpg') ?>" alt="Un match nul frustrant">
        </a>
        <div class="content">
          <h4 class="title"><a href="<?= url('actualites') ?>">⚽ match nul pour nos U18</a></h4>
          <p class="description">
            Match décisif pour l’accession en U18R1 : nos U16 R2 font match nul face à Foot Sud 41 (1-1), dans une rencontre tendue marquée par deux penalties transformés ...
          </p>
          <time class="date" datetime="2025-06-12">12 juin 2025</time>
        </div>
      </article>

      <article class="news-card">
        <a class="thumb" href="<?= url('actualites') ?>">
          <img src="<?= asset('img\frustrant.jpg') ?>" alt="Une saison frustrante">
        </a>
        <div class="content">
          <h4 class="title"><a href="<?= url('actualites') ?>">📅 Une saison frustrante</a></h4>
          <p class="description">
            Nos loups s’imposent par 2 buts à 1 face à la réserve de l’US Orléans dans un match à suspense où les visiteurs ont pu ouvrir le score en première mi-temps ...
          </p>
          <time class="date" datetime="2025-06-07">7 juin 2025</time>
        </div>
      </article>
    </div>

    <div class="news-cta">
      <a class="btn-gradient" href="<?= url('actualites') ?>">Toutes nos Actualités →</a>
    </div>

  </div>
</section>
<!-- ===== FIN ACTUALITÉS ===== -->


<!-- ======== LE CLUB ============== -->

<section class="club-hero" aria-label="Présentation du club"
  style="--club-bg: url('<?= asset('img/fond_ecran_esm.png') ?>')">

  <!-- deco LE CLUB -->
  <img class="club-hero__script"
    src="<?= asset('img/Le_club-titre.png') ?>"
    alt="" aria-hidden="true">

  <div class="club-hero__text">
    <p class="club-hero__eyebrow">En vert, et contre tous !</p>
  </div>

  <div class="club-hero_grid">
    <h2 class="club-hero__title">
      Quelques mots à propos<br>de notre club
    </h2>

    <p class="club-hero__lead">
      Découvrez notre histoire, nos valeurs et notre équipe dirigeante.
    </p>

    <div class="news-cta">
      <a class="btn-gradient" href="<?= url('histoire_et_valeurs') ?>">Decouvrez l'ESM →</a>
    </div>
  </div>

  <!-- Colonne  joueurs -->
  <div class="club-hero__players">
    <img src="<?= asset('img/capture_le_club.png') ?>"
      alt="Joueurs de l’ES Moulon">
  </div>

</section>
<!-- ========== FIN LE CLUB ========== -->

<!-- ========= PARTENAIRES =========== -->

<section class="partners" aria-labelledby="partners-title">
  <div class="container">
    <header class="partners__head">
      <div class="partners__line" aria-hidden="true"></div>    <!-- line d'oré -->
      <h2 id="partners-title" class="partners__title">
        <span class="gold">Nos partenaires</span> <span class="green">officiels</span>
      </h2>
    </header>

    <div class="partners__wrap">
      <!-- Flèche gauche -->
      <!-- <button class="partners__arrow left" type="button" aria-label="Défiler vers la gauche">
        ‹‹
      </button> -->

      <!-- Carrousel -->
      <div class="partners__rail" id="partnersRail" tabindex="0" aria-label="Logos des partenaires">
        <!-- 5 logos (ajoute/retire au besoin) -->
        <a class="partner" href="#" target="_blank" rel="noopener">
          <img src="<?= asset('img\food_market.png') ?>" alt="foodmarket">
        </a>
        <a class="partner" href="#" target="_blank" rel="noopener">
          <img src="<?= asset('img\Monin-Logo.png') ?>" alt="Monin">
        </a>
        <a class="partner" href="#" target="_blank" rel="noopener">
          <img src="<?= asset('img\VSAJ.png') ?>" alt="VSAJ">
        </a>
        <a class="partner" href="#" target="_blank" rel="noopener">
          <img src="<?= asset('img\InterSport-Embleme.png') ?>" alt="Intersport">
        </a>
        <a class="partner" href="#" target="_blank" rel="noopener">
          <img src="<?= asset('img\AB-CLIM-LOGO.png') ?>" alt="AB Climatisation">
        </a>

      </div>

      <!-- Flèche droite -->
      <!-- <button class="partners__arrow right" type="button" aria-label="Défiler vers la droite">
        ››
      </button> -->
    </div>

    <p class="partners__intro">
      L’ES Moulon ne serait pas ce qu’il est aujourd’hui sans le soutien généreux de nos sponsors.
    </p>

    <div class="partners__cta">
      <div class="news-cta">
        <a class="btn-gradient" href="<?= url('partenaires') ?>">Tous nos Partenaires →</a>
        <a class="btn-gradient" href="<?= url('Rejoignez_nous\devenir_partenaire.php') ?>">Devenir Partenaire →</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ FIN PARTENAIRES ================= -->