<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Classement - ES Moulon</title>
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  
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
    background: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.85));
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

    .hero-content{
        font-size: 2rem;
    }

      /*  Animations */
    @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(-50%); }
    }

    @keyframes fadeDown {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
    }

    /*  Responsive */
    @media (max-width: 768px) {
      .hero-pro {
          height: 320px;
      }
      .hero-pro .hero-content .hero-logo {
          width: 80px;
      }
      .hero-pro h1 {
          font-size: 1.5rem;
      }
    }

    .classement-section {
      padding: 80px 20px;
      background: var(--grey);
    }

    .classement-section h1 {
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
      max-width: 600px;
      width: 100%;
      height: auto;
      margin: 0 auto 30px;
      border-radius: 10px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
      transition: transform 0.3s ease;
    }

    .classement-img:hover {
      transform: scale(1.02);
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
  </style>

</head>
<body>

  <section class="hero-pro">
    <img src="<?= asset('uploads/loups-moulon.png') ?>" alt="Bannière ES Moulon" class="hero-bg">
    <div class="overlay"></div>
    <div class="hero-content">
      <h1>CLASSEMENT</h1>
      <p>Équipe Senior - Régional 1 - Saison 2025/2026</p>
    </div>
  </section>

  <section class="classement-section">
    <h1>Consultez le classement officiel</h1>
    <p>Retrouvez le classement complet sur le site de la <em>Fédération Française de Football</em> !</p>

    <a href=https://epreuves.fff.fr/competition/club/514340-espe-s-du-moulon-bourges/equipe/2025_4473_SEM_1/classement
       target="_blank" rel="noopener">
      <img src="<?= asset('uploads/fff-classement.png') ?>" 
           alt="Classement FFF" class="classement-img">
    </a>

    <br>
    <a href="https://epreuves.fff.fr/competition/club/514340-espe-s-du-moulon-bourges/equipe/2025_4473_SEM_1/classement
       target="_blank" rel="noopener"
       class="btn-fff">
       🔗 Voir le classement sur FFF.fr

       <p style="font-size:0.9rem;color:#555;margin-top:10px;">
        Source officielle : Fédération Française de Football (FFF)
       </p>
    </a>
  </section>
