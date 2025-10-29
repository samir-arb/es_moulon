<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>

  <style>
    :root {
      --green: #009639;
      --dark-green: #016f29;
      --black: #111;
      --grey: #f5f5f5;
    }

    body {
      font-family: 'Poppins', 'Segoe UI', Roboto, sans-serif;
      background: var(--white);
      color: var(--black);
      text-align: center;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    /* HERO */
    .hero-pro {
      position: relative;
      width: 100%;
      height: 450px;
      overflow: hidden;
    }

    .hero-pro .hero-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      z-index: 0;
    }

    /*  dégradé sombre */
    .hero-pro .overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.85));
      z-index: 1;
    }

    /*  Contenu centré */
    .hero-pro .hero-content {
      position: relative;
      z-index: 2;
      color: #fff;
      text-align: center;
      top: 65%;
      transform: translateY(-50%);
      text-transform: uppercase;
      animation: fadeIn 1.2s ease-in-out;
    }

    /* Logo centré */
    .hero-pro .hero-content .hero-logo {
      width: 100px;
      border-radius: 50%;
      height: auto;
      margin-top: 30px;
      animation: fadeDown 1.2s ease-in-out;
    }

    .hero-content {
      font-size: 2rem;
    }

    /*  Animations */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(-50%);
      }
    }

    @keyframes fadeDown {
      from {
        opacity: 0;
        transform: translateY(-15px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .classement-section {
      padding: 50px 20px;
      
    }

    .classement-section h2 {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--dark-green);
      margin-bottom: 10px;
    }

    .classement-section p {
      font-size: 1.1rem;
      color: #444;
      margin-bottom: 40px;
    }

    .classement-img {
      max-width: 700px;
      width: 100%;
      height: auto;
      margin: 0 auto 30px;
      transition: transform 0.3s ease;
    }

    .classement-img:hover {
      transform: scale(1.05);
    }

    .btn-fff {
      display: inline-block;
      background: var(--green);
      color: white;
      padding: 14px 28px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1rem;
      transition: background 0.3s ease;
    }

    .btn-fff:hover {
      background: var(--dark-green);
    }

     /*  Responsive */
    @media (max-width: 768px) {
      .hero-pro {
        height: 320px;
      }

      .hero-pro .hero-content .hero-logo {
          width: 120px;
      }

      .hero-content {
        font-size: 1.5rem;
      }
    }

    @media (max-width: 590px){
        .hero-pro {
        height: 200px;
      }

      .hero-pro .hero-content .hero-logo {
          width: 80px;
      }

      .hero-content {
        font-size: 1.2rem;
      }
    }


  </style>


  <section class="hero-pro">
    <img src="<?= asset('uploads/loups-moulon.png') ?>" alt="Bannière ES Moulon" class="hero-bg">
    <div class="overlay"></div>
    <div class="hero-content">
      <h1>CLASSEMENT</h1>
      <p>Équipe Senior - Régional 1 - Saison 2025/2026</p>
    </div>
  </section>

  <section class="classement-section">
    <h2>Consultez le classement officiel</h2>
    <p>Retrouvez le classement complet sur le site de la <em>Fédération Française de Football</em> !</p>

    <a href=https://epreuves.fff.fr/competition/club/514340-espe-s-du-moulon-bourges/equipe/2025_4473_SEM_1/classement
      target="_blank" rel="noopener">
      <img src="<?= asset('uploads/img_ordi_classement.webp') ?>"
        alt="Classement FFF" class="classement-img">
      <p style="font-size:0.9rem;color:#555;margin-top:10px;">
        Source officielle : Fédération Française de Football (FFF)
      </p>
    </a>

  </section>