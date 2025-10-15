<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';

// Récupérer les arbitres
$sql = "
    SELECT u.id_user, u.first_name, u.name, m.file_path AS photo
    FROM users u
    INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
    INNER JOIN club_functions f ON ucf.id_club_function = f.id_club_function
    LEFT JOIN medias m ON u.id_media = m.id_media
    WHERE f.function_name LIKE '%arbitre%'
    GROUP BY u.id_user
    ORDER BY u.name ASC
";
$result = $conn->query($sql);
$arbitres = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Arbitres - ES Moulon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
            color: #333;
            overflow-x: hidden;
        }

        :root {
            --green-primary: #009639;
            --green-light: #00b34a;
            --green-dark: #007a3d;
            --white: #ffffff;
            --grey-light: #f9f9f9;
        }

        /* HERO SECTION */
        .hero-arbitres {
            position: relative;
            background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-primary) 50%, var(--green-light) 100%);
            color: var(--white);
            padding: 30px 20px 50px;
            text-align: center;
            overflow: hidden;
        }

        .hero-arbitres::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 120%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero-arbitres h1 {
            font-size: 3.5em;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.3);
        }

        .hero-arbitres .subtitle {
            font-size: 1.3em;
            font-weight: 300;
            opacity: 0.95;
            line-height: 1.6;
        }

        .whistle-icon {
            font-size: 4em;
            margin-bottom: 20px;
            animation: swing 2s ease-in-out infinite;
        }

        @keyframes swing {

            0%,
            100% {
                transform: rotate(-10deg);
            }

            50% {
                transform: rotate(10deg);
            }
        }

        /* CONTAINER */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* SECTION INTRO */
        .intro-section {
            background: var(--white);
            padding: 25px;
            text-align: center;
        }

        .intro-section h2 {
            font-size: 2.2em;
            color: var(--green-primary);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .intro-section p {
            font-size: 1.1em;
            line-height: 1.8;
            color: #555;
            max-width: 800px;
            margin: 0 auto 30px;
        }

        /* GRILLE ARBITRES */
        .arbitres-section {
            padding: 80px 20px;
            background: linear-gradient(to bottom, #f5f5f5 0%, var(--grey-light) 100%);
        }

        .section-title {
            text-align: center;
            font-size: 2.5em;
            color: var(--green-primary);
            margin-bottom: 50px;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--green-light);
            border-radius: 2px;
        }

        .arbitres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 35px;
            margin-bottom: 60px;
        }

        .arbitre-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .arbitre-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--green-primary), var(--green-light));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .arbitre-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 150, 57, 0.2);
        }

        .arbitre-card:hover::before {
            transform: scaleX(1);
        }

        .photo-container {
            width: 160px;
            height: 160px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--green-primary);
            box-shadow: 0 8px 20px rgba(0, 150, 57, 0.25);
            position: relative;
            background: linear-gradient(135deg, var(--green-light), var(--green-primary));
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .arbitre-card:hover .photo-container img {
            transform: scale(1.1);
        }

        .arbitre-name {
            font-size: 1.3em;
            font-weight: 700;
            color: var(--green-dark);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .arbitre-firstname {
            font-size: 1.05em;
            color: #666;
            margin-bottom: 15px;
            font-weight: 400;
        }

        .arbitre-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--green-primary), var(--green-light));
            color: var(--white);
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(0, 150, 57, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            font-size: 1.2em;
            grid-column: 1 / -1;
        }

        /* SECTION DEVENIR ARBITRE */
        .become-arbitre {
            background: var(--white);
            padding: 80px 20px;
        }

        .become-content {
            max-width: 900px;
            margin: 0 auto;
        }

        .become-arbitre h2 {
            font-size: 2.5em;
            color: var(--green-primary);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .become-intro {
            font-size: 1.15em;
            text-align: center;
            color: #555;
            line-height: 1.8;
            margin-bottom: 50px;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .benefit-card {
            background: linear-gradient(135deg, #f9f9f9, var(--white));
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 5px solid var(--green-primary);
            transition: transform 0.3s ease;
        }

        .benefit-card:hover {
            transform: translateX(10px);
        }

        .benefit-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }

        .benefit-card h3 {
            font-size: 1.3em;
            color: var(--green-dark);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .benefit-card p {
            color: #666;
            line-height: 1.6;
        }

        .testimonial {
            background: linear-gradient(135deg, var(--green-primary), var(--green-light));
            color: var(--white);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin: 50px 0;
            box-shadow: 0 10px 30px rgba(0, 150, 57, 0.3);
        }

        .testimonial-text {
            font-size: 1.2em;
            font-style: italic;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .testimonial-author {
            font-weight: 600;
            font-size: 1.1em;
        }

        /* CTA BUTTON */
        .cta-container {
            text-align: center;
            margin-top: 50px;
        }

        .btn-cta {
            display: inline-block;
            background: linear-gradient(135deg, var(--green-primary), var(--green-light));
            color: var(--white);
            padding: 18px 50px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: 700;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(0, 150, 57, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }

        .btn-cta:hover::before {
            left: 100%;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 150, 57, 0.5);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .hero-arbitres h1 {
                font-size: 2.2em;
            }

            .hero-arbitres .subtitle {
                font-size: 1.1em;
            }

            .section-title {
                font-size: 2em;
            }

            .arbitres-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 25px;
            }

            .benefits-grid {
                grid-template-columns: 1fr;
            }

            .btn-cta {
                padding: 15px 35px;
                font-size: 1em;
            }
        }
    </style>
</head>

<body>

    <!-- HERO SECTION -->
    <section class="hero-arbitres">
        <div class="hero-content">
            <div class="whistle-icon">🎯</div>
            <h1>Nos Arbitres</h1>
            <p class="subtitle">
                Les gardiens de l'équité, les garants du fair-play.<br>
                Découvrez les hommes et femmes qui font vivre les valeurs du football à l' ES Moulon.
            </p>
        </div>
    </section>

    <!-- INTRO SECTION -->
    <section class="intro-section">
        <div class="container">
            <h2>Le Cœur de Chaque Match</h2>
            <p>
                Sans arbitre, pas de match. Nos arbitres sont bien plus que des juges de jeu :
                ce sont des passionnés qui garantissent le respect, l'intégrité et l'esprit sportif
                dans chaque rencontre. Leur engagement fait honneur aux couleurs vert et blanc de notre club.
            </p>
        </div>
    </section>

    <!-- SECTION ARBITRES -->
    <section class="arbitres-section">
        <div class="container">
            <h2 class="section-title">Notre équipe d'arbitres</h2>

            <div class="arbitres-grid">
                <?php if (empty($arbitres)): ?>
                    <div class="empty-state">
                        <p>🎽 Aucun arbitre enregistré pour le moment.</p>
                        <p>Vous souhaitez devenir le premier arbitre officiel de l'ES Moulon ?</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($arbitres as $arbitre): ?>
                        <div class="arbitre-card">
                            <div class="photo-container">
                                <img src="<?= asset($arbitre['photo'] ?? 'uploads/default.png') ?>"
                                    alt="Photo de <?= htmlspecialchars($arbitre['first_name'] . ' ' . $arbitre['name']) ?>">
                            </div>
                            <h3 class="arbitre-name"><?= htmlspecialchars($arbitre['name']) ?></h3>
                            <p class="arbitre-firstname"><?= htmlspecialchars($arbitre['first_name']) ?></p>
                            <span class="arbitre-badge">Arbitre Officiel</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- SECTION DEVENIR ARBITRE -->
    <section class="become-arbitre">
        <div class="container">
            <div class="become-content">
                <h2>Devenez Arbitre à l'ES Moulon</h2>
                <p class="become-intro">
                    Vous aimez le football et souhaitez vous impliquer autrement ?
                    Devenir arbitre, c'est vivre le jeu de l'intérieur, prendre des responsabilités
                    et contribuer activement à la vie du club. Une aventure humaine unique vous attend.
                </p>

                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon">⚽</div>
                        <h3>Comprenez le Jeu</h3>
                        <p>
                            Approfondissez vos connaissances des règles du football et
                            développez une vision tactique incomparable depuis le terrain.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">🎓</div>
                        <h3>Formation Gratuite</h3>
                        <p>
                            Bénéficiez d'une formation complète assurée par la Ligue,
                            avec accompagnement personnalisé et montée en compétence progressive.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">🤝</div>
                        <h3>Esprit d'Équipe</h3>
                        <p>
                            Intégrez une communauté soudée, partagez des moments forts
                            et tissez des liens durables avec d'autres passionnés.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">💪</div>
                        <h3>Développement Personnel</h3>
                        <p>
                            Renforcez votre confiance, votre capacité de décision et
                            votre maîtrise du stress dans des situations challengeantes.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">💰</div>
                        <h3>Indemnités de Match</h3>
                        <p>
                            Recevez une rémunération pour chaque rencontre arbitrée,
                            avec possibilité d'évolution selon votre niveau.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">🏆</div>
                        <h3>Reconnaissance du Club</h3>
                        <p>
                            Devenez un pilier essentiel de l'ES Moulon et gagnez
                            le respect de tous les membres du club et des supporters.
                        </p>
                    </div>
                </div>

                <div class="testimonial">
                    <p class="testimonial-text">
                        "Devenir arbitre a changé ma vision du football. C'est exigeant,
                        mais tellement enrichissant ! On est au cœur de l'action, et chaque
                        décision compte. Je me sens vraiment utile au club."
                    </p>
                    <p class="testimonial-author">— Thomas, arbitre ES Moulon depuis 2022</p>
                </div>

                <div class="cta-container">
                    <a href="/es_moulon/pages/rejoignez_nous/devenir-arbitre.php" class="btn-cta">
                        🎽 Je deviens arbitre
                    </a>
                </div>
            </div>
        </div>
    </section>

</body>

</html>