<?php
require_once __DIR__ . '/../includes/tracker.php';
require_once __DIR__ . '/../includes/config.php';

// Récupération des partenaires actifs
try {
    $stmt = $pdo->query("
        SELECT p.company_name, p.redirect_url, p.description, m.file_path AS logo, p.display_order
        FROM partners p
        LEFT JOIN medias m ON p.id_media = m.id_media
        WHERE p.is_active = 1
        ORDER BY p.display_order ASC
    ");
    $all_partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erreur chargement partenaires : ' . $e->getMessage());
    $all_partners = [];
}

// Séparer partenaires premium (3 premiers) et autres
$premium_partners = array_slice($all_partners, 0, 3);
$other_partners = array_slice($all_partners, 3);

$title = "Nos Partenaires — ES Moulon";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Partenaires — ES Moulon</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --vert-esm: #1c995a;
            --vert-fonce: #0b562b;
            --vert-clair: #4ade80;
            --or: #fbbf24;
            --blanc: #ffffff;
            --gris-clair: #f8f9fa;
            --gris: #6b7280;
            --noir: #1f2937;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gris-clair);
            color: var(--noir);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
        }



        /* ========================= HERO ========================= */

        .hero--partners {
            background: linear-gradient(180deg, var(--vert-fonce), var(--vert-esm));
            color: #fff;
            text-align: center;
            padding: 40px 0 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .hero--partners::before {
             content: '⚽';
            position: absolute;
            font-size: 250px;
            opacity: 0.05;
            top: -50px;
            right: -50px;
            transform: rotate(-15deg);
        }

        .hero--partners h1 {
            font-size: clamp(32px, 5vw, 52px);
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .hero--partners h1 span {
            color: #fcd34d; /* doré */
            text-shadow: 2px 2px 10px rgba(252,211,77,0.25);
        }

        .hero--partners p {
            font-size: 1.3rem;
            font-weight: 500;
            color: rgba(255,255,255,0.9);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .hero--partners {
                padding: 70px 15px;
            }

            .hero--partners h1 {
                font-size: 12rem;
                
            }

            .hero--partners p {
                font-size: 1rem;
            }
        }

        /* ========================= SECTION TITLES ========================= */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--noir);
            margin-bottom: 10px;
        }

        .section-title .gold {
            color: var(--or);
            text-shadow: 2px 2px 4px rgba(251, 191, 36, 0.2);
        }

        .section-title p {
            color: var(--gris);
            font-size: 1.1rem;
        }

        /* ========================= PARTENAIRES PREMIUM ========================= */
        .premium-section {
            margin-bottom: 80px;
        }

        .premium-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .premium-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            position: relative;
            border: 3px solid transparent;
        }

        .premium-card::before {
            content: '⭐ PARTENAIRE PREMIUM';
            position: absolute;
            top: 50px;
            right: -60px;
            background: linear-gradient(135deg, var(--or) 0%, #fcd34d 100%);
            color: var(--noir);
            padding: 5px 40px;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 1px;
            transform: rotate(45deg);
            box-shadow: 0 2px 10px rgba(251, 191, 36, 0.4);
            z-index: 10;
        }

        .premium-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 166, 81, 0.2);
            border-color: var(--vert-esm);
        }

        .premium-logo {
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
            padding: 40px;
            border-bottom: 3px solid var(--or);
            position: relative;
        }

        .premium-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .premium-card:hover .premium-logo img {
            transform: scale(1.05);
        }

        /* Image par défaut pour premium */
        .premium-logo .default-icon {
            font-size: 4rem;
            color: var(--or);
            filter: drop-shadow(0 4px 10px rgba(251, 191, 36, 0.3));
        }

        .premium-content {
            padding: 30px;
        }

        .premium-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--noir);
            margin-bottom: 15px;
        }

        .premium-desc {
            color: var(--gris);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .premium-link {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--vert-esm) 0%, var(--vert-fonce) 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .premium-link:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 166, 81, 0.4);
        }

        /* ========================= AUTRES PARTENAIRES ========================= */
        .partners-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }

        .partner-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .partner-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 166, 81, 0.15);
            border-color: var(--vert-esm);
        }

        .partner-logo {
            width: 100%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background: var(--gris-clair);
            border-radius: 8px;
            padding: 15px;
        }

        .partner-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .partner-card:hover .partner-logo img {
            transform: scale(1.1);
        }

        /* Image par défaut pour partenaire standard */
        .partner-logo .default-icon {
            font-size: 2.5rem;
            color: var(--gris);
            opacity: 0.5;
        }

        .partner-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--noir);
            margin-bottom: 10px;
        }

        .partner-link {
            color: var(--vert-esm);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .partner-link:hover {
            color: var(--vert-fonce);
            text-decoration: underline;
        }

        
        /* ========================= RESPONSIVE ========================= */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .premium-grid {
                grid-template-columns: 1fr;
            }

            .partners-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            .section-title h2 {
                font-size: 1.8rem;
            }

            .cta-section h2 {
                font-size: 1.5rem;
            }
        }

         /* ========================= FOOTER SECTION PARTENAIRES ========================= */
        .partner-footer-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e8f5e9 100%);
            margin-top: 40px;
        }

        .partner-footer-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 60px;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .partner-footer-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 166, 81, 0.2);
            height: 500px;
        }

        .partner-footer-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .partner-footer-image:hover img {
            transform: scale(1.05);
        }

        .partner-footer-content {
            padding: 20px;
        }

        .partner-footer-title {
            font-size: 2.2rem;
            font-weight: 900;
            margin-bottom: 30px;
            color: var(--noir);
        }

        .partner-footer-title .highlight {
            color: #c7a13a;
        }

        .partner-footer-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            border: 3px solid var(--vert-esm);
        }

        .crown-icon {
            position: absolute;
            top: -25px;
            right: 30px;
            font-size: 3rem;
            filter: drop-shadow(0 4px 10px rgba(199, 161, 58, 0.5));
        }

        .partner-footer-subtitle {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--vert-esm);
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .partner-footer-desc {
            color: var(--gris);
            line-height: 1.8;
            margin-bottom: 30px;
            font-size: 1.05rem;
        }

        .partner-footer-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            background: linear-gradient(135deg, var(--vert-esm) 0%, var(--vert-fonce) 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 166, 81, 0.3);
        }

        .partner-footer-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 166, 81, 0.5);
        }

        /* Responsive */
        @media (max-width: 968px) {
            .partner-footer-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .partner-footer-image {
                height: 350px;
            }
            
            .partner-footer-title {
                font-size: 1.8rem;
            }
            
            .partner-footer-card {
                padding: 30px 20px;
            }
        }

        @media (max-width: 640px) {
            .partner-footer-section {
                padding: 50px 0;
            }
            
            .partner-footer-image {
                height: 280px;
            }
        }

    </style>

