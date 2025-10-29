<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';

// Récupération des infos du club
$stmt = $pdo->query("SELECT * FROM club_info LIMIT 1");
$club = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération de l'image du stade
$stadium_image = null;
if ($club && $club['id_media']) {
    $stmt_img = $pdo->prepare("SELECT file_path FROM medias WHERE id_media = ?");
    $stmt_img->execute([$club['id_media']]);
    $stadium_image = $stmt_img->fetchColumn();
}

// ✅ CORRECTION : Récupérer SEULEMENT les documents qui ont un fichier attaché
$stmt_docs = $pdo->query("
    SELECT cm.*, m.file_name, m.file_path, m.file_type
    FROM categories_medias cm
    INNER JOIN medias m ON cm.media_id = m.id_media
    WHERE cm.is_active = 1 AND m.file_path IS NOT NULL
    ORDER BY cm.display_order ASC
");
$documents = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

// Jours de la semaine
$days = [
    'monday' => 'Lundi',
    'tuesday' => 'Mardi',
    'wednesday' => 'Mercredi',
    'thursday' => 'Jeudi',
    'friday' => 'Vendredi',
    'saturday' => 'Samedi',
    'sunday' => 'Dimanche',
];
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

    .hero-history h1,
    .hero-history p {
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

    .section-infos {
        background: #f7f9f8;
        padding: 80px 20px;
        color: #333;
        font-family: "Roboto", sans-serif;
    }

    .bloc {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
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

    .bloc-text p,
    .bloc-text li {
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
        max-height: 350px;
        object-fit: cover;
    }

    /* ✅ CORRECTION : Image documents plus petite et mieux cadrée */
    .bloc-image.docs-image {
        flex: 0 0 400px;
        max-height: 300px;
    }

    .bloc-image.docs-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 20px;
        background: #f9fafb;
    }

    /* --- HORAIRES AMÉLIORÉS --- */
    ul.hours {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 12px;
    }

    ul.hours li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        background: linear-gradient(135deg, #f0f9f4 0%, #ffffff 100%);
        border-left: 4px solid #1c995a;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    ul.hours li:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(28, 153, 90, 0.15);
    }

    ul.hours li strong {
        color: #1f2937;
        font-weight: 700;
        font-size: 15px;
    }

    ul.hours li .time {
        color: #1c995a;
        font-weight: 600;
        font-size: 15px;
        font-family: 'Courier New', monospace;
    }

    ul.hours li .closed {
        color: #ef4444;
        font-weight: 600;
        font-style: italic;
    }

    /* --- DOCUMENTS --- */
    ul.docs {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    ul.docs li {
        margin-bottom: 16px;
        padding-left: 0;
    }

    ul.docs a {
        color: #1c995a;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        font-size: 1.05rem;
    }

    ul.docs a:hover {
        color: #14824b;
        transform: translateX(5px);
    }

    ul.docs li small {
        display: block;
        color: #666;
        margin-top: 4px;
        margin-left: 32px;
        font-size: 0.9rem;
    }

    /* ✅ CORRECTION : Boutons alignés et cohérents */
    .btn-group {
        display: flex;
        gap: 15px;
        margin-top: 25px;
        flex-wrap: wrap;
    }

    .btn-main,
    .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 28px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .btn-main {
        background: linear-gradient(135deg, #1c995a 0%, #14824b 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(28, 153, 90, 0.3);
    }

    .btn-main:hover {
        background: linear-gradient(135deg, #14824b 0%, #0f6638 100%);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(28, 153, 90, 0.4);
    }

    .btn-secondary {
        background: transparent;
        border: 2px solid #1c995a;
        color: #1c995a;
    }

    .btn-secondary:hover {
        background: #1c995a;
        color: #fff;
        transform: translateY(-3px);
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 900px) {
        .bloc-content {
            flex-direction: column;
        }

        .bloc-text {
            padding: 30px 20px;
        }

        .bloc-image.docs-image {
            flex: 1 1 100%;
            max-height: 250px;
        }

        .btn-group {
            flex-direction: column;
        }

        .btn-main,
        .btn-secondary {
            width: 100%;
        }
    }

    /* Message si aucun document */
    .no-docs-message {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 16px 20px;
        border-radius: 8px;
        color: #92400e;
        font-weight: 600;
    }
</style>

<!-- HERO -->
<section class="hero-history">
    <h1>Informations Pratiques</h1>
    <p>Trouvez ici toutes les informations utiles pour venir, contacter le club ou télécharger les documents nécessaires.</p>
</section>

<section class="section-infos">
    <div class="container">

        <!-- Adresse et contact -->
        <div class="bloc">
            <div class="bloc-content">
                <div class="bloc-text">
                    <h2>📍 Le Stade & le Club</h2>
                    <?php if ($club): ?>
                        <p><strong>Stade :</strong> <?= htmlspecialchars($club['stadium_name'] ?? 'Stade Jacques Loubier') ?></p>
                        <p><strong>Adresse :</strong> <?= htmlspecialchars($club['address'] ?? 'Rue de la Sente aux Loups, 18000 Bourges') ?></p>
                    <?php else: ?>
                        <p><strong>Stade :</strong> Stade Jacques Loubier</p>
                        <p><strong>Adresse :</strong> Rue de la Sente aux Loups, 18000 Bourges</p>
                    <?php endif; ?>
                    <p><strong>Téléphone :</strong> 06 12 34 56 78</p>
                    <p><strong>Email :</strong> contact@esmoulon.fr</p>
                    <p><strong>Secrétariat :</strong> Voir horaires ci-dessous</p>
                    
                    <div class="btn-group">
                        <a href="<?= url('Rejoignez_nous/nous_contactez') ?>" class="btn-main">
                            Rejoignez-nous
                        </a>
                        <?php if ($club && !empty($club['google_maps_url'])): ?>
                            <a href="<?= htmlspecialchars($club['google_maps_url']) ?>" target="_blank" class="btn-secondary">
                                📍 Voir sur Google Maps
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bloc-image">
                    <?php if ($stadium_image): ?>
                        <img src="<?= asset($stadium_image) ?>" alt="Stade <?= htmlspecialchars($club['stadium_name'] ?? '') ?>">
                    <?php else: ?>
                        <img src="<?= asset('uploads/1200x680_sc_esmoulon.jpg') ?>" alt="Stade de la Sente aux Loups">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Horaires -->
        <div class="bloc alt">
            <div class="bloc-content reverse">
                <div class="bloc-image small">
                    <img src="<?= asset('img/sc_esmoulon.jpg') ?>" alt="Horaires du club">
                </div>
                <div class="bloc-text">
                    <h2>🕐 Horaires d'ouverture</h2>
                    <?php if ($club): ?>
                        <ul class="hours">
                            <?php foreach ($days as $key => $label):
                                $open = $club[$key . '_open'];
                                $close = $club[$key . '_close'];
                            ?>
                                <li>
                                    <strong><?= $label ?></strong>
                                    <?php if ($open && $close): ?>
                                        <span class="time"><?= date('H\hi', strtotime($open)) ?> – <?= date('H\hi', strtotime($close)) ?></span>
                                    <?php else: ?>
                                        <span class="closed">Fermé</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <ul class="hours">
                            <li><strong>Lundi</strong> <span class="time">17h00 – 19h00</span></li>
                            <li><strong>Mardi</strong> <span class="time">17h00 – 19h00</span></li>
                            <li><strong>Mercredi</strong> <span class="time">17h00 – 19h00</span></li>
                            <li><strong>Jeudi</strong> <span class="time">17h00 – 19h00</span></li>
                            <li><strong>Vendredi</strong> <span class="time">17h00 – 19h00</span></li>
                            <li><strong>Samedi</strong> <span class="closed">Fermé</span></li>
                            <li><strong>Dimanche</strong> <span class="closed">Fermé</span></li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="bloc">
            <div class="bloc-content">
                <div class="bloc-text">
                    <h2>📄 Documents utiles</h2>
                    <?php if (count($documents) > 0): ?>
                        <p>Téléchargez les documents importants du club :</p>
                        <ul class="docs">
                            <?php foreach ($documents as $doc): ?>
                                <li>
                                    <a href="<?= asset($doc['file_path']) ?>" target="_blank" download>
                                        <?php
                                        // Icône selon le type de fichier
                                        if ($doc['file_type'] === 'application/pdf') {
                                            echo '📄';
                                        } elseif (strpos($doc['file_type'], 'word') !== false) {
                                            echo '📝';
                                        } elseif (strpos($doc['file_type'], 'excel') !== false || strpos($doc['file_type'], 'spreadsheet') !== false) {
                                            echo '📊';
                                        } elseif (strpos($doc['file_type'], 'image') !== false) {
                                            echo '🖼️';
                                        } else {
                                            echo '📎';
                                        }
                                        ?>
                                        <?= htmlspecialchars($doc['name']) ?>
                                    </a>
                                    <?php if ($doc['description']): ?>
                                        <small><?= htmlspecialchars($doc['description']) ?></small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="no-docs-message">
                            ⚠️ Aucun document disponible pour le moment. Les documents seront bientôt ajoutés.
                        </div>
                    <?php endif; ?>
                </div>
                
                
                <div class="bloc-image docs-image">
                    <img src="<?= asset('img/doc_telecharger.jpg') ?>" alt="Documents du club">
                </div>
            </div>
        </div>
    </div>
</section>