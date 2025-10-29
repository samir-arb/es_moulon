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
        --or-fonce: #d97706;
        --argent: #94a3b8;
        --bronze: #cd7f32;
        --blanc: #ffffff;
        --gris-clair: #f8f9fa;
        --gris: #6b7280;
        --noir: #1f2937;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--gris-clair);
        color: var(--noir);
        line-height: 1.6;
        width: 100%;
        overflow-x: hidden;
    }

    .container {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        padding-left: 30px;
        padding-right: 30px;
    }

    /* ========================= HERO PREMIUM ========================= */

    .hero--partners {
        position: relative;
        min-height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: linear-gradient(135deg, var(--vert-fonce) 0%, var(--vert-esm) 50%, var(--vert-clair) 100%);
        width: 100%;
    }

    /* Effet de texture géométrique en arrière-plan */
    .hero--partners::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            linear-gradient(30deg, rgba(255,255,255,0.05) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.05) 87.5%, rgba(255,255,255,0.05)),
            linear-gradient(150deg, rgba(255,255,255,0.05) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.05) 87.5%, rgba(255,255,255,0.05)),
            linear-gradient(30deg, rgba(255,255,255,0.05) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.05) 87.5%, rgba(255,255,255,0.05)),
            linear-gradient(150deg, rgba(255,255,255,0.05) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.05) 87.5%, rgba(255,255,255,0.05));
        background-size: 80px 140px;
        background-position: 0 0, 0 0, 40px 70px, 40px 70px;
        opacity: 0.3;
        z-index: 1;
    }

    /* Particules flottantes dorées */
    .hero--partners::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background-image: 
            radial-gradient(circle, var(--or) 1px, transparent 1px),
            radial-gradient(circle, rgba(251, 191, 36, 0.5) 2px, transparent 2px);
        background-size: 50px 50px, 100px 100px;
        background-position: 0 0, 25px 25px;
        opacity: 0.15;
        animation: particlesFloat 20s linear infinite;
        z-index: 1;
    }

    @keyframes particlesFloat {
        0% {
            transform: translateY(0);
        }
        100% {
            transform: translateY(-100px);
        }
    }

    /* Dégradé overlay sombre pour contraste texte */
    .hero--partners-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
            to bottom,
            rgba(0, 0, 0, 0.3) 0%,
            rgba(0, 0, 0, 0.2) 50%,
            rgba(0, 0, 0, 0.4) 100%
        );
        z-index: 2;
    }

    .hero--partners-content {
        position: relative;
        z-index: 3;
        text-align: center;
        max-width: 900px;
        padding: 40px 30px;
        animation: fadeInUp 1s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero--partners h1 {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 900;
        margin-bottom: 20px;
        line-height: 1.3;
        color: white;
        text-shadow: 
            0 2px 10px rgba(0, 0, 0, 0.3),
            0 4px 20px rgba(28, 153, 90, 0.4);
        letter-spacing: -0.5px;
    }
    
    .hero--partners h1 .main-line {
        display: block;
        margin-bottom: 10px;
    }
    
    .hero--partners h1 .subtitle-line {
        font-size: clamp(1.3rem, 2.8vw, 2rem);
        font-weight: 600;
        display: block;
        font-style: italic;
        opacity: 0.95;
        letter-spacing: 0.5px;
    }

    .hero--partners h1 .highlight {
        background: linear-gradient(135deg, var(--or) 0%, #fcd34d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: inline-block;
        position: relative;
    }

    .hero--partners h1 .highlight::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, var(--or), transparent);
        border-radius: 2px;
    }

    .hero--partners-subtitle {
        font-size: clamp(1rem, 2vw, 1.2rem);
        color: rgba(255, 255, 255, 0.95);
        max-width: 700px;
        margin: 0 auto 30px;
        line-height: 1.7;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        font-weight: 300;
    }

    .hero--partners-subtitle strong {
        color: var(--or);
        font-weight: 600;
    }

    /* Boutons d'action */
    .hero--partners-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 30px;
    }

    .hero--partners-cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 32px;
        background: linear-gradient(135deg, var(--or) 0%, var(--or-fonce) 100%);
        color: var(--noir);
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 
            0 10px 30px rgba(251, 191, 36, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);
        position: relative;
        overflow: hidden;
    }

    .hero--partners-cta::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }

    .hero--partners-cta:hover::before {
        left: 100%;
    }

    .hero--partners-cta:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 
            0 15px 40px rgba(251, 191, 36, 0.6),
            inset 0 1px 0 rgba(255, 255, 255, 0.4);
    }

    .hero--partners-cta-secondary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 32px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .hero--partners-cta-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-3px);
    }

    /* Responsive Hero */
    @media (max-width: 768px) {
        .hero--partners {
            min-height: 380px;
            padding: 30px 20px;
        }

        .hero--partners-content {
            padding: 30px 20px;
        }

        .hero--partners h1 {
            font-size: 2rem;
        }
        
        .hero--partners h1 .subtitle-line {
            font-size: 1.1rem;
        }

        .hero--partners-subtitle {
            font-size: 0.9rem;
        }

        .hero--partners-actions {
            flex-direction: column;
            gap: 12px;
        }

        .hero--partners-cta,
        .hero--partners-cta-secondary {
            width: 100%;
            justify-content: center;
            padding: 14px 28px;
            font-size: 0.95rem;
        }
    }

    /* ========================= STATISTIQUES D'IMPACT ========================= */
    .stats-section {
        background: linear-gradient(135deg, var(--vert-fonce) 0%, var(--vert-esm) 50%, var(--vert-clair) 100%);
        padding: 50px 0;
        color: white;
        position: relative;
        overflow: hidden;
        width: 100%;
    }

    /* Effet de texture géométrique en arrière-plan */
    .stats-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            linear-gradient(30deg, rgba(255,255,255,0.05) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.05) 87.5%, rgba(255,255,255,0.05)),
            linear-gradient(150deg, rgba(255,255,255,0.05) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.05) 87.5%, rgba(255,255,255,0.05)),
            linear-gradient(30deg, rgba(255,255,255,0.05) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.05) 87.5%, rgba(255,255,255,0.05)),
            linear-gradient(150deg, rgba(255,255,255,0.05) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.05) 87.5%, rgba(255,255,255,0.05));
        background-size: 80px 140px;
        background-position: 0 0, 0 0, 40px 70px, 40px 70px;
        opacity: 0.3;
        z-index: 1;
    }

    /* Particules flottantes dorées */
    .stats-section::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background-image: 
            radial-gradient(circle, var(--or) 1px, transparent 1px),
            radial-gradient(circle, rgba(251, 191, 36, 0.5) 2px, transparent 2px);
        background-size: 50px 50px, 100px 100px;
        background-position: 0 0, 25px 25px;
        opacity: 0.15;
        animation: particlesFloat 20s linear infinite;
        z-index: 1;
    }

    .stats-container {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        padding-left: 30px;
        padding-right: 30px;
        position: relative;
        z-index: 2;
    }

    .stats-title {
        text-align: center;
        margin-bottom: 40px;
    }

    .stats-title h3 {
        font-size: 1.8rem;
        font-weight: 800;
        color: white;
        margin-bottom: 10px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .stats-title p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1rem;
        text-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }

    .stat-item {
        text-align: center;
        padding: 25px 15px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        border: 2px solid rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--or), #fcd34d);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-item:hover::before {
        transform: scaleX(1);
    }

    .stat-item:hover {
        transform: translateY(-8px);
        background: rgba(255, 255, 255, 0.25);
        border-color: var(--or);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
    }

    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 12px;
        filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.3));
        display: block;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 900;
        color: var(--or);
        display: block;
        margin-bottom: 8px;
        line-height: 1;
        text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
    }

    .stat-label {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.95);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ========================= SECTION TITLES ========================= */
    .section-title {
        text-align: center;
        margin-bottom: 60px;
        padding-top: 80px;
    }

    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--noir);
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }

    .section-title h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: linear-gradient(90deg, transparent, var(--or), transparent);
        border-radius: 2px;
    }

    .section-title .gold {
        color: var(--or);
        text-shadow: 2px 2px 4px rgba(251, 191, 36, 0.2);
    }

    .section-title p {
        color: var(--gris);
        font-size: 1.2rem;
        max-width: 600px;
        margin: 20px auto 0;
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
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
        position: relative;
        border: 3px solid transparent;
    }

    .premium-card::before {
        content: '👑 PARTENAIRE OR';
        position: absolute;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, var(--or) 0%, #fcd34d 100%);
        color: var(--noir);
        padding: 8px 16px;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(251, 191, 36, 0.5);
        z-index: 10;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    .premium-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 25px 50px rgba(251, 191, 36, 0.3);
        border-color: var(--or);
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
        transition: transform 0.3s ease, filter 0.3s ease;
        filter: grayscale(20%);
    }

    .premium-card:hover .premium-logo img {
        transform: scale(1.08) rotate(-2deg);
        filter: grayscale(0%);
    }

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
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
        transition: transform 0.3s ease, filter 0.3s ease;
        filter: grayscale(30%);
    }

    .partner-card:hover .partner-logo img {
        transform: scale(1.15);
        filter: grayscale(0%);
    }

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

    /* ========================= SECTION AVANTAGES ========================= */
    .advantages-section {
        background: white;
        padding: 80px 0;
        width: 100%;
    }

    .advantages-container {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        padding-left: 30px;
        padding-right: 30px;
    }

    .advantages-intro {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 60px;
    }

    .advantages-intro h2 {
        font-size: 2.5rem;
        font-weight: 900;
        color: var(--noir);
        margin-bottom: 20px;
    }

    .advantages-intro .highlight {
        color: var(--vert-esm);
    }

    .advantages-intro p {
        font-size: 1.2rem;
        color: var(--gris);
        line-height: 1.8;
    }

    .advantages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
    }

    .advantage-card {
        background: linear-gradient(135deg, #f8f9fa 0%, white 100%);
        padding: 40px 30px;
        border-radius: 20px;
        text-align: center;
        border: 2px solid transparent;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .advantage-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--vert-esm), var(--vert-clair));
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }

    .advantage-card:hover::before {
        transform: scaleX(1);
    }

    .advantage-card:hover {
        transform: translateY(-10px);
        border-color: var(--vert-esm);
        box-shadow: 0 15px 40px rgba(28, 153, 90, 0.15);
    }

    .advantage-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
        display: block;
        filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.1));
    }

    .advantage-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--noir);
        margin-bottom: 15px;
    }

    .advantage-desc {
        color: var(--gris);
        font-size: 1rem;
        line-height: 1.7;
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

    /* ========================= RESPONSIVE ========================= */
    @media (max-width: 768px) {
        .container,
        .stats-container,
        .advantages-container {
            padding-left: 20px;
            padding-right: 20px;
        }

        .stats-section {
            padding: 40px 0;
        }

        .stats-title h3 {
            font-size: 1.4rem;
        }

        .stats-title p {
            font-size: 0.9rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .stat-item {
            padding: 20px 12px;
        }

        .stat-icon {
            font-size: 2rem;
        }

        .stat-number {
            font-size: 2rem;
        }

        .stat-label {
            font-size: 0.8rem;
        }

        .advantages-section {
            padding: 50px 0;
        }

        .advantages-intro h2 {
            font-size: 1.8rem;
        }

        .advantages-intro p {
            font-size: 1rem;
        }

        .advantages-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .premium-grid {
            grid-template-columns: 1fr;
        }

        .partners-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
        }

        .section-title {
            padding-top: 60px;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 1.8rem;
        }

        .section-title p {
            font-size: 1rem;
        }

        .partner-footer-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .partner-footer-image {
            height: 350px;
        }

        .partner-footer-content {
            padding: 0;
        }

        .partner-footer-title {
            font-size: 1.6rem;
        }

        .partner-footer-card {
            padding: 30px 20px;
        }

        .partner-footer-btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
</style>

<!-- HERO PREMIUM -->
<header class="hero--partners">
    <div class="hero--partners-overlay"></div>
    
    <div class="hero--partners-content">
        <h1>
            <span class="main-line">Nos <span class="highlight">Partenaires</span></span>
            <span class="subtitle-line">— Font Notre Force —</span>
        </h1>
        
        <p class="hero--partners-subtitle">
            Des entreprises <strong>engagées</strong> qui nous font confiance et partagent nos valeurs.<br>
            Ensemble, construisons l'avenir du <strong>football moulonnais</strong>.
        </p>

        <div class="hero--partners-actions">
            <a href="<?= url('devenir_partenaire') ?>" class="hero--partners-cta">
                <span>💼</span>
                <span>Devenir Partenaire</span>
            </a>
            <a href="#avantages" class="hero--partners-cta-secondary">
                <span>📊</span>
                <span>Découvrir les Avantages</span>
            </a>
        </div>
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
</div>

<!-- SECTION AVANTAGES -->
<section class="advantages-section" id="avantages">
    <div class="advantages-container">
        <div class="advantages-intro">
            <h2>Pourquoi devenir <span class="highlight">Partenaire</span> ?</h2>
            <p>
                Un partenariat avec l'ES Moulon vous offre bien plus qu'une simple exposition.
                C'est l'opportunité de vous associer à un club historique, de toucher une communauté passionnée
                et de contribuer à l'essor du football local.
            </p>
        </div>

        <div class="advantages-grid">
            <div class="advantage-card">
                <span class="advantage-icon">📢</span>
                <h3 class="advantage-title">Visibilité Maximale</h3>
                <p class="advantage-desc">
                    Logo sur nos supports digitaux, réseaux sociaux, panneaux publicitaires au stade
                    et lors de nos événements majeurs. Plus de 15 000 visiteurs par an !
                </p>
            </div>

            <div class="advantage-card">
                <span class="advantage-icon">🤝</span>
                <h3 class="advantage-title">Image Positive</h3>
                <p class="advantage-desc">
                    Associez votre marque aux valeurs du sport : esprit d'équipe, dépassement de soi,
                    fair-play et engagement local. Un investissement RSE valorisant.
                </p>
            </div>

            <div class="advantage-card">
                <span class="advantage-icon">🌍</span>
                <h3 class="advantage-title">Ancrage Local</h3>
                <p class="advantage-desc">
                    Renforcez votre présence sur le territoire bourguignon. L'ES Moulon, c'est une
                    communauté de plus de 500 licenciés et des milliers de supporters fidèles.
                </p>
            </div>

            <div class="advantage-card">
                <span class="advantage-icon">🎟️</span>
                <h3 class="advantage-title">Invitations VIP</h3>
                <p class="advantage-desc">
                    Offrez à vos clients et collaborateurs des places privilégiées lors de nos matchs,
                    dans des espaces dédiés avec hospitalités.
                </p>
            </div>

            <div class="advantage-card">
                <span class="advantage-icon">📊</span>
                <h3 class="advantage-title">Retombées Mesurables</h3>
                <p class="advantage-desc">
                    Accès aux statistiques de fréquentation, rapports d'activité et bilans de visibilité.
                    Un partenariat transparent et professionnel.
                </p>
            </div>

            <div class="advantage-card">
                <span class="advantage-icon">💚</span>
                <h3 class="advantage-title">Soutien à la Jeunesse</h3>
                <p class="advantage-desc">
                    Contribuez à la formation de plus de 250 jeunes chaque année. Un impact concret
                    sur l'avenir sportif et éducatif de la région.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- STATISTIQUES D'IMPACT -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stats-title">
            <h3>L'Impact de Nos Partenariats</h3>
            <p>Des chiffres qui témoignent de notre rayonnement</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-icon">⚽</span>
                <span class="stat-number">250+</span>
                <span class="stat-label">Jeunes Formés</span>
            </div>
            <div class="stat-item">
                <span class="stat-icon">👥</span>
                <span class="stat-number">500+</span>
                <span class="stat-label">Licenciés</span>
            </div>
            <div class="stat-item">
                <span class="stat-icon">🏆</span>
                <span class="stat-number">85</span>
                <span class="stat-label">Ans d'Histoire</span>
            </div>
            <div class="stat-item">
                <span class="stat-icon">🎯</span>
                <span class="stat-number">15K+</span>
                <span class="stat-label">Visiteurs/an</span>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <!-- CTA DEVENIR PARTENAIRE -->
    <?php
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

    <section class="partner-footer-section">
        <div class="partner-footer-grid">
            <div class="partner-footer-image">
                <img src="<?= htmlspecialchars($partnership_img) ?>"
                    alt="Rejoignez nos partenaires"
                    onerror="this.src='<?= asset('img/default-partnership.jpg') ?>'">
            </div>

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
                        Être partenaire de l'ES Moulon, c'est bien plus qu'un logo sur un maillot.
                        C'est soutenir un club historique fondé en 1940, une aventure humaine et une fierté locale qui fait vibrer Bourges et tout le Cher.
                        <br><br>
                        <strong>Ensemble, donnons plus de moyens à nos jeunes, plus de visibilité à votre entreprise, et plus de passion à nos supporters.</strong>
                    </p>

                    <a href="<?= url('devenir_partenaire') ?>" class="partner-footer-btn">
                        💼 Devenir Partenaire →
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>