</head>
<body>
    <!-- HERO -->
    <header class="hero hero--partners">
        <div class="container">
            <h1>🤝 <span>Nos partenaires</span></h1>
            <p>Des entreprises engagées à nos côtés pour faire briller le football moulonnais</p>
        </div>
    </header>

    <div class="container">
        <!-- PARTENAIRES PREMIUM -->
        <section class="premium-section">
            <div class="section-title">
                <h2><span class="gold">⭐ Partenaires Premium</span></h2>
                <p>Nos sponsors majeurs qui soutiennent activement le club</p>
            </div>

            <?php if (!empty($premium_partners)): ?>
                <div class="premium-grid">
                    <?php foreach ($premium_partners as $partner): ?>
                        <div class="premium-card">
                            <div class="premium-logo">
                                <?php 
                                // Gestion du logo premium
                                if (!empty($partner['logo']) && $partner['logo'] !== 'NULL') {
                                    if (strpos($partner['logo'], 'uploads/') === 0) {
                                        $logo_path = asset($partner['logo']);
                                    } else {
                                        $logo_path = asset('uploads/' . ltrim($partner['logo'], '/'));
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($logo_path) ?>" 
                                        alt="<?= htmlspecialchars($partner['company_name']) ?>"
                                        onerror="this.parentElement.innerHTML='<div class=\'default-icon\'>🏆</div>'">
                                <?php } else { ?>
                                    <div class="default-icon">🏆</div>
                                <?php } ?>
                            </div>
                            <div class="premium-content">
                                <h3 class="premium-name"><?= htmlspecialchars($partner['company_name']) ?></h3>
                                <?php if (!empty($partner['description'])): ?>
                                    <p class="premium-desc"><?= nl2br(htmlspecialchars($partner['description'])) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($partner['redirect_url'])): ?>
                                    <a href="<?= htmlspecialchars($partner['redirect_url']) ?>" 
                                    target="_blank" 
                                    rel="noopener" 
                                    class="premium-link">
                                        Visiter le site →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center;color:#999;padding:40px;">Aucun partenaire premium pour le moment.</p>
            <?php endif; ?>
        </section>

        <!-- AUTRES PARTENAIRES -->
        <section style="margin-bottom:80px;">
            <div class="section-title">
                <h2>Tous nos partenaires</h2>
                <p>Ensemble, nous faisons grandir l'ES Moulon</p>
            </div>

            <?php if (!empty($other_partners)): ?>
                <div class="partners-grid">
                    <?php foreach ($other_partners as $partner): ?>
                        <div class="partner-card">
                            <div class="partner-logo">
                                <?php 
                                // Gestion du logo standard
                                if (!empty($partner['logo']) && $partner['logo'] !== 'NULL') {
                                    if (strpos($partner['logo'], 'uploads/') === 0) {
                                        $logo_path = asset($partner['logo']);
                                    } else {
                                        $logo_path = asset('uploads/' . ltrim($partner['logo'], '/'));
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($logo_path) ?>" 
                                        alt="<?= htmlspecialchars($partner['company_name']) ?>"
                                        onerror="this.parentElement.innerHTML='<div class=\'default-icon\'>🤝</div>'">
                                <?php } else { ?>
                                    <div class="default-icon">🤝</div>
                                <?php } ?>
                            </div>
                            <h3 class="partner-name"><?= htmlspecialchars($partner['company_name']) ?></h3>
                            <?php if (!empty($partner['redirect_url'])): ?>
                                <a href="<?= htmlspecialchars($partner['redirect_url']) ?>" 
                                target="_blank" 
                                rel="noopener" 
                                class="partner-link">
                                    Visiter →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center;color:#999;padding:40px;">Aucun autre partenaire pour le moment.</p>
            <?php endif; ?>
        </section>

        <!-- CTA DEVENIR PARTENAIRE -->
        <?php
        // Récupération de l'image footer
        try {
            $stmt_footer = $pdo->query("
                SELECT file_path
                FROM medias
                WHERE usage_type = 'partner_footer'
                ORDER BY uploaded_at DESC
                LIMIT 1
            ");
            $footer_image = $stmt_footer->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur image footer : ' . $e->getMessage());
            $footer_image = null;
        }

        // Gestion du chemin de l'image
        if (!empty($footer_image['file_path']) && $footer_image['file_path'] !== 'NULL') {
            if (strpos($footer_image['file_path'], 'uploads/') === 0) {
                $partnership_img = asset($footer_image['file_path']);
            } else {
                $partnership_img = asset('uploads/' . ltrim($footer_image['file_path'], '/'));
            }
        } else {
            $partnership_img = asset('img/default-partnership.jpg');
        }
        ?>

        <!-- SECTION REJOIGNEZ NOS PARTENAIRES -->
        <section class="partner-footer-section">
            <div class="partner-footer-grid">
                <!-- Image gauche -->
                <div class="partner-footer-image">
                    <img src="<?= htmlspecialchars($partnership_img) ?>" 
                         alt="Rejoignez nos partenaires"
                         onerror="this.src='<?= asset('img/default-partnership.jpg') ?>'">
                </div>

                <!-- Contenu droite -->
                <div class="partner-footer-content">
                    <h2 class="partner-footer-title">
                        Rejoignez nos <span class="highlight">Partenaires !</span>
                    </h2>
                    
                    <div class="partner-footer-card">
                        <div class="crown-icon">👑</div>
                        
                        <h3 class="partner-footer-subtitle">
                            Rejoignez la Famille des Partenaires de l'ES Moulon
                        </h3>
                        
                        <p class="partner-footer-desc">
                            Être partenaire de l’ES Moulon, c’est bien plus qu’un logo sur un maillot.
                            C’est soutenir un club historique fondé en 1940, une aventure humaine et une fierté locale qui fait vibrer Bourges et tout le Cher.<br>

                            Ensemble, donnons plus de moyens à nos jeunes, plus de visibilité à votre entreprise, et plus de passion à nos supporters.
                        </p>
                        
                        <div class="news-cta">
                             <a class="btn-gradient" href="<?= url('histoire_et_valeurs') ?>">Rejoignez l'aventure<br> dès aujourd'hui →</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</body>
</html>