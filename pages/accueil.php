<?php
require_once __DIR__ . '/../includes/tracker.php';
require_once __DIR__ . '/../includes/config.php';

// ==============================
// 📢 AFFICHAGE MESSAGES FLASH
// ==============================
if (isset($_SESSION['flash'])): ?>
    <div style="position:fixed;top:20px;right:20px;z-index:9999;max-width:400px;">
        <?php foreach ($_SESSION['flash'] as $type => $message): ?>
            <div style="
                background: <?= $type === 'error' ? '#fee2e2' : '#d1fae5' ?>;
                color: <?= $type === 'error' ? '#991b1b' : '#065f46' ?>;
                border: 2px solid <?= $type === 'error' ? '#ef4444' : '#10b981' ?>;
                padding: 15px 20px;
                border-radius: 8px;
                margin-bottom: 10px;
                font-weight: 600;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                animation: slideIn 0.3s ease-out;
            ">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <style>
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
    <script>
        setTimeout(() => {
            document.querySelectorAll('[style*="position:fixed"]').forEach(el => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.5s';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    </script>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php
// ==============================
// 0️⃣ CONFIGURATION HERO (Bannière d'accueil)
// ==============================
$hero_title = 'Bienvenue à';
$hero_subtitle = "L'ES Moulon";
$hero_lead = "Depuis 1940, notre club s'engage à développer le football local et à promouvoir les valeurs du sport.";
$hero_image = asset('img/photo_accueil.jpg'); // Image par défaut

try {
    $stmt = $pdo->query("
        SELECT s.hero_title, s.hero_subtitle, s.hero_lead, m.file_path 
        FROM site_settings s
        LEFT JOIN medias m ON s.id_hero_media = m.id_media
        LIMIT 1
    ");
    $hero_config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($hero_config) {
        $hero_title = $hero_config['hero_title'] ?? $hero_title;
        $hero_subtitle = $hero_config['hero_subtitle'] ?? $hero_subtitle;
        $hero_lead = $hero_config['hero_lead'] ?? $hero_lead;
        
        // Si une image est configurée, construire le bon chemin
        if (!empty($hero_config['file_path'])) {
            // Le file_path dans medias contient déjà "uploads/..." donc on utilise asset() directement
            $hero_image = asset($hero_config['file_path']);
        }
    }
} catch (PDOException $e) {
    error_log('Erreur chargement Hero : ' . $e->getMessage());
    // On garde les valeurs par défaut
}

// ==============================
// 1️⃣ DERNIER RÉSULTAT (matchs où ES MOULON a joué)
// ==============================
try {
    $stmt = $pdo->query("
        SELECT 
            m.*, 
            home.name AS home_team_name, 
            home.id_club_team AS home_is_club,
            away.name AS away_team_name,
            away.id_club_team AS away_is_club,
            mh.file_path AS home_logo,
            ma.file_path AS away_logo
        FROM matches m
        LEFT JOIN teams home ON m.id_home_team = home.id_team
        LEFT JOIN medias mh ON home.id_team_logo = mh.id_media
        LEFT JOIN teams away ON m.id_away_team = away.id_team
        LEFT JOIN medias ma ON away.id_team_logo = ma.id_media
        WHERE m.match_date < NOW() 
          AND m.home_score IS NOT NULL
          AND (home.id_club_team = 1 OR away.id_club_team = 1)
        ORDER BY m.match_date DESC 
        LIMIT 1
    ");
    $dernier_resultat = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erreur dernier résultat : ' . $e->getMessage());
    $dernier_resultat = null;
}

// ==============================
// 2️⃣ PROCHAIN MATCH (matchs où ES MOULON va jouer)
// ==============================
try {
    $stmt = $pdo->query("
        SELECT 
            m.*, 
            home.name AS home_team_name, 
            home.id_club_team AS home_is_club,
            away.name AS away_team_name,
            away.id_club_team AS away_is_club,
            mh.file_path AS home_logo,
            ma.file_path AS away_logo
        FROM matches m
        LEFT JOIN teams home ON m.id_home_team = home.id_team
        LEFT JOIN medias mh ON home.id_team_logo = mh.id_media
        LEFT JOIN teams away ON m.id_away_team = away.id_team
        LEFT JOIN medias ma ON away.id_team_logo = ma.id_media
        WHERE m.match_date >= NOW()
          AND (home.id_club_team = 1 OR away.id_club_team = 1)
        ORDER BY m.match_date ASC 
        LIMIT 1
    ");
    $prochain_match = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erreur prochain match : ' . $e->getMessage());
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
    <!-- Image de fond dynamique depuis site_settings -->
    <div class="home-hero__bg" style="--hero:url('<?= htmlspecialchars($hero_image) ?>');"></div>
    
    <div class="home-hero__content container">
        <!-- Titre dynamique depuis site_settings -->
        <p class="home-hero__eyebrow"><?= htmlspecialchars($hero_title) ?></p>
        <h1 class="home-hero__title"><?= htmlspecialchars($hero_subtitle) ?></h1>
        
        <!-- Texte d'introduction dynamique -->
        <p class="home-hero__lead"><?= htmlspecialchars($hero_lead) ?></p>
        
        <div class="home-hero__cta">
            <a href="<?= url('Rejoignez_nous/nous_contactez') ?>" class="btn btn-gradient">Rejoignez-nous</a>
        </div>
    </div>
</section>


<!-- ============== CARTES RÉSULTATS/MATCHS ================== -->

<section class="cards-grid">

    <!-- ========== CARD 1 : DERNIERS RÉSULTATS ========== -->
    <div class="match-card">
        <?php if ($dernier_resultat): ?>
            <?php
            $club_name = 'E.S.MOULON';
            $is_home_club = strcasecmp(trim($dernier_resultat['home_team_name']), $club_name) === 0;
            $is_away_club = strcasecmp(trim($dernier_resultat['away_team_name']), $club_name) === 0;

            // Format de la date
            $date_match = new DateTime($dernier_resultat['match_date']);
            $formatter = new IntlDateFormatter(
                'fr_FR',                    // Locale
                IntlDateFormatter::NONE,    // Format de date complet (on le gère à la main)
                IntlDateFormatter::NONE,    // Format d'heure (on gère aussi à part)
                'Europe/Paris',             // Fuseau horaire
                IntlDateFormatter::GREGORIAN,
                'EEE dd MMM'                // Format : abrégé jour + jour + mois (ex: "sam. 12 oct.")
            );

            // Formater la date
            $date_formattee = $formatter->format($date_match);
            $heure = $date_match->format('H\hi');
            ?>

            <div class="card-header">
                <div class="card-badge">
                    <span>⚽</span>
                    <?php echo strtoupper(htmlspecialchars($dernier_resultat['match_type'])); ?>
                </div>
                <h3 class="card-title">Derniers Résultats</h3>
                <div class="card-date"><?php echo strtoupper($date_formattee); ?> - <?php echo $heure; ?></div>
            </div>

            <div class="card-body">
                <div class="match-display">
                    <!-- Équipe Domicile -->
                    <div class="team">
                        <div class="team-logo <?php echo $is_home_club ? 'home' : ''; ?>">
                            <?php if (!empty($dernier_resultat['home_logo'])): ?>
                                <img src="<?php echo asset($dernier_resultat['home_logo']); ?>" alt="<?php echo htmlspecialchars($dernier_resultat['home_team_name']); ?>">
                            <?php else: ?>
                                <span class="team-logo-emoji"><?php echo $is_home_club ? '⚽' : '🔴'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="team-name <?php echo $is_home_club ? 'home' : ''; ?>">
                            <?php echo htmlspecialchars($dernier_resultat['home_team_name']); ?>
                        </div>
                    </div>

                    <!-- Score -->
                    <div class="match-separator">
                        <div class="score-display">
                            <div class="score-number <?php echo ($dernier_resultat['home_score'] > $dernier_resultat['away_score']) ? 'winner' : ''; ?>">
                                <?php echo $dernier_resultat['home_score']; ?>
                            </div>
                            <div class="score-number <?php echo ($dernier_resultat['away_score'] > $dernier_resultat['home_score']) ? 'winner' : ''; ?>">
                                <?php echo $dernier_resultat['away_score']; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Équipe Extérieur -->
                    <div class="team">
                        <div class="team-logo <?php echo $is_away_club ? 'home' : ''; ?>">
                            <?php if (!empty($dernier_resultat['away_logo'])): ?>
                                <img src="<?php echo asset($dernier_resultat['away_logo']); ?>" alt="<?php echo htmlspecialchars($dernier_resultat['away_team_name']); ?>">
                            <?php else: ?>
                                <span class="team-logo-emoji"><?php echo $is_away_club ? '⚽' : '🔴'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="team-name <?php echo $is_away_club ? 'home' : ''; ?>">
                            <?php echo htmlspecialchars($dernier_resultat['away_team_name']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="match-info">
                    📍 <?php echo htmlspecialchars($dernier_resultat['location']); ?>
                </div>
                <a href="https://epreuves.fff.fr/competition/club/514340-espe-s-du-moulon-bourges/club" class="btn-card" target="_blank" rel="nooper noreferrer">Tous les Résultats →</a>
            </div>
        <?php else: ?>
            <div class="card-header">
                <div class="card-badge"><span>⚽</span> RÉSULTATS</div>
                <h3 class="card-title">Derniers Résultats</h3>
            </div>
            <div class="card-body">
                <p class="empty-message">Aucun résultat disponible pour le moment.</p>
            </div>
            <div class="card-footer">
                <a href="<?php echo url('Rejoignez_nous/nous_contactez'); ?>" class="btn-card">Tous les Résultats →</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========== CARD 2 : PROCHAINES RENCONTRES ========== -->
    <div class="match-card">
        <?php if ($prochain_match): ?>
            <?php
            $club_name = 'E.S.MOULON';
            $is_home_club = strcasecmp(trim($prochain_match['home_team_name']), $club_name) === 0;
            $is_away_club = strcasecmp(trim($prochain_match['away_team_name']), $club_name) === 0;

            $date_match = new DateTime($prochain_match['match_date']);
            $formatter = new IntlDateFormatter(
                'fr_FR',                    // Locale
                IntlDateFormatter::NONE,    // Format de date complet (on le gère à la main)
                IntlDateFormatter::NONE,    // Format d'heure (on gère aussi à part)
                'Europe/Paris',             // Fuseau horaire
                IntlDateFormatter::GREGORIAN,
                'EEE dd MMM'                // Format : abrégé jour + jour + mois (ex: "sam. 12 oct.")
            );

            // Formater la date
            $date_formattee = $formatter->format($date_match);
            $heure = $date_match->format('H\hi');
            ?>

            <div class="card-header">
                <div class="card-badge">
                    <span>📅</span>
                    <?php echo strtoupper(htmlspecialchars($prochain_match['match_type'])); ?>
                </div>
                <h3 class="card-title">Prochaines Rencontres</h3>
                <div class="card-date"><?php echo strtoupper($date_formattee); ?> - <?php echo $heure; ?></div>
            </div>

            <div class="card-body">
                <div class="match-display">
                    <!-- Équipe Domicile -->
                    <div class="team">
                        <div class="team-logo <?php echo $is_home_club ? 'home' : ''; ?>">
                            <?php if (!empty($prochain_match['home_logo'])): ?>
                                <img src="<?php echo asset($prochain_match['home_logo']); ?>" alt="<?php echo htmlspecialchars($prochain_match['home_team_name']); ?>">
                            <?php else: ?>
                                <span class="team-logo-emoji"><?php echo $is_home_club ? '⚽' : '🔵'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="team-name <?php echo $is_home_club ? 'home' : ''; ?>">
                            <?php echo htmlspecialchars($prochain_match['home_team_name']); ?>
                        </div>
                    </div>

                    <!-- VS -->
                    <div class="match-separator">
                        <div class="vs-text">VS</div>
                    </div>

                    <!-- Équipe Extérieur -->
                    <div class="team">
                        <div class="team-logo <?php echo $is_away_club ? 'home' : ''; ?>">
                            <?php if (!empty($prochain_match['away_logo'])): ?>
                                <img src="<?php echo asset($prochain_match['away_logo']); ?>" alt="<?php echo htmlspecialchars($prochain_match['away_team_name']); ?>">
                            <?php else: ?>
                                <span class="team-logo-emoji"><?php echo $is_away_club ? '⚽' : '🔴'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="team-name <?php echo $is_away_club ? 'home' : ''; ?>">
                            <?php echo htmlspecialchars($prochain_match['away_team_name']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="match-info">
                    📍 <?php echo htmlspecialchars($prochain_match['location']); ?>
                </div>
                <a href="https://epreuves.fff.fr/competition/club/514340-espe-s-du-moulon-bourges/equipes" class="btn-card" target="_blank" rel="nooper noreferrer">Tous les Calendriers →</a>
            </div>
        <?php else: ?>
            <div class="card-header">
                <div class="card-badge"><span>📅</span> CALENDRIER</div>
                <h3 class="card-title">Prochaines Rencontres</h3>
            </div>
            <div class="card-body">
                <p class="empty-message">Aucun match prévu pour le moment.</p>
            </div>
            <div class="card-footer">
                <a href="<?php echo url('calendrier'); ?>" class="btn-card">Tous les Calendriers →</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========== CARD 3 : CLASSEMENT ========== -->
    <div class="match-card card-classement">
        <div class="card-header">
            <div class="card-badge">
                <img src="<?php echo asset('img/logo R1.webp'); ?>" alt="R1">
                RÉGIONAL 1
            </div>
            <h3 class="card-title">Classement</h3>
        </div>

        <div class="card-body">
            <img src="<?php echo asset('img/logo R1.webp'); ?>" alt="Régional 1" class="classement-icon">
            <p class="classement-text">
                Consultez le classement officiel<br>
                de la <strong>Régionale 1</strong><br>
                sur le site de la FFF
            </p>
        </div>

        <div class="card-footer">
            <a href="https://epreuves.fff.fr/competition/club/514340-espe-s-du-moulon-bourges/equipe/2025_4473_SEM_1/classement" target="_blank" rel="noopener" class="btn-card">
                📊 Classement Officiel →
            </a>
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

    <!-- Décoration "LE CLUB" -->
    <img class="club-hero__script"
        src="<?= asset('img/Le_club-titre.png') ?>"
        alt=""
        aria-hidden="true"
        loading="lazy">

    <div class="club-hero__container">
        <!-- Slogan principal -->
        <p class="club-hero__eyebrow" aria-label="Slogan du club">
            En vert, et contre tous !
        </p>

        <!-- Contenu principal -->
        <div class="club-hero__content">
            <h1 class="club-hero__title">
                Quelques mots à propos<br>de notre club
            </h1>

            <p class="club-hero__lead">
                Découvrez notre histoire, nos valeurs<br> et notre équipe dirigeante.
            </p>

            <div class="news-cta">
                <a class="btn-gradient" href="<?= url('Le_club/histoire_et_valeurs') ?>">Decouvrez l'ESM →</a>
            </div>
        </div>
    </div>

    <!-- Image alternative - Logo du club stylisé -->
    <div class="club-hero__visual">
        <img src="<?= asset('img/coupe_cher.webp') ?>"
            alt="Logo et mascotte de l'ES Moulon"
            loading="lazy">
    </div>

</section>




<!-- ========================= PARTENAIRES ========================= -->

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
                    //  TRIPLICATION pour effet défilement infini
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
    //caroussel partenaires pages accueil

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



