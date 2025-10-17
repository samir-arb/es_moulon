<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>

<style>
    :root {
        --vert-esm: #1c995a;
        --vert-fonce: #0b562b;
        --gris: #6b7280;
        --blanc: #ffffff;
        --gris-clair: #f5f7f6;
        --noir: #1f2937;
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
        padding: 60px 20px;
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

    /* SECTION */
    .section-history {
        max-width: 1100px;
        margin: 100px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 40px;
        align-items: center;
    }

    .section-history:nth-child(even) { 
        direction: rtl; 
        text-align: left; 
    } 
    .section-history:nth-child(even) .history-photo { 
        direction: ltr; 
    }

    .section-history h2 {
        font-size: 2.2rem;
        color: var(--vert-esm);
        font-weight: 800;
        margin-bottom: 20px;
        direction: ltr;
        unicode-bidi: bidi-override;
    }

    .section-history p {
        color: var(--gris);
        font-size: 1.05rem;
    }

    .history-photo {
        margin-top: 80px;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
    }

    .history-image {
        width: 100%;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        object-fit: cover;
        transition: transform 0.4s ease; 
        box-shadow: 0.3s ease;
    }

    .history-image:hover {
        transform: scale(1.03);
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    }

    .photo-caption {
        font-size: 0.95rem;
        color: #444;
        margin-top: 10px;
        font-style: italic;
    }

    /* GALERIE RÉTRO */
    .gallery {
        background: var(--blanc);
        padding: 50px 20px;
        /* border-top: 5px solid var(--vert-esm);*/
    }

    .gallery h2 {
        text-align: center;
        color: var(--vert-esm);
        font-size: 2.4rem;
        font-weight: 800;
        margin-bottom: 100px;
    }

    .gallery-grid {
        max-width: 1100px;
        margin: 0 auto 40px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .gallery-grid img {
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease;
    }

    .gallery-grid img:hover {
        transform: scale(1.03);
    }

    /* VALEURS */
    .values {
        background: var(--gris-clair);
        padding: 100px 20px;
        text-align: center;
    }

    .values h2 {
        font-size: 2.3rem;
        color: var(--vert-esm);
        margin-bottom: 60px;
        font-weight: 800;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 300px));
        gap: 40px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .value-card {
        background: white;
        padding: 40px 30px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .value-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(28, 153, 90, 0.25);
    }

    .value-icon {
        font-size: 2.5rem;
        margin-bottom: 20px;
        color: var(--vert-esm);
    }

    .value-card h3 {
        font-size: 1.4rem;
        color: var(--noir);
        margin-bottom: 10px;
    }

    .value-card p {
        color: var(--gris);
        font-size: 1rem;
    }

    /* CTA */
    .cta {
        background: linear-gradient(180deg, var(--vert-esm), var(--vert-fonce));
        color: white;
        text-align: center;
        padding: 40px 20px;
    }

    .cta h2 {
        font-size: 2.4rem;
        margin-bottom: 20px;
        font-weight: 800;
    }

    .cta p {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 30px;
    }

    @media (max-width: 1200px) {
        .section-history {
            gap: 30px;
        }
    }

    @media (min-width: 1100px) {
        .values-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 60px 120px;
        }
    }

    @media (max-width: 900px) {
        .section-history {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .section-history:nth-child(even) {
            direction: ltr;
        }
    }
</style>


    <!-- HERO -->
    <section class="hero-history">
        <h1>Notre Histoire & Nos Valeurs</h1>
        <p>Depuis 1940, l’Espérance Sportive du Moulon incarne la passion, la solidarité et la fierté du football berruyer.</p>
    </section>

    <!-- HISTOIRE -->
    <section class="section-history">
        <div>
            <h2>Une histoire née de la passion</h2>
            <p>
                Fondée le <strong>13 novembre 1940</strong>, l’Espérance Sportive du Moulon est l’un des clubs les plus anciens du Cher.
                De ses débuts sur un terrain en <strong>terre battue</strong> jusqu’à la création du premier
                <strong>terrain synthétique de Bourges</strong> en 1994, l’ESM a accompagné des générations de joueurs.
                <br><br>
                Club formateur et convivial, le Moulon s’est forgé une identité forte : un football populaire, ouvert à tous,
                basé sur le respect, la solidarité et l’engagement.
            </p>
        </div>
        <div class="history-photo">
            <img src="../../public/assets/img/stade_1994.jpeg" alt="Terrain synthétique du stade de la Sente aux Loups" class="history-image">
            <p class="photo-caption">Le premier terrain synthétique de Bourges, inauguré au stade de la Sente-aux-Loups en 1994.</p>
        </div>
     <!--<div class="history-photo">
            <img src="../../public/assets/img/terrain_esm.jpg" alt="Terrain synthétique du stade de la Sente aux Loups" class="history-image">
            <p class="photo-caption"> En 2010 le synthétique est <strong>entièrement rénové</strong> pour moderniser les infrastructures.</p>
        </div>   -->
    </section>

    <!-- ANCIENNES PHOTOS -->
    <section class="gallery">
        <h2>Un héritage qui traverse les générations</h2>
        <div class="gallery-grid">
            <img src="../../public/assets/img/esm_1970.webp" alt="Équipe du Moulon dans les années 60">
            <img src="../../public/assets/img/esm_jeunes.webp" alt="Jeunes joueurs du Moulon">
            <img src="../../public/assets/img/esm_1990.webp" alt="Ancienne équipe séniors ES Moulon">
        </div>
    </section>

    <!-- STADE JACQUES LOUBIER -->
    <section class="section-history">
        <div>
            <h2>Le stade Jacques Loubier, <br>un hommage à la passion</h2>
            <p>
                Le <strong>12 octobre 2025</strong>, le stade de la Sente-aux-Loups a été renommé
                <strong>Stade Jacques Loubier</strong>, en hommage à un dirigeant emblématique.
                Arrivé au club dans les années 1960, Jacques Loubier a été successivement joueur, éducateur, trésorier puis président.
                <br><br>
                L’inauguration a réuni la famille Loubier, la municipalité et de nombreux Moulonnais,
                dans un moment d’émotion et de reconnaissance envers un homme dont la passion a marqué plusieurs générations.
            </p>
        </div>
        <div class="history-photo">
            <img src="../../public/assets/img/stade_jacques_loubier.png" alt="Inauguration du stade Jacques Loubier" class="history-image">
            <p class="photo-caption">Inauguration du <strong>Stade Jacques Loubier</strong> – octobre 2025.</p>
        </div>
    </section>

    <!-- PALMARÈS -->
    <section class="section-history">
        <div>
            <h2>Figures marquantes & palmarès local</h2>
            <p>
                L’Espérance Sportive du Moulon a vu naître et grandir plusieurs talents qui ont marqué le football français.
                Parmi eux, <strong>Sébastien Dallet</strong>, formé au club, a poursuivi une brillante carrière professionnelle
                en <strong>Ligue 1</strong> et <strong>Ligue 2</strong> sous les couleurs de Lens, Sochaux, Guingamp ou encore Troyes.
                <strong>Jean-Pierre Meudic</strong>, autre joueur emblématique du Moulon, a lui aussi porté haut les valeurs du club sur les terrains de la région.
                Plus récemment, le jeune <strong>Amidou Doumbouya</strong>, issu de la formation moulonnaise, a rejoint le <strong>centre de formation de l’OGC Nice</strong>,
                symbole de la continuité du travail accompli depuis plus de 80 ans.
                <br><br>
                Côté palmarès, le club s’est illustré à plusieurs reprises dans les compétitions locales,
                notamment en <strong>Coupe du Cher</strong>, qu’il a remportée chez les séniors en 2018.
                L’ES Moulon continue d’y participer avec fierté, dans le même esprit de combativité et de respect qui anime toutes ses équipes.
            </p>
        </div>
        <div class="history-photo">
            <img src="../../public/assets/img/coupe_cher.webp" alt="Trophées et figures du club" class="history-image">
            <p class="photo-caption">Des générations de passionnés au service du maillot vert et blanc.</p>
        </div>
    </section>

    <!-- VALEURS -->
    <section class="values">
        <h2>Nos Valeurs</h2>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">💚</div>
                <h3>Passion & Engagement</h3>
                <p>Depuis 1940, un même feu anime joueurs, éducateurs, dirigeants et bénévoles du Moulon.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🤝</div>
                <h3>Solidarité & Respect</h3>
                <p>Le collectif avant tout : respect du jeu, des adversaires et des habitants du quartier.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🌱</div>
                <h3>Formation & Ambition</h3>
                <p>Accompagner chaque jeune pour grandir humainement et sportivement sous nos couleurs.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🏟️</div>
                <h3>Héritage & Transmission</h3>
                <p>Préserver la mémoire de ceux qui ont bâti le club pour inspirer les générations futures.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <h2>Une aventure qui continue depuis 1940</h2>
        <p>Rejoignez ceux qui écrivent chaque jour la suite de notre histoire.</p>
        <a href="<?= url('Rejoignez_nous/nous_contactez') ?>" class="btn-gradient">Rejoignez-nous</a>
    </section>
