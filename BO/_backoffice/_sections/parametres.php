<?php
require_once __DIR__ . '/../../../includes/config.php';

// Protection de la page - ADMIN UNIQUEMENT
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'ROLE_ADMIN') {
    $_SESSION['flash']['danger'] = "Accès réservé aux administrateurs.";
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="<?= asset('_back.css/parametres.css') ?>">
    <title>Paramètres - ES Moulon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #1f2937;
            font-size: 1.5rem;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        .empty-state {
            color: #6b7280;
        }
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .empty-state h2 {
            color: #1f2937;
            margin-bottom: 12px;
        }
        .empty-state p {
            color: #6b7280;
            line-height: 1.6;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
            text-align: left;
        }
        .info-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #1e40af;
        }
        .info-card h3 {
            color: #1f2937;
            margin-bottom: 12px;
            font-size: 1.1rem;
        }
        .info-card ul {
            list-style: none;
            padding: 0;
        }
        .info-card li {
            padding: 8px 0;
            color: #6b7280;
            font-size: 0.9rem;
        }
        .info-card li:before {
            content: "✓ ";
            color: #10b981;
            font-weight: bold;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>⚙️ Paramètres</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
        </div>

        <div class="card">
            <div class="empty-state">
                <div class="empty-icon">⚙️</div>
                <h2>Paramètres du site</h2>
                <p>
                    Cette section permettra de configurer les paramètres généraux du site :<br>
                    informations du club, logos, couleurs, emails de contact, etc.
                </p>

                <div class="info-grid">
                    <div class="info-card">
                        <h3>🏢 Informations du club</h3>
                        <ul>
                            <li>Nom du club</li>
                            <li>Adresse du stade</li>
                            <li>Téléphone</li>
                            <li>Email de contact</li>
                            <li>Réseaux sociaux</li>
                        </ul>
                    </div>

                    <div class="info-card">
                        <h3>🎨 Personnalisation</h3>
                        <ul>
                            <li>Logo du club</li>
                            <li>Couleurs du thème</li>
                            <li>Images de fond</li>
                            <li>Favicon</li>
                        </ul>
                    </div>

                    <div class="info-card">
                        <h3>📧 Notifications</h3>
                        <ul>
                            <li>Emails automatiques</li>
                            <li>Alertes convocations</li>
                            <li>Newsletter</li>
                            <li>Rappels matchs</li>
                        </ul>
                    </div>
                </div>

                <p style="margin-top: 30px; font-style: italic;">
                    🚧 Fonctionnalité à développer selon vos besoins.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
