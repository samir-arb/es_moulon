<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>

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
        --noir: #1f2937;
        --vert-esm: #1c995a;
        --vert-fonce: #0b562b;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #ecfff2ff;
        color: var(--noir);
        margin: 0;
        line-height: 1.7;
    }

     /* HERO */
    .hero-history {
        background: linear-gradient(180deg, var(--vert-fonce), var(--vert-esm));
        color: white;
        text-align: center;
        padding: 60px 20px 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-history h1, .hero-history p {
        opacity: 0;
        animation: fadeUp 1.2s ease-out forwards;
    }

    .hero-history h1 {
        font-size: clamp(30px, 5vw, 52px);
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .hero-history p {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 750px;
        margin: 0 auto;
    }

    /* HERO SECTION */
    .hero-benevoles {
        position: relative;
        background: linear-gradient(135deg, #007a3d 0%, #009639 50%, #f9f9f9 100%);
        color: var(--white);
        padding: 30px 20px 50px;
        text-align: center;
        overflow: hidden;
    }

    .hero-benevoles::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -10%;
        width: 120%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 15s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 900px;
        margin: 0 auto;
    }

    .hero-benevoles h1 {
        font-size: 3.5em;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 20px;
        text-shadow: 2px 4px 8px rgba(0,0,0,0.3);
    }

    .hero-benevoles .subtitle {
        font-size: 1.3em;
        font-weight: 300;
        opacity: 0.95;
        line-height: 1.6;
    }

    .heart-icon {
        font-size: 4em;
        margin-bottom: 20px;
        animation: heartbeat 1.5s ease-in-out infinite;
    }

    @keyframes heartbeat {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.15); }
        50% { transform: scale(1); }
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
        padding: 60px 20px;
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

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
        margin-top: 50px;
    }
    
    .stat-box {
        background: linear-gradient(135deg, #009639, #00b34a);
        color: var(--white);
        padding: 30px 20px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,150,57,0.3);
        transition: transform 0.3s ease;
    }

    .stat-box:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 3em;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .stat-label {
        font-size: 1.1em;
        font-weight: 300;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* SECTION MISSIONS */
    .missions-section {
        padding: 80px 20px;
        background: linear-gradient(to bottom, #f5f5f5 0%, #00b34a 100%);
    }

    .section-title {
        text-align: center;
        font-size: 2.5em;
        color: var(--green-primary);
        margin-bottom: 20px;
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

    .section-subtitle {
        text-align: center;
        font-size: 1.1em;
        color: #666;
        margin-bottom: 50px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    .missions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }
    :root {
        --green-primary: #009639;
        --green-light: #00b34a;
        --green-dark: #007a3d;
        --white: #ffffff;
        --grey-light: #f9f9f9;
    }

    .mission-card {
        background: var(--white);
        padding: 35px 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border-left: 5px solid #009639;
        transition: all 0.3s ease;
    }

    .mission-card:hover {
        transform: translateX(10px);
        box-shadow: 0 10px 30px rgba(0,150,57,0.15);
    }

    .mission-icon {
        font-size: 2.8em;
        margin-bottom: 20px;
    }

    .mission-card h3 {
        font-size: 1.4em;
        color: var(--green-dark);
        margin-bottom: 15px;
        font-weight: 600;
    }

    .mission-card p {
        color: #666;
        line-height: 1.7;
        font-size: 1.05em;
    }

    /* SECTION TEMOIGNAGES */
    .testimonials-section {
        background: var(--white);
        padding: 80px 20px;
    }

    .testimonials-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .testimonial {
        background: linear-gradient(135deg, var(--green-primary), var(--green-light));
        color: var(--white);
        padding: 40px;
        border-radius: 20px;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,150,57,0.3);
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

    /* SECTION DEVENIR BENEVOLE */
    .become-benevole {
        background: linear-gradient(to bottom, #f5f5f5 0%, var(--grey-light) 100%);
        padding: 80px 20px;
    }

    .become-content {
        max-width: 900px;
        margin: 0 auto;
    }

    .become-benevole h2 {
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
        background: var(--white);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border-top: 5px solid var(--green-primary);
        transition: transform 0.3s ease;
    }

    .benefit-card:hover {
        transform: translateY(-5px);
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

    /* SECTION PROCESSUS */
    .process-section {
        background: var(--white);
        padding: 80px 20px;
    }

    .process-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-top: 50px;
        max-width: 1000px;
        margin-left: auto;
        margin-right: auto;
    }

    .step {
        text-align: center;
        position: relative;
    }

    .step-number {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--green-primary), var(--green-light));
        color: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8em;
        font-weight: 800;
        margin: 0 auto 20px;
        box-shadow: 0 8px 20px rgba(0,150,57,0.3);
    }

    .step h3 {
        font-size: 1.3em;
        color: var(--green-dark);
        margin-bottom: 10px;
        font-weight: 600;
    }

    .step p {
        color: #666;
        line-height: 1.6;
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
        box-shadow: 0 10px 25px rgba(0,150,57,0.4);
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
        background: rgba(255,255,255,0.2);
        transition: left 0.5s ease;
    }

    .btn-cta:hover::before {
        left: 100%;
    }

    .btn-cta:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0,150,57,0.5);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .hero-benevoles h1 {
            font-size: 2.2em;
        }

        .hero-benevoles .subtitle {
            font-size: 1.1em;
        }

        .section-title {
            font-size: 2em;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }

        .missions-grid,
        .benefits-grid,
        .process-steps {
            grid-template-columns: 1fr;
        }

        .btn-cta {
            padding: 15px 35px;
            font-size: 1em;
        }
    }
