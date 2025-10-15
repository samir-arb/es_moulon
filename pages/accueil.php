<?php
require_once __DIR__ . '/../includes/tracker.php'; // Enregistre la visite
require_once __DIR__ . '/../includes/config.php';

// ==============================
// 1️⃣ DERNIER RÉSULTAT
// ==============================
try {
    $stmt = $pdo->query("
        SELECT m.*, home.name AS home_team_name, away.name AS away_team_name
        FROM matches m
        LEFT JOIN teams home ON m.id_home_team = home.id_team
        LEFT JOIN teams away ON m.id_away_team = away.id_team
        WHERE m.match_date < NOW() AND m.home_score IS NOT NULL
        ORDER BY m.match_date DESC LIMIT 1
    ");
    $dernier_resultat = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dernier_resultat = null;
}

// ==============================
// 2️⃣ PROCHAIN MATCH
// ==============================
try {
    $stmt = $pdo->query("
        SELECT m.*, home.name AS home_team_name, away.name AS away_team_name
        FROM matches m
        LEFT JOIN teams home ON m.id_home_team = home.id_team
        LEFT JOIN teams away ON m.id_away_team = away.id_team
        WHERE m.match_date >= NOW()
        ORDER BY m.match_date ASC LIMIT 1
    ");
    $prochain_match = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $prochain_match = null;
}

// ==============================
// 3️⃣ ACTUALITÉS
// ==============================
try {
    $stmt = $pdo->query("
        SELECT n.id_new, n.title, n.content, n.published_at, m.file_path AS image
        FROM news n
        LEFT JOIN medias m ON n.id_media = m.id_media
        WHERE n.status = 1
        ORDER BY n.published_at DESC
        LIMIT 5
    ");
    $actualites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erreur chargement actualités : ' . $e->getMessage());
    $actualites = [];
}

// ==============================
// 4️⃣ PARTENAIRES
// ==============================
try {
    $stmt = $pdo->query("
        SELECT p.company_name, p.redirect_url, m.file_path AS logo
        FROM partners p
        LEFT JOIN medias m ON p.id_media = m.id_media
        WHERE p.is_active = 1
        ORDER BY p.display_order ASC
    ");
    $partenaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erreur chargement partenaires : ' . $e->getMessage());
    $partenaires = [];
}

$title = "Accueil — ES Moulon";
?>



<!-- ========================= HERO ========================= -->



<section class="home-hero" aria-label="Présentation du club">
    <div class="home-hero__bg" style="--hero:url('<?= asset('img/photo_accueil.jpg') ?>');"></div>
    <div class="home-hero__content container">
        <p class="home-hero__eyebrow">Bienvenue à</p>
        <h1 class="home-hero__title">L'ES <span>Moulon</span></h1>
        <p class="home-hero__lead">Depuis 1940, notre club s'engage à développer le football local et à promouvoir les valeurs du sport.</p>
        <div class="home-hero__cta">
            <a href="<?= url('Rejoignez_nous/nous_contactez') ?>" class="btn btn-gradient">Rejoignez-nous</a>
        </div>
    </div>
</section>



<!-- ========================= CARTES RÉSULTATS/MATCHS ========================= -->


<section class="cards-grid">
    <!-- Dernier résultat -->
    <div class="card card--gradient">
        <header class="card__head">
            <img src="<?= asset('picto/icons8-coup-de-pied-de-football-64.png') ?>" alt="Football" class="badge-league">
            <h3>DERNIERS RÉSULTATS</h3>
        </header>

        <?php if ($dernier_resultat): ?>
            <p class="card__sub"><?= strtoupper(htmlspecialchars($dernier_resultat['match_type'])); ?></p>
            <p class="card__date"><?= date('d/m/Y', strtotime($dernier_resultat['match_date'])); ?></p>
            <div class="card__teams">
                <div class="team"><?= htmlspecialchars($dernier_resultat['home_team_name']); ?> <strong><?= $dernier_resultat['home_score']; ?></strong></div>
                <div class="team"><?= htmlspecialchars($dernier_resultat['away_team_name']); ?> <strong><?= $dernier_resultat['away_score']; ?></strong></div>
            </div>
        <?php else: ?>
            <p style="text-align:center; color:#ccc;">Aucun résultat disponible.</p>
        <?php endif; ?>

        <div class="card__foot"><a class="btn-pill" href="<?= url('resultats') ?>">Tous les Résultats →</a></div>
    </div>

    <!-- Prochain match -->
    <div class="card card--gradient">
        <header class="card__head">
            <h3>PROCHAINES RENCONTRES</h3>
        </header>
        <?php if ($prochain_match): ?>
            <p class="card__sub"><?= strtoupper(htmlspecialchars($prochain_match['match_type'])); ?></p>
            <p class="card__date"><?= date('d/m/Y', strtotime($prochain_match['match_date'])); ?></p>
            <div class="card__teams">
                <div class="team"><?= htmlspecialchars($prochain_match['home_team_name']); ?></div>
                <div class="team"><?= htmlspecialchars($prochain_match['away_team_name']); ?></div>
            </div>
        <?php else: ?>
            <p style="text-align:center; color:#ccc;">Aucun match prévu.</p>
        <?php endif; ?>
        <div class="card__foot"><a class="btn-pill" href="<?= url('calendrier') ?>">Calendrier complet →</a></div>
    </div>

    <!-- Classement -->
    <div class="card card--gradient">
        <header class="card__head">
            <img src="<?= asset('img/logo R1.webp') ?>" alt="Régional 1" class="badge-league">
            <h3>CLASSEMENT</h3>
        </header>
        <div class="card__foot">
            <a class="btn-pill" href="https://centre.fff.fr/competitions/classements/" target="_blank">📊 Voir le Classement Officiel →</a>
        </div>
    </div>
</section>




<!-- ========================= ACTUALITÉS ========================= -->


<section class="news-block" aria-labelledby="news-title">
    <div class="container">
        <header class="news-head">
            <h2 id="news-title">AU COEUR DE L'ESM</h2>
            <p class="news-sub">Découvrez nos actualités pour suivre l'ESM</p>
            <div class="news-sep">
                <span class="dash"></span>
                <img src="<?= asset('img/logo_moulon.jpg') ?>" alt="" aria-hidden="true">
                <span class="dash_2"></span>
            </div>
        </header>

        <div class="news-grid">
            <?php if (!empty($actualites)): ?>
                <?php foreach ($actualites as $i => $news): ?>
                    <?php
                    // Gestion du chemin image
                    $has_image = !empty($news['image']) && $news['image'] !== 'NULL';

                    if ($has_image) {
                        if (strpos($news['image'], 'uploads/') === 0) {
                            $image_path = asset($news['image']);
                        } else {
                            $image_path = asset('uploads/' . ltrim($news['image'], '/'));
                        }
                    } else {
                        $image_path = asset('img/default-news.jpg');
                    }
                    ?>

                    <?php if ($i === 0): ?>
                        <!-- Article principal -->
                        <article class="news-feature">
                            <a class="thumb" href="<?= url('actualite?id=' . $news['id_new']) ?>">
                                <img src="<?= htmlspecialchars($image_path) ?>"
                                    alt="<?= htmlspecialchars($news['title']) ?>"
                                    loading="lazy">
                            </a>
                            <div class="content">
                                <h3 class="title">
                                    <a href="<?= url('actualite?id=' . $news['id_new']) ?>">
                                        <?= htmlspecialchars($news['title']) ?>
                                    </a>
                                </h3>
                                <p class="excerpt"><?= nl2br(htmlspecialchars(substr($news['content'], 0, 150))) ?>...</p>
                                <time class="date"><?= date('d M Y', strtotime($news['published_at'])) ?></time>
                            </div>
                        </article>
                    <?php else: ?>
                        <!-- Articles secondaires -->
                        <article class="news-card">
                            <a class="thumb" href="<?= url('actualite?id=' . $news['id_new']) ?>">
                                <img src="<?= htmlspecialchars($image_path) ?>"
                                    alt="<?= htmlspecialchars($news['title']) ?>"
                                    loading="lazy">
                            </a>
                            <div class="content">
                                <h4 class="title">
                                    <a href="<?= url('actualite?id=' . $news['id_new']) ?>">
                                        <?= htmlspecialchars($news['title']) ?>
                                    </a>
                                </h4>
                                <p class="description"><?= nl2br(htmlspecialchars(substr($news['content'], 0, 80))) ?>...</p>
                                <time class="date"><?= date('d M Y', strtotime($news['published_at'])) ?></time>
                            </div>
                        </article>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;color:#555;">Aucune actualité disponible.</p>
            <?php endif; ?>
        </div>

        <div class="news-cta">
            <a class="btn-gradient" href="<?= url('actualites') ?>">Toutes nos Actualités →</a>
        </div>
    </div>
</section>


           <!-- ========================= LE CLUB ========================= -->

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


  <div class="club-hero__players">
    <img src="<?= asset('img/capture_le_club.png') ?>"
      alt="Joueurs de l’ES Moulon">
  </div>

</section>




<!-- ========================= PARTENAIRES ========================= -->
<style>
    /* ========================= PARTENAIRES CAROUSEL - FORCÉ ========================= */
    .partners {
        overflow: hidden ;
        padding: 60px 0 ;
        background: #f6faf7 ;
    }

    .partners .container {
        max-width: 1400px ;
        margin: 0 auto ;
        padding: 0 20px ;
    }

    .partners__head { 
        text-align: center ;
        margin-bottom: 50px ;
    }

    .partners__title {
        margin: 10px 0 30px ;
        text-transform: uppercase ;
        letter-spacing: 0.06em ;
        font-weight: 900 ;
        font-size: clamp(20px, 2.5vw, 32px) ;
    }

    .partners__title .gold { 
        color: #c7a13a ;
    }

    .partners__title .green { 
        color: #1C995A ;
    }

    /* Wrapper du carousel */
    .partners__wrap {
        position: relative ;
        overflow: hidden ;
        background: #ffffff ;
        padding: 50px 0 ;
        border-radius: 20px ;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) ;
        margin-bottom: 40px ;
    }

    /* Rail qui défile - LE PLUS IMPORTANT */
    .partners__rail {
        display: flex;
        gap: 50px;
        width: max-content;
        animation: partnerScroll 30s linear infinite;
    }

    /* Animation du défilement */
    @keyframes partnerScroll {
        0% {
            transform: translateX(0) ;
        }
        100% {
            transform: translateX(-33.33%) ;
        }
    }

    /* Pause au survol */
    .partners__wrap:hover .partners__rail {
        animation-play-state: paused ;
    }

    /* Cartes partenaires */
    a.partner {
        flex: 0 0 220px ;
        width: 220px ;
        height: 140px ;
        display: flex ;
        align-items: center ;
        justify-content: center ;
        background: #fff ;
        border: 1px solid rgba(17, 136, 72, 0.28) ;
        border-radius: 16px ;
        padding: 25px ;
        text-decoration: none ;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08) ;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        overflow: hidden ;
    }

    a.partner:hover {
        transform: translateY(-10px) ;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15) ;
        border-color: rgba(17, 136, 72, 0.65) ;
        border: 2px solid rgba(17, 136, 72, 0.65);
    }

    a.partner img {
        max-width: 100% ;
        max-height: 100% ;
        object-fit: contain ;
        transition: transform 0.3s ease, filter 0.3s ease;
        
    }

    a.partner:hover img {
        transform: scale(1.10) ;
       
    }

    /* Dégradés sur les bords */
    .partners__wrap::before,
    .partners__wrap::after {
        content: '' ;
        position: absolute ;
        top: 0 ;
        bottom: 0 ;
        width: 100px ;
        z-index: 5 ;
        pointer-events: none ;
    }

    .partners__wrap::before {
        left: 0 ;
        background: linear-gradient(90deg, #fff 0%, transparent 100%) ;
    }

    .partners__wrap::after {
        right: 0 ;
        background: linear-gradient(270deg, #fff 0%, transparent 100%) ;
    }

    /* Intro */
    .partners__intro {
        text-align: center ;
        margin: 0 auto 30px ;
        max-width: 700px ;
        line-height: 1.7 ;
        font-size: 1.1rem ;
        font-weight: 600 ;
    }

    /* CTA */
    .partners__cta {
        display: flex ;
        justify-content: center ;
        gap: 20px ;
        flex-wrap: wrap ;
    }


    /* Responsive */
    @media (max-width: 768px) {
        .partners__rail {
            gap: 30px ;
            animation-duration: 35s ;
        }
        
        a.partner {
            flex: 0 0 150px ;
            width: 150px ;
            height: 100px ;
        }
    }
</style>

<section class="partners" aria-labelledby="partners-title">
    <div class="container">
        <header class="partners__head">
            <h2 id="partners-title" class="partners__title">
                <span class="gold">Nos partenaires</span> <span class="green">officiels</span>
            </h2>
        </header>

        <div class="partners__wrap">
            <div class="partners__rail">
                <?php if (!empty($partenaires)): ?>
                    <?php 
                    // 🔄 TRIPLICATION pour effet défilement infini
                    $partenaires_tripled = array_merge($partenaires, $partenaires, $partenaires);
                    foreach ($partenaires_tripled as $p): 
                    ?>
                        <?php
                        // Gestion du logo partenaire
                        if (!empty($p['logo']) && $p['logo'] !== 'NULL') {
                            if (strpos($p['logo'], 'uploads/') === 0) {
                                $logo_path = asset($p['logo']);
                            } else {
                                $logo_path = asset('uploads/' . ltrim($p['logo'], '/'));
                            }
                        } else {
                            $logo_path = asset('img/default-partner.png');
                        }
                        ?>
                        <a class="partner" 
                           href="<?= htmlspecialchars($p['redirect_url'] ?? '#') ?>" 
                           target="_blank" 
                           rel="noopener"
                           title="<?= htmlspecialchars($p['company_name']) ?>">
                            <img src="<?= htmlspecialchars($logo_path) ?>"
                                 alt="<?= htmlspecialchars($p['company_name']) ?>"
                                 loading="lazy"
                                 onerror="this.src='<?= asset('img/default-partner.png') ?>'">
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center;color:#555;padding:40px;">Aucun partenaire enregistré.</p>
                <?php endif; ?>
            </div>
        </div>

        <p class="partners__intro">
            L'ES Moulon ne serait pas ce qu'il est aujourd'hui sans le soutien généreux de nos sponsors.
        </p>

        <div class="partners__cta">
            <a class="btn-gradient" href="<?= url('partenaires') ?>">Tous nos Partenaires →</a>
            <a class="btn-gradient" href="<?= url('Rejoignez_nous/devenir_partenaire') ?>">Devenir Partenaire →</a>
        </div>
    </div>
</section>

<script>
    (function() {
        'use strict';
        
        const rail = document.querySelector('.partners__rail');
        
        if (!rail) {
            console.error('❌ Rail non trouvé');
            return;
        }
        
        const partners = rail.querySelectorAll('.partner');
        console.log('✅ ' + partners.length + ' partenaires trouvés');
        
        // Force l'animation JavaScript
        let position = 0;
        const speed = 0.5; // pixels par frame
        let isPaused = false;
        
        function scroll() {
            if (!isPaused) {
                position -= speed;
                
                // Largeur d'un tiers (un set complet)
                const oneThirdWidth = rail.scrollWidth / 3;
                
                // Reset quand on atteint un tiers
                if (Math.abs(position) >= oneThirdWidth) {
                    position = 0;
                }
                
                rail.style.transform = `translateX(${position}px)`;
                rail.style.transition = 'none';
            }
            
            requestAnimationFrame(scroll);
        }
        
        // Démarre l'animation
        scroll();
        
        // Pause au survol
        const wrap = document.querySelector('.partners__wrap');
        if (wrap) {
            wrap.addEventListener('mouseenter', () => {
                isPaused = true;
                console.log('⏸️ Pause');
            });
            
            wrap.addEventListener('mouseleave', () => {
                isPaused = false;
                console.log('▶️ Reprise');
            });
        }
        
        console.log('🎉 Carousel démarré avec JavaScript');
    })();
</script>

<script>
  const burger = document.getElementById('burger');
  const menu = document.querySelector('.menu');

  burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    menu.classList.toggle('active');
  });

  document.querySelectorAll('.menu a').forEach(link => {
    link.addEventListener('click', () => {
      burger.classList.remove('active');
      menu.classList.remove('active');
    });
  });
</script>

<script>
  const burger = document.getElementById('burger');
  const menu = document.querySelector('.menu');

  // ouverture/fermeture du menu
  burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    menu.classList.toggle('active');
  });

  // ferme le menu quand on clique sur un lien simple
  document.querySelectorAll('.menu a').forEach(link => {
    link.addEventListener('click', () => {
      burger.classList.remove('active');
      menu.classList.remove('active');
    });
  });

  // gestion des sous-menus sur mobile
  const submenuParents = document.querySelectorAll('.has-submenu > a');
  submenuParents.forEach(link => {
    link.addEventListener('click', (e) => {
      if (window.innerWidth <= 960) {
        e.preventDefault(); // empêche le lien de rediriger
        const parent = link.parentElement;
        parent.classList.toggle('open');

        // bascule la classe sur le <ul> du sous-menu
        const submenu = parent.querySelector('.submenu');
        submenu.classList.toggle('open');
      }
    });
  });
</script>

