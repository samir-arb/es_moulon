<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';

// -------------------------------
// 1) Sélection de l'équipe cible
// -------------------------------
$team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;

// Si rien passé, on prend la 1re équipe trouvée (fallback simple)
if ($team_id === 0) {
    try {
        $team_id = (int)$pdo->query("SELECT id_team FROM teams ORDER BY id_team ASC LIMIT 1")->fetchColumn();
    } catch (\Throwable $e) {
        $team_id = 0;
    }
}
if ($team_id === 0) {
    die("Aucune équipe trouvée. Passe un paramètre ?team_id=xx ou crée une équipe dans la table teams.");
}

// (Optionnel) Récup infos de l’équipe
$team = [
    'name'  => 'Équipe',
    'level' => '',
];
try {
    $stmt = $pdo->prepare("SELECT name, level FROM teams WHERE id_team = :id");
    $stmt->execute([':id' => $team_id]);
    if ($row = $stmt->fetch()) {
        $team['name']  = $row['name'];
        $team['level'] = $row['level'];
    }
} catch (\Throwable $e) { /* no-op */
}

// ---------------------------------------
// 2) STAFF TECHNIQUE (sportif ≠ Joueur)
// ---------------------------------------
$staff = [];
try {
    $sql = "
        SELECT u.id_user,
               u.first_name,
               u.name          AS last_name,
               cf.function_name,
               m.file_path     AS photo
        FROM users_club_functions ucf
        JOIN users u               ON u.id_user = ucf.id_user
        JOIN club_functions cf     ON cf.id_club_function = ucf.id_club_function
        LEFT JOIN medias m         ON m.id_media = u.id_media
        WHERE ucf.id_team = :team
          AND cf.function_type = 'sportif'
          AND cf.function_name <> 'Joueur'
        ORDER BY 
          FIELD(cf.function_name,'Responsable sportif','Entraîneur','Entraîneur adjoint','Préparateur gardiens','Préparateur physique','Arbitre'),
          u.name, u.first_name
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':team' => $team_id]);
    $staff = $stmt->fetchAll();
} catch (\Throwable $e) {
    $staff = [];
}

// ---------------------------------------
// 3) JOUEURS par ligne (position)
// ---------------------------------------
$players_by_line = [
    'Gardien'    => [],
    'Défenseur'  => [],
    'Milieu'     => [],
    'Attaquant'  => [],

];

try {
    $sql = "
        SELECT u.id_user,
               u.first_name,
               u.name         AS last_name,
               ucf.position   AS position,
               m.file_path    AS photo
        FROM users_club_functions ucf
        JOIN users u           ON u.id_user = ucf.id_user
        LEFT JOIN medias m     ON m.id_media = u.id_media
        WHERE ucf.id_team = :team
          AND ucf.id_club_function = 1     -- 1 = Joueur
        ORDER BY 
          CASE ucf.position
              WHEN 'Gardien' THEN 1
              WHEN 'Défenseur' THEN 2
              WHEN 'Milieu' THEN 3
              WHEN 'Attaquant' THEN 4
              ELSE 5
          END, u.name, u.first_name
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':team' => $team_id]);
    while ($row = $stmt->fetch()) {
        $pos = $row['position'] ?: 'Autres';
        if (!isset($players_by_line[$pos])) $pos = 'Autres';
        $players_by_line[$pos][] = $row;
    }
} catch (\Throwable $e) {
    // laisser les arrays vides
}

// Image de fond (logo club) — ajuste le chemin si besoin
$bg_logo = asset('uploads/sc_esmoulon.png'); // par ex. /public/assets/img/logo_moulon.png
?>

<style>
    :root {
        --green: #009639;
        --dark-green: #016f29;
        --white: #fff;
        --grey: #f5f5f5;
        --black: #111;
        --shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        --transition: all 0.3s ease-in-out;
    }

    body {
        margin: 0;
        font-family: 'Poppins', 'Segoe UI', Roboto, sans-serif;
        background-color: var(--white);
        color: var(--black);
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

    /* WRAP */
    .wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 20px 100px;
    }

    /* SECTION TITRES */
    .section h2 {
        text-align: center;
        text-transform: uppercase;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-green);
        margin: 50px auto 40px;
        position: relative;
        width: max-content;
    }

    .section h2::after {
        content: "";
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 60%;
        height: 3px;
        background: var(--green);
        border-radius: 4px;
    }

    /* GRILLE */
    .grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
        padding: 0 20px;
    }

    /* CARTES */
    .card {
        width: 220px;
        height: 370px;
        background: var(--white);
        border-radius: 14px;
        box-shadow: var(--shadow);
        text-align: center;
        overflow: hidden;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
    }

    /* IMAGE */
    .card .top {
        flex: 0 0 250px;
        overflow: hidden;
        background: var(--grey);
    }

    .card .top img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .card:hover .top img {
        transform: scale(1.05);
    }

    /* TEXTES */
    .card .name {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--black);
        margin-top: 10px;
        text-transform: uppercase;
    }

    .card .role,
    .card .pos {
        color: var(--green);
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 14px;
    }


    /* RESPONSIVE */
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

        .grid {
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 20px;
        }

        .card {
            width: 180px;
            height: 330px;
        }

        .card .top {
            flex: 0 0 200px;
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
            font-size: 1rem;
        }
    }

</style>


    <!-- HERO -->
    <section class="hero-pro">
        <img src="<?= asset('uploads/loups-moulon.png') ?>" alt="Bannière ES Moulon" class="hero-bg">
        <div class="overlay"></div>
        <div class="hero-content">
           
            <h1>ÉFFECTIF & STAFF TECHNIQUE</h1>
            <p>Équipe Senior - Régional 1 - Saison 2025/2026</p>
            
        </div>
    </section>


    <!-- STAFF TECHNIQUE -->
    <section class="section">
        <h2>STAFF TECHNIQUE</h2>
        <?php if (empty($staff)): ?>
            <p class="muted" style="text-align:center;">Aucun membre de staff enregistré pour cette équipe.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($staff as $m):
                    $photo = !empty($m['photo']) ? asset($m['photo']) : asset('img/avatar.png');
                ?>
                    <article class="card">
                        <div class="top"><img src="<?= htmlspecialchars($photo) ?>" alt=""></div>
                        <div class="name"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></div>
                        <div class="role"><?= htmlspecialchars($m['function_name']) ?></div>
                        <div class="bar"></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- JOUEURS PAR LIGNES -->
    <?php
    $lines_order = ['Gardiens' => 'Gardien', 'Défenseurs' => 'Défenseur', 'Milieux' => 'Milieu', 'Attaquants' => 'Attaquant'];
    foreach ($lines_order as $label => $key):
        $list = $players_by_line[$key];
    ?>
        <section class="section">
            <h2><?= $label ?></h2>
            <?php if (empty($list)): ?>
                <p class="muted" style="text-align:center;">Aucun joueur dans cette ligne.</p>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($list as $p):
                        $photo = !empty($m['photo']) ? asset($m['photo']) : asset('uploads/logo_moulon.jpg');
                    ?>
                        <article class="card">
                            <div class="top"><img src="<?= htmlspecialchars($photo) ?>" alt=""></div>
                            <div class="name"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></div>
                            <div class="pos"><?= htmlspecialchars($p['position'] ?: '—') ?></div>
                            <div class="bar"></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    </div>