</style>

<!-- HERO -->
<section class="hero-history">   
    <h1>Nos Bénévoles</h1>
    <p>Le cœur battant de l'ES Moulon. Sans eux, rien ne serait possible. 
    Découvrez l'engagement extraordinaire qui fait vivre notre club jour après jour.</p>
    <div class="heart-icon">💚</div>
</section>

    <!-- INTRO SECTION -->
    <section class="intro-section">
        <div class="container">
            <h2>L'Âme du Club</h2>
            <p>
                À l'ES Moulon, nos bénévoles sont bien plus que de simples membres : ce sont les piliers 
                qui soutiennent chaque entraînement, chaque match, chaque événement. Leur dévouement 
                incarne les valeurs vert et blanc de solidarité, de passion et de générosité 
                qui font la fierté de notre club.
            </p>

            <div class="stats-container">
                <div class="stat-box">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Bénévoles actifs</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Heures données</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Passion</div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION MISSIONS -->
    <section class="missions-section">
        <div class="container">
            <h2 class="section-title">Les Missions des Bénévoles</h2>
            <p class="section-subtitle">
                Chaque bénévole contribue à sa manière à la vie du club. 
                Voici quelques-unes des missions essentielles qu'ils accomplissent avec passion.
            </p>

            <div class="missions-grid">
                <div class="mission-card">
                    <div class="mission-icon">🏟️</div>
                    <h3>Organisation des Matchs</h3>
                    <p>
                        Préparation des terrains, accueil des équipes adverses, gestion des buvettes, 
                        tenue des scores... Les bénévoles assurent le bon déroulement de chaque rencontre.
                    </p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">📋</div>
                    <h3>Gestion Administrative</h3>
                    <p>
                        Inscriptions, licences, convocations, comptabilité... Une partie invisible mais 
                        cruciale pour le fonctionnement quotidien du club.
                    </p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">🚌</div>
                    <h3>Transport & Logistique</h3>
                    <p>
                        Accompagnement des équipes lors des déplacements, organisation des covoiturages, 
                        gestion du matériel sportif.
                    </p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">🎉</div>
                    <h3>Événements & Animations</h3>
                    <p>
                        Tournois, fêtes du club, ventes de gâteaux, lotos... Les bénévoles créent 
                        la convivialité et renforcent les liens entre tous les membres.
                    </p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">🧹</div>
                    <h3>Entretien des Installations</h3>
                    <p>
                        Maintenance des vestiaires, tonte des pelouses, petits travaux... 
                        Ils veillent à ce que nos infrastructures soient toujours impeccables.
                    </p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">📢</div>
                    <h3>Communication</h3>
                    <p>
                        Gestion des réseaux sociaux, rédaction d'articles, photos des matchs... 
                        Ils font rayonner l'image de l'ES Moulon.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION TÉMOIGNAGES -->
    <section class="testimonials-section">
        <div class="container">
            <h2 class="section-title">Ils Témoignent</h2>
            <div class="testimonials-container">
                <div class="testimonial">
                    <p class="testimonial-text">
                        "S'engager comme bénévole à l'ES Moulon, c'est bien plus que donner de son temps. 
                        C'est faire partie d'une vraie famille, partager des moments inoubliables et voir 
                        les enfants grandir avec le sourire. Je ne regrette rien !"
                    </p>
                    <p class="testimonial-author">— Barbara, bénévole depuis 2022</p>
                </div>

                <div class="testimonial">
                    <p class="testimonial-text">
                        "Au début, je suis venu aider pour le tournoi des jeunes. Aujourd'hui, je suis 
                        trésorier du club ! L'ambiance est géniale, on se sent utile et on crée des liens 
                        incroyables. C'est une aventure humaine formidable."
                    </p>
                    <p class="testimonial-author">— Rachid, bénévole et trésorier</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION DEVENIR BÉNÉVOLE -->
    <section class="become-benevole">
        <div class="container">
            <div class="become-content">
                <h2>Rejoignez l'Aventure !</h2>
                <p class="become-intro">
                    Vous souhaitez vous investir dans un projet collectif porteur de sens ? 
                    Devenir bénévole à l'ES Moulon, c'est contribuer à l'épanouissement de centaines 
                    de licenciés, participer à la vie d'un club historique et vivre des moments uniques.
                </p>

                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon">🤝</div>
                        <h3>Liens Sociaux</h3>
                        <p>
                            Rencontrez des personnes passionnées de tous horizons et créez des amitiés 
                            durables dans une ambiance chaleureuse.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">💡</div>
                        <h3>Nouvelles Compétences</h3>
                        <p>
                            Développez des compétences en organisation, gestion, communication et 
                            bien d'autres domaines utiles au quotidien.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">🎯</div>
                        <h3>Engagement Flexible</h3>
                        <p>
                            Choisissez vos missions selon vos disponibilités et vos envies. 
                            Quelques heures par mois suffisent pour faire la différence !
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">⚽</div>
                        <h3>Passion Partagée</h3>
                        <p>
                            Vivez votre amour du football autrement, au cœur de l'action, 
                            entouré de personnes qui partagent votre enthousiasme.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">🏆</div>
                        <h3>Fierté & Reconnaissance</h3>
                        <p>
                            Contribuez concrètement aux succès du club et recevez la gratitude 
                            de toute la communauté de l'ES Moulon.
                        </p>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">🌟</div>
                        <h3>Moments Inoubliables</h3>
                        <p>
                            Partagez des victoires, des célébrations, des tournois et des événements 
                            qui resteront gravés dans votre mémoire.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION PROCESSUS -->
    <section class="process-section">
        <div class="container">
            <h2 class="section-title">Comment Devenir Bénévole ?</h2>
            <p class="section-subtitle">
                Un processus simple et rapide pour rejoindre notre équipe de bénévoles.
            </p>

            <div class="process-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Contactez-nous</h3>
                    <p>
                        Remplissez le formulaire en ligne ou venez nous rencontrer directement 
                        lors d'un match ou d'un entraînement.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Échangeons</h3>
                    <p>
                        Discutons de vos envies, de vos disponibilités et des missions qui 
                        vous correspondent le mieux.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Commencez !</h3>
                    <p>
                        Rejoignez l'équipe des bénévoles et découvrez immédiatement l'ambiance 
                        familiale et conviviale de l'ES Moulon.
                    </p>
                </div>
            </div>

            <div class="cta-container">
                <a href="/es_moulon/pages/rejoindre/devenir-benevole.php" class="btn-cta">
                    💚 Je deviens bénévole
                </a>
            </div>
        </div>
    </section>

</body>
</html>