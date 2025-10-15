<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';
?>

<section class="section-infos">
    <div class="container">
        <header class="section-header">
            <h1>Informations Pratiques</h1>
            <p>Trouvez ici toutes les informations utiles pour venir, contacter le club ou télécharger les documents nécessaires.</p>
        </header>

        <!-- Adresse et contact -->
        <div class="bloc">
            <div class="bloc-content">
                <div class="bloc-text">
                    <h2>📍 Le Stade & le Club</h2>
                    <p><strong>Stade :</strong> Stade de la Sente aux Loups</p>
                    <p><strong>Adresse :</strong> Rue de la Sente aux Loups, 18000 Bourges</p>
                    <p><strong>Téléphone :</strong> 06 12 34 56 78</p>
                    <p><strong>Email :</strong> contact@esmoulon.fr</p>
                    <p><strong>Secrétariat :</strong> Lundi à Vendredi – 17h à 19h</p>
                    <a href="mailto:contact@esmoulon.fr" class="btn-main">Nous contacter</a>
                    <a href="https://www.google.com/maps/dir//Rue+de+la+Sente+aux+Loups,+18000+Bourges/"
                       target="_blank" class="btn-secondary">Voir sur Google Maps</a>
                </div>
                <div class="bloc-image">
                    <img src="<?= asset('uploads/1200x680_sc_esmoulon.jpg') ?>" alt="Stade de la Sente aux Loups">
                </div>
            </div>
        </div>

        <!-- Horaires -->
        <div class="bloc alt">
            <div class="bloc-content reverse">
                <div class="bloc-image small">
                    <img src="<?= asset('img/horaires.jpg') ?>" alt="Horaires du club">
                </div>
                <div class="bloc-text">
                    <h2>🕐 Horaires d’ouverture</h2>
                    <ul class="hours">
                        <li><strong>Lundi – Vendredi :</strong> 17h00 à 19h00</li>
                        <li><strong>Samedi :</strong> 9h00 à 12h00</li>
                        <li><strong>Dimanche :</strong> Fermé</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="bloc">
            <div class="bloc-content">
                <div class="bloc-text">
                    <h2>📄 Documents utiles</h2>
                    <p>Téléchargez les formulaires et documents importants du club :</p>
                    <ul class="docs">
                        <li><a href="#">📘 Règlement intérieur</a></li>
                        <li><a href="#">📝 Fiche d’inscription 2024-2025</a></li>
                        <li><a href="#">🎯 Charte de bonne conduite</a></li>
                        <li><a href="#">📄 Demande de licence</a></li>
                    </ul>
                </div>
                <div class="bloc-image">
                    <img src="<?= asset('img/documents.jpg') ?>" alt="Documents du club">
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* --- HARMONISATION AVEC HISTOIRE & VALEURS --- */
.section-infos {
    background: #f7f9f8;
    padding: 80px 20px;
    color: #333;
    font-family: "Roboto", sans-serif;
}
.section-header {
    text-align: center;
    max-width: 800px;
    margin: 0 auto 60px;
}
.section-header h1 {
    font-size: 2.6rem;
    color: #1c995a;
    font-weight: 900;
    text-transform: uppercase;
    margin-bottom: 15px;
}
.section-header p {
    font-size: 1.1rem;
    color: #555;
}

/* --- BLOCS --- */
.bloc {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    margin-bottom: 60px;
    overflow: hidden;
}
.bloc.alt {
    background: #f0f8f3;
}
.bloc-content {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}
.bloc-content.reverse {
    flex-direction: row-reverse;
}
.bloc-text {
    flex: 1 1 500px;
    padding: 50px;
}
.bloc-text h2 {
    color: #1c995a;
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 20px;
}
.bloc-text p, .bloc-text li {
    font-size: 1rem;
    line-height: 1.7;
    color: #444;
}
.bloc-image {
    flex: 1 1 500px;
    overflow: hidden;
}
.bloc-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.bloc-image.small img {
    max-height: 280px;
    object-fit: contain;
}

/* --- LISTES --- */
ul.hours, ul.docs {
    list-style: none;
    padding: 0;
    margin: 0;
}
ul.hours li {
    padding: 5px 0;
}
ul.docs li {
    margin-bottom: 8px;
}
ul.docs a {
    color: #1c995a;
    text-decoration: none;
    font-weight: 600;
}
ul.docs a:hover {
    text-decoration: underline;
}

/* --- BOUTONS --- */
.btn-main, .btn-secondary {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    margin-top: 15px;
    margin-right: 10px;
    transition: all 0.3s ease;
}
.btn-main {
    background: #1c995a;
    color: #fff;
}
.btn-main:hover {
    background: #14824b;
    transform: translateY(-3px);
}
.btn-secondary {
    background: transparent;
    border: 2px solid #1c995a;
    color: #1c995a;
}
.btn-secondary:hover {
    background: #1c995a;
    color: #fff;
}

/* --- RESPONSIVE --- */
@media (max-width: 900px) {
    .bloc-content {
        flex-direction: column;
    }
    .bloc-text {
        padding: 30px 20px;
    }
}
</style>
