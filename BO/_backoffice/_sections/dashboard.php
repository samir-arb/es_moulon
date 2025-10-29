
<?php

// ====================================================================
// _backoffice/_sections/dashboard.php
//
// Ce fichier gère uniquement l'affichage du dashboard (la vue).
// Les données ($stats, $activities, $statsAvancees) sont préparées
// en amont par _backoffice/_core/dashboard_data.php, inclus via admin.php.
// Cela respecte une logique inspirée du MVC :
// - dashboard_data.php = "modèle" (requêtes SQL, préparation des données)
// - dashboard.php = "vue" (affichage HTML/CSS)
// ====================================================================

// ====================================================================
// _backoffice/_sections/dashboard.php
// Vue du tableau de bord : affichage des statistiques et activités
// ====================================================================

// Les variables suivantes sont déjà définies par dashboard_data.php :
// - $user_role
// - $user_prenom
// - $stats
// - $activities
// - $statsAvancees
?>

    <style>

        .content-area .dashboard-welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .dashboard-welcome h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }

        .dashboard-welcome p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .stat-card > span {
            font-size: 42px;
            filter: grayscale(0.2);
            flex-shrink: 0;
        }

        .stat-card > div {
            flex: 1;
            font-size: 16px;
            color: #555;
        }

        .stat-card strong {
            font-size: 32px;
            color: #2c3e50;
            display: block;
            margin-top: 5px;
        }

        /* Badge de notification */
        .notification-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            animation: pulse 2s infinite;
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Section statistiques avancées */
        .advanced-stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .stats-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .stats-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }

        .month-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .month-stat:hover {
            background: #ecf0f1;
            transform: translateX(5px);
        }

        .month-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 15px;
        }

        .month-value {
            font-size: 24px;
            font-weight: 700;
            color: #3498db;
        }

        .month-evolution {
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 12px;
            margin-left: 10px;
        }

        .evolution-positive {
            background: #d4edda;
            color: #155724;
        }

        .evolution-negative {
            background: #f8d7da;
            color: #721c24;
        }

        .evolution-neutral {
            background: #e7f3ff;
            color: #004085;
        }

        /* Barre de progression */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        /* Top pages */
        .top-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .top-item:hover {
            background: #ecf0f1;
        }

        .top-rank {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .top-info {
            flex: 1;
            margin-left: 15px;
        }

        .top-page-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .top-visits {
            font-size: 18px;
            font-weight: 700;
            color: #3498db;
        }

        /* Statistiques rapides */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .quick-stat-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .quick-stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .quick-stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .activity-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .activity-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 22px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .activity-card p {
            padding: 18px;
            margin-bottom: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        .activity-card p:hover {
            background: #ecf0f1;
            transform: translateX(5px);
        }

        .activity-card p:last-child {
            margin-bottom: 0;
        }

        .activity-card p[data-type="message"] {
            border-left-color: #e74c3c;
        }

        .activity-card p[data-type="candidature"] {
            border-left-color: #f39c12;
        }

        .activity-card p[data-type="article"] {
            border-left-color: #3498db;
        }

        .activity-card p[data-type="equipe"] {
            border-left-color: #2ecc71;
        }

        .activity-card p[data-type="photo"] {
            border-left-color: #9b59b6;
        }

        .activity-card p[data-type="match"] {
            border-left-color: #f97316;
        }

        .activity-card strong {
            color: #2c3e50;
        }

        .activity-card em {
            color: #7f8c8d;
            font-size: 14px;
            display: block;
            margin-top: 5px;
        }

        .no-activity {
            text-align: center;
            color: #7f8c8d;
            padding: 60px 20px;
            font-size: 16px;
        }

        .no-activity-icon {
            font-size: 64px;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .advanced-stats-section {
                grid-template-columns: 1fr;
            }
            
            .dashboard-welcome h2 {
                font-size: 22px;
            }
            
            .stat-card strong {
                font-size: 28px;
            }
        }
    </style>

    <!-- =======================
        ACCUEIL DASHBOARD
    =========================== -->
    <div class="dashboard-welcome">
        <h2>👋 Bienvenue, <?= htmlspecialchars($user_prenom ?? ''); ?> !</h2>
        <p>Voici un aperçu de l’activité du site ES Moulon</p>

        <?php if ($user_role === 'ROLE_ADMIN'): ?>
            <a href="?section=dashboard&refresh=1" 
                id="refreshBtn"
                style="display:inline-block;background:#3498db;color:white;
                padding:10px 18px;border-radius:8px;text-decoration:none;
                font-size:15px;transition:0.3s;border:none;cursor:pointer;"
                onclick="this.innerHTML='⏳ Rafraîchissement...'; this.style.opacity='0.6'; this.style.pointerEvents='none';">
                🔄 Rafraîchir les statistiques
            </a>
        <?php endif; ?>

    </div>

    <!-- =======================
        STATISTIQUES RAPIDES
    =========================== -->
    <div class="stats-grid">
        <?php if (!empty($stats)): ?>
            <?php foreach ($stats as $stat): ?>
                <div class="stat-card" style="border-left:4px solid <?= $stat['color']; ?>">
                    <span><?= $stat['icon']; ?></span>
                    <div>
                        <?= htmlspecialchars($stat['label']); ?>
                        <strong><?= htmlspecialchars($stat['value']); ?></strong>
                    </div>
                    <?php if (!empty($stat['notification'])): ?>
                        <div class="notification-badge">NOUVEAU</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; color:#777;">Aucune statistique disponible pour le moment.</p>
        <?php endif; ?>
    </div>

    <!-- =======================
        STATISTIQUES AVANCÉES
    =========================== -->
    <?php if ($user_role === 'ROLE_ADMIN' && !empty($statsAvancees)): ?>
    <div class="advanced-stats-section">

        <!-- Évolution des visites -->
        <div class="stats-card">
            <h3>📈 Évolution des visites (6 derniers mois)</h3>
            <?php if (!empty($statsAvancees['visites_par_mois'])): ?>
                <?php foreach ($statsAvancees['visites_par_mois'] as $mois): ?>
                    <div class="month-stat">
                        <div style="flex:1;">
                            <div><strong><?= htmlspecialchars($mois['mois_nom']); ?></strong></div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $mois['pourcentage']; ?>%;"></div>
                            </div>
                        </div>
                        <span><strong><?= $mois['total']; ?></strong></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune donnée sur 6 mois.</p>
            <?php endif; ?>
        </div>

        <!-- Pages les plus visitées -->
        <div class="stats-card">
            <h3>🏆 Pages les plus visitées</h3>
            <?php if (!empty($statsAvancees['top_pages'])): ?>
                <?php $rank = 1; foreach ($statsAvancees['top_pages'] as $page): ?>
                    <div class="month-stat">
                        <span>#<?= $rank++; ?> <?= htmlspecialchars($page['page']); ?></span>
                        <span><strong><?= $page['total']; ?></strong> visites</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune donnée disponible.</p>
            <?php endif; ?>
        </div>

        <!-- Statistiques rapides -->
        <div class="stats-card">
            <h3>⚡ Statistiques rapides</h3>
            <p><strong>Visites / jour :</strong> <?= $statsAvancees['moyenne_jour'] ?? 0; ?></p>
            <p><strong>Visites cette semaine :</strong> <?= $statsAvancees['total_semaine'] ?? 0; ?></p>
            <p><strong>Jour le plus visité :</strong> <?= $statsAvancees['meilleur_jour']['nom'] ?? 'N/A'; ?> (<?= $statsAvancees['meilleur_jour']['total'] ?? 0; ?> visites)</p>
            <p><strong>Pages vues / visite :</strong> <?= $statsAvancees['navigation_moyenne'] ?? 0; ?></p>
        </div>

        <!-- Navigateurs utilisés -->
        <div class="stats-card">
            <h3>🌐 Navigateurs utilisés</h3>
            <?php if (!empty($statsAvancees['navigateurs'])): ?>
                <?php foreach ($statsAvancees['navigateurs'] as $nav): ?>
                    <div class="month-stat">
                        <span><?= htmlspecialchars($nav['browser']); ?></span>
                        <div class="progress-bar" style="flex:1; margin:0 15px;">
                            <div class="progress-fill" style="width: <?= $nav['pourcentage']; ?>%; background:linear-gradient(90deg,#667eea,#764ba2);"></div>
                        </div>
                        <span><?= $nav['pourcentage']; ?>%</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune donnée disponible.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- =======================
        ACTIVITÉ RÉCENTE
    =========================== -->
    <div class="activity-card">
        <h3>📌 Activité récente</h3>
        <?php if (!empty($activities)): ?>
            <?php foreach ($activities as $activity): ?>
                <p data-type="<?= $activity['type'] ?? 'default'; ?>">
                    <strong><?= htmlspecialchars($activity['action']); ?></strong> —
                    <?= htmlspecialchars($activity['item']); ?>
                    <em>⏱️ <?= htmlspecialchars($activity['time']); ?></em>
                </p>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; color:#888;">Aucune activité récente.</p>
        <?php endif; ?>
    </div>
</body>
</html

