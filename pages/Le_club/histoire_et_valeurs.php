<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>


<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Histoire & Valeurs — ES Moulon</title>

<style>
    :root {
    --vert-esm: #1c995a;
    --vert-fonce: #0b562bff;
    --or: #d4af37;
    --gris: #6b7280;
    --blanc: #ffffff;
    --noir: #1f2937;
    --gris-clair: #f7f9f8;
    }

    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--gris-clair);
    color: var(--noir);
    margin: 0;
    line-height: 1.7;
    }

        /* ========================= HERO ========================= */
        .hero-history {
    background: linear-gradient(180deg, var(--vert-fonce), var(--vert-esm)),
                url('img/histoire_et_valeurs.jpg') center/cover no-repeat;
    color: #fff;
    text-align: center;
    padding: 60px 20px;
    border-radius: 0 0 40px 40px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    position: relative;
    overflow: hidden;
    }

    .hero-history h1 {
    font-size: clamp(32px, 5vw, 54px);
    font-weight: 900;
    letter-spacing: 2px;
    margin-bottom: 15px;
    text-transform: uppercase;
    }

    .hero-history p {
    font-size: 1.2rem;
    max-width: 700px;
    margin: 0 auto;
    color: rgba(255,255,255,0.95);
    }

    /* ========================= SECTION HISTOIRE ========================= */
    .section-history {
    max-width: 1200px;
    margin: 100px auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 60px;
    align-items: center;
    }

    .section-history img {
    width: 100%;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    object-fit: cover;
    transition: transform 0.4s ease;
    }

    .section-history img:hover {
        transform: scale(1.02);
    }

    .history-photo {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    }

    .history-photo .history-image {
        max-width: 550px;
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        margin-bottom: 10px;
        transition: transform 0.3s ease;
    }

    .history-photo .history-image:hover {
        transform: scale(1.02);
    }

    .photo-caption {
    font-size: 0.95rem;
    color: #555;
    margin: 0px;
    font-style: italic;
    max-width: 550px;
    }

    .section-history h2 {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--vert-esm);
    margin-bottom: 20px;
    }

    .section-history p {
    color: var(--gris);
    font-size: 1.05rem;
    }

    /* ========================= SECTION VALEURS ========================= */
    .values {
    background: var(--blanc);
    padding: 100px 20px;
    text-align: center;
    border-top: 6px solid var(--vert-esm);
    }

    .values h2 {
    font-size: 2.3rem;
    color: var(--vert-esm);
    margin-bottom: 60px;
    font-weight: 800;
    }

    .values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    max-width: 1100px;
    margin: 0 auto;
    }

    .value-card {
    background: var(--gris-clair);
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    }

    .value-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(28,153,90,0.25);
    }

    .value-icon {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: var(--vert-esm);
    }

    .value-card h3 {
    color: var(--noir);
    font-size: 1.4rem;
    margin-bottom: 10px;
    }

    .value-card p {
    color: var(--gris);
    font-size: 1rem;
    }

    /* ========================= CTA ========================= */
    .cta {
    background: linear-gradient(180deg, var(--vert-esm), var(--vert-fonce));
    color: white;
    text-align: center;
    padding: 50px 30px;
    
    border-radius:40px 40px 0 0 ;
    }

    .cta h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    font-weight: 800;
    }

    .cta p {
    font-size: 1.3rem;
    opacity: 0.9;
    margin-bottom: 60px;
    }

    /* ========================= RESPONSIVE ========================= */
    @media (max-width: 900px) {
    .section-history {
        grid-template-columns: 1fr;
    }

    .section-history {
        margin: 60px auto;
    }
    }
</style>
</head>

<body>

<section class="hero-history">
  <h1>Notre Histoire & Nos Valeurs</h1>
  <p>Depuis 1940, l’Espérance Sportive du Moulon incarne la passion, le respect et la fierté du football berruyer.</p>
</section>

<section class="section-history">
    <div>
        <h2>Une histoire née de la passion</h2>
        <p>
            Fondée le <strong>13 novembre 1940</strong>, l’Espérance Sportive du Moulon s’est forgée une identité unique dans le paysage sportif du Cher. 
            D’abord club de quartier, elle est devenue au fil des décennies un pilier du football régional, formant des générations de joueurs et 
            entretenant un lien indéfectible avec ses habitants.  
            <br><br>
            De la terre battue des débuts, au terrain synthétique du stade de la sente aux loups, l'ES Moulon a toujours porté haut les valeurs du sport : 
            engagement, solidarité et persévérance.
        </p>
        </div>

        <div class="history-photo">
        <img src="../../public/assets/img/histoire_et_valeurs.jpg" 
                alt="Ancien maillot de l'ES Moulon - 1940" 
                class="history-image">
        <p class="photo-caption">
            <em>Maillot original de 1940 exposé au siège du club<br>symbole de notre héritage sportif.</em>
        </p>
    </div>



</section>

<section class="values">
  <h2>Nos Valeurs</h2>
  <div class="values-grid">
    <div class="value-card">
      <div class="value-icon">💚</div>
      <h3>Passion & Engagement</h3>
      <p>Le football n’est pas qu’un sport, c’est un moteur de vie. Chaque joueur, dirigeant et bénévole partage la même passion du maillot vert et blanc.</p>
    </div>

    <div class="value-card">
      <div class="value-icon">🤝</div>
      <h3>Solidarité & Respect</h3>
      <p>Nous croyons en la force du collectif, dans le respect des autres, du jeu et des valeurs humaines qui font la grandeur du sport.</p>
    </div>

    <div class="value-card">
      <div class="value-icon">🌟</div>
      <h3>Formation & Ambition</h3>
      <p>Former, accompagner et inspirer les jeunes talents du Cher pour qu’ils puissent grandir humainement et sportivement sous nos couleurs.</p>
    </div>
  </div>
</section>

<section class="cta">
  <h2>Une aventure qui continue depuis 1940</h2>
  <p>Rejoignez ceux qui écrivent chaque jour la suite de notre histoire.</p>
  <div class="news-cta">
      <a class="btn-gradient" href="<?= url('Rejoignez_nous/nous_contactez') ?>" class="btn btn-gradient">Rejoignez-nous </a>
    </div>
</section>

</body>
</html>



