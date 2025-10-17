<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';

// Récupération des matchs de TOUTES les équipes du club
$matchs_query = $conn->query("
    SELECT m.*, 
           th.name AS home_team, th.id_club_team AS home_is_club,
           ta.name AS away_team, ta.id_club_team AS away_is_club
    FROM matches m
    LEFT JOIN teams th ON m.id_home_team = th.id_team
    LEFT JOIN teams ta ON m.id_away_team = ta.id_team
    WHERE th.id_club_team = 1 OR ta.id_club_team = 1
    ORDER BY m.match_date ASC
");

$matchs = $matchs_query ? $matchs_query->fetch_all(MYSQLI_ASSOC) : [];

// Séparation des matchs
$matchs_passes = [];
$matchs_a_venir = [];
$prochain_match = null;

foreach ($matchs as $m) {
    if (is_null($m['home_score'])) {
        $matchs_a_venir[] = $m;
        if ($prochain_match === null) {
            $prochain_match = $m;
        }
    } else {
        $matchs_passes[] = $m;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier ES Moulon - Équipe Senior R1</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        .calendar-page {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f5f5f5;
            color: #333;
            padding: 0;
            margin: 0;
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

        /* CONTAINER */
        .calendar-container {
            max-width: 1000px;
            margin: 20px auto 40px;
            padding: 0 20px;
        }

        /* PROCHAIN MATCH */
        .cal-next-match {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-radius: 20px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 8px 30px rgba(30, 64, 175, 0.25);
            border: none;
            position: relative;          
            overflow: hidden;            
            }

            /* Ballon animé */
            .cal-next-match::before {
            content: '⚽';
            position: absolute;
            font-size: 120px; 
            opacity: 0.1;
            top: 0;
            left: 0;
            animation: moveBall 12s ease-in-out infinite alternate,
                        spinBall 4s linear infinite;
            pointer-events: none;
            }

            /* Mouvement de rebond à l'intérieur du bloc */
            @keyframes moveBall {
                0% {
                    top: 5%;
                    left: 5%;
                }
                25% {
                    top: 80%;
                    left: 10%;
                }
                50% {
                    top: 10%;
                    left: 80%;
                }
                75% {
                    top: 85%;
                    left: 70%;
                }
                100% {
                    top: 5%;
                    left: 5%;
                }
            }

            /* Rotation continue du ballon */
            @keyframes spinBall {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(720deg);
                }
            }


        .cal-next-label {
            color: #60a5fa;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .cal-next-teams {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .cal-next-team {
            font-size: 1.6rem;
            font-weight: 800;
            color: white;
        }

        .cal-next-vs {
            font-size: 1.1rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.5);
        }

        .cal-next-info {
            display: flex;
            justify-content: center;
            gap: 35px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            flex-wrap: wrap;
            padding-top: 20px;
            border-top: 2px solid rgba(255, 255, 255, 0.1);
        }

        .cal-next-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        /* TABS */
        .cal-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: white;
            padding: 8px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .cal-tab {
            flex: 1;
            padding: 12px 20px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            color: #666;
        }

        .cal-tab.active {
            background: #009639;
            color: white;
        }

        /* MATCHS */
        .cal-matches-list {
            display: grid;
            gap: 15px;
        }

        .cal-match-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 20px;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .cal-match-card:hover {
            box-shadow: 0 4px 16px rgba(0, 150, 57, 0.15);
            border-color: #009639;
        }

        .cal-match-date {
            text-align: center;
            background: #f8f9fa;
            padding: 12px 8px;
            border-radius: 10px;
        }

        .cal-match-day {
            font-size: 1.8rem;
            font-weight: 800;
            color: #009639;
            line-height: 1;
        }

        .cal-match-month {
            font-size: 0.75rem;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .cal-match-time {
            font-size: 0.75rem;
            color: #999;
            margin-top: 5px;
        }

        .cal-match-info {
            flex: 1;
        }

        .cal-match-teams {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .cal-match-team {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }

        .cal-match-team.club {
            color: #009639;
            font-weight: 700;
        }

        .cal-match-vs {
            font-size: 0.85rem;
            color: #999;
            font-weight: 600;
        }

        .cal-match-location {
            font-size: 0.85rem;
            color: #666;
        }

        .cal-match-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
        }

        .cal-match-type {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .cal-match-type.championnat {
            background: #e3f2fd;
            color: #1976d2;
        }

        .cal-match-type.coupe {
            background: #ffebee;
            color: #c62828;
        }

        .cal-match-type.amical {
            background: #f5f5f5;
            color: #666;
        }

        /* BOUTON RÉSULTAT */
        .cal-result-btn {
            padding: 8px 16px;
            border: 2px solid #009639;
            background: white;
            color: #009639;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .cal-result-btn:hover {
            background: #009639;
            color: white;
        }

        .cal-result-score {
            display: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 700;
            text-align: center;
        }

        .cal-result-score.show {
            display: block;
        }

        .cal-result-score.victoire {
            background: #d4edda;
            color: #155724;
        }

        .cal-result-score.defaite {
            background: #f8d7da;
            color: #721c24;
        }

        .cal-result-score.nul {
            background: #fff3cd;
            color: #856404;
        }

        /* EMPTY STATE */
        .cal-empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            color: #999;
        }

        .cal-empty-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        /* RESPONSIVE */
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
            .calendar-hero h1 {
                font-size: 1.5rem;
            }

            .cal-next-match {
                padding: 20px;
            }

            .cal-next-teams {
                flex-direction: column;
                gap: 15px;
            }

            .cal-next-team {
                font-size: 1.2rem;
            }

            .cal-next-info {
                flex-direction: column;
                gap: 10px;
            }

            .cal-match-card {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .cal-match-date {
                width: fit-content;
                margin: 0 auto;
            }

            .cal-match-actions {
                align-items: center;
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

</head>

<body>
    <!-- HERO -->
    <section class="hero-pro">
        <img src="<?= asset('uploads/loups-moulon.png') ?>" alt="Bannière ES Moulon" class="hero-bg">
        <div class="overlay"></div>
        <div class="hero-content">
           
            <h1>CALENDRIER & RÈSULTATS</h1>
            <p>Équipe Senior - Régional 1 - Saison 2025/2026</p>            
        </div>
    </section>

    <div class="calendar-page">
        <div class="calendar-container">
            <!-- PROCHAIN MATCH -->
            <?php if ($prochain_match):
                $is_home = $prochain_match['home_is_club'] == 1;
                $team_home = $prochain_match['home_team'];
                $team_away = $prochain_match['away_team'];
                $dateObj = new DateTime($prochain_match['match_date']);
                $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE d MMMM yyyy');
            ?>
                <div class="cal-next-match">
                    <div class="cal-next-label">⚡ Prochain Match</div>
                    <div class="cal-next-teams">
                        <span class="cal-next-team">
                            <?= $is_home ? '🏠 ' : '' ?><?= htmlspecialchars($team_home) ?>
                        </span>
                        <span class="cal-next-vs">VS</span>
                        <span class="cal-next-team">
                            <?= !$is_home ? '✈️ ' : '' ?><?= htmlspecialchars($team_away) ?>
                        </span>
                    </div>
                    <div class="cal-next-info">
                        <div class="cal-next-info-item">
                            <span>📅</span>
                            <span><?= ucfirst($fmt->format($dateObj)) ?></span>
                        </div>
                        <div class="cal-next-info-item">
                            <span>🕐</span>
                            <span><?= $dateObj->format('H:i') ?></span>
                        </div>
                        <div class="cal-next-info-item">
                            <span>📍</span>
                            <span><?= htmlspecialchars($prochain_match['location']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABS -->
            <div class="cal-tabs">
                <button class="cal-tab active" onclick="showCalTab('a-venir')">
                    À venir (<?= count($matchs_a_venir) ?>)
                </button>
                <button class="cal-tab" onclick="showCalTab('passes')">
                    Passés (<?= count($matchs_passes) ?>)
                </button>
            </div>

            <!-- MATCHS À VENIR -->
            <div class="cal-matches-list" id="cal-tab-a-venir">
                <?php if (empty($matchs_a_venir)): ?>
                    <div class="cal-empty-state">
                        <div class="cal-empty-icon">📅</div>
                        <p>Aucun match à venir</p>
                    </div>
                <?php else: ?>
                    <?php
                    $first_match_shown = false;
                    foreach ($matchs_a_venir as $m):
                        // Sauter le premier match (déjà affiché en hero)
                        if (!$first_match_shown && $m === $prochain_match) {
                            $first_match_shown = true;
                            continue;
                        }

                        $is_home = $m['home_is_club'] == 1;
                        $dateObj = new DateTime($m['match_date']);
                        $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
                        $fmt->setPattern('MMM');
                    ?>
                        <div class="cal-match-card">
                            <div class="cal-match-date">
                                <div class="cal-match-day"><?= $dateObj->format('d') ?></div>
                                <div class="cal-match-month"><?= strtoupper($fmt->format($dateObj)) ?></div>
                                <div class="cal-match-time"><?= $dateObj->format('H:i') ?></div>
                            </div>
                            <div class="cal-match-info">
                                <div class="cal-match-teams">
                                    <span class="cal-match-team <?= $is_home ? 'club' : '' ?>">
                                        <?= $is_home ? '🏠 ' : '' ?><?= htmlspecialchars($m['home_team']) ?>
                                    </span>
                                    <span class="cal-match-vs">VS</span>
                                    <span class="cal-match-team <?= !$is_home ? 'club' : '' ?>">
                                        <?= !$is_home ? '✈️ ' : '' ?><?= htmlspecialchars($m['away_team']) ?>
                                    </span>
                                </div>
                                <div class="cal-match-location">
                                    📍 <?= htmlspecialchars($m['location']) ?>
                                </div>
                            </div>
                            <div class="cal-match-actions">
                                <span class="cal-match-type <?= $m['match_type'] ?>">
                                    <?= ucfirst($m['match_type']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- MATCHS PASSÉS -->
            <div class="cal-matches-list" id="cal-tab-passes" style="display:none;">
                <?php if (empty($matchs_passes)): ?>
                    <div class="cal-empty-state">
                        <div class="cal-empty-icon">⚽</div>
                        <p>Aucun match passé</p>
                    </div>
                <?php else: ?>
                    <?php
                    $matchs_passes_reversed = array_reverse($matchs_passes);
                    foreach ($matchs_passes_reversed as $index => $m):
                        $is_home = $m['home_is_club'] == 1;
                        $club_score = $is_home ? $m['home_score'] : $m['away_score'];
                        $opp_score = $is_home ? $m['away_score'] : $m['home_score'];
                        $dateObj = new DateTime($m['match_date']);
                        $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
                        $fmt->setPattern('MMM');

                        $scoreClass = '';
                        if ($club_score > $opp_score) $scoreClass = 'victoire';
                        elseif ($club_score < $opp_score) $scoreClass = 'defaite';
                        else $scoreClass = 'nul';
                    ?>
                        <div class="cal-match-card">
                            <div class="cal-match-date">
                                <div class="cal-match-day"><?= $dateObj->format('d') ?></div>
                                <div class="cal-match-month"><?= strtoupper($fmt->format($dateObj)) ?></div>
                                <div class="cal-match-time"><?= $dateObj->format('H:i') ?></div>
                            </div>
                            <div class="cal-match-info">
                                <div class="cal-match-teams">
                                    <span class="cal-match-team <?= $is_home ? 'club' : '' ?>">
                                        <?= $is_home ? '🏠 ' : '' ?><?= htmlspecialchars($m['home_team']) ?>
                                    </span>
                                    <span class="cal-match-vs">VS</span>
                                    <span class="cal-match-team <?= !$is_home ? 'club' : '' ?>">
                                        <?= !$is_home ? '✈️ ' : '' ?><?= htmlspecialchars($m['away_team']) ?>
                                    </span>
                                </div>
                                <div class="cal-match-location">
                                    📍 <?= htmlspecialchars($m['location']) ?>
                                </div>
                            </div>
                            <div class="cal-match-actions">
                                <span class="cal-match-type <?= $m['match_type'] ?>">
                                    <?= ucfirst($m['match_type']) ?>
                                </span>
                                <button class="cal-result-btn" onclick="toggleCalResult(<?= $index ?>)">
                                    📊 Résultat
                                </button>
                                <div id="cal-result-<?= $index ?>" class="cal-result-score <?= $scoreClass ?>">
                                    <?= $opp_score ?> - <?= $club_score ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showCalTab(tabName) {
            document.getElementById('cal-tab-a-venir').style.display = 'none';
            document.getElementById('cal-tab-passes').style.display = 'none';
            document.getElementById('cal-tab-' + tabName).style.display = 'grid';

            const tabs = document.querySelectorAll('.cal-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
        }

        function toggleCalResult(index) {
            const resultDiv = document.getElementById('cal-result-' + index);
            const btn = event.target;

            if (resultDiv.classList.contains('show')) {
                resultDiv.classList.remove('show');
                btn.textContent = '📊 Résultat';
            } else {
                resultDiv.classList.add('show');
                btn.textContent = '❌ Masquer';
            }
        }
    </script>

</body>

</html>