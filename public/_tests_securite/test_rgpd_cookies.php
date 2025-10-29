<?php
/**
 * 🍪 TEST RGPD - Consentement des Cookies
 * Ce fichier permet de tester la conformité RGPD du système de tracking
 */

// ========================================
// TRAITER LES ACTIONS AVANT TOUT AFFICHAGE
// ========================================
if (isset($_GET['action'])) {
    session_start(); // Démarrer la session uniquement si nécessaire
    
    switch ($_GET['action']) {
        case 'clear':
            // Supprimer tous les cookies
            setcookie('cookie_consent', '', time() - 3600, '/');
            setcookie('tracking_consent', '', time() - 3600, '/');
            header("Location: test_rgpd_cookies.php?msg=cleared");
            exit;
            
        case 'accept':
            // Simuler l'acceptation
            setcookie('cookie_consent', 'accepted', time() + (365 * 24 * 60 * 60), '/', '', false, false);
            setcookie('tracking_consent', 'yes', time() + (365 * 24 * 60 * 60), '/', '', false, false);
            header("Location: test_rgpd_cookies.php?msg=accepted");
            exit;
            
        case 'refuse':
            // Simuler le refus
            setcookie('cookie_consent', 'refused', time() + (365 * 24 * 60 * 60), '/', '', false, false);
            setcookie('tracking_consent', 'no', time() + (365 * 24 * 60 * 60), '/', '', false, false);
            header("Location: test_rgpd_cookies.php?msg=refused");
            exit;
            
        case 'reset_session':
            // Réinitialiser la session
            session_unset();
            session_destroy();
            session_start(); // Redémarrer une nouvelle session
            header("Location: test_rgpd_cookies.php?msg=session_reset");
            exit;
    }
}

// Démarrer la session pour afficher la page
session_start();

// Vérifier l'état actuel du consentement
$consent_status = $_COOKIE['cookie_consent'] ?? 'non_defini';
$tracking_status = $_COOKIE['tracking_consent'] ?? 'non_defini';

// Messages après redirection
$message = '';
$message_type = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'cleared':
            $message = "✅ Tous les cookies ont été supprimés.";
            $message_type = 'success';
            break;
        case 'accepted':
            $message = "✅ Cookies acceptés ! Le tracking est maintenant ACTIF.";
            $message_type = 'success';
            break;
        case 'refused':
            $message = "❌ Cookies refusés ! Le tracking est maintenant INACTIF.";
            $message_type = 'danger';
            break;
        case 'session_reset':
            $message = "🔄 Session réinitialisée ! Vous pouvez enregistrer une nouvelle visite.";
            $message_type = 'success';
            break;
    }
}

// Connexion à la base pour compter les visites
require_once '../../includes/config.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM visites");
    $total_visites = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Dernières visites
    $stmt = $pdo->query("SELECT * FROM visites ORDER BY date_visite DESC LIMIT 5");
    $dernieres_visites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $total_visites = 'Erreur';
    $dernieres_visites = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test RGPD - Consentement Cookies</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .content {
            padding: 40px;
        }
        
        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            animation: slideDown 0.3s ease-out;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .status-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .status-card h2 {
            color: #1e293b;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #e2e8f0;
        }
        
        .status-label {
            font-weight: 600;
            color: #475569;
        }
        
        .status-value {
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .status-accepted {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-refused {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-none {
            background: #fef3c7;
            color: #92400e;
        }
        
        .test-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 18px 30px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }
        
        .table-container {
            margin-top: 30px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th {
            background: #1e293b;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:hover {
            background: #f8fafc;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #d1fae5; color: #065f46; }
        
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .back-link {
            margin-top: 30px;
            text-align: center;
        }
        
        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍪 Test RGPD</h1>
            <p>Vérification du système de consentement des cookies</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= $message ?>
            </div>
            <?php endif; ?>
            
            <!-- Statut actuel -->
            <div class="status-card">
                <h2>📊 Statut actuel du consentement</h2>
                <div class="status-row">
                    <span class="status-label">🍪 Cookie de consentement :</span>
                    <span class="status-value <?= $consent_status === 'accepted' ? 'status-accepted' : ($consent_status === 'refused' ? 'status-refused' : 'status-none') ?>">
                        <?= strtoupper($consent_status) ?>
                    </span>
                </div>
                <div class="status-row">
                    <span class="status-label">📈 Tracking autorisé :</span>
                    <span class="status-value <?= $tracking_status === 'yes' ? 'status-accepted' : ($tracking_status === 'no' ? 'status-refused' : 'status-none') ?>">
                        <?= strtoupper($tracking_status) ?>
                    </span>
                </div>
                <div class="status-row">
                    <span class="status-label">📁 Total visites en BDD :</span>
                    <span class="status-value status-accepted">
                        <?= $total_visites ?> entrées
                    </span>
                </div>
                <div class="status-row">
                    <span class="status-label">🔄 État de la session :</span>
                    <span class="status-value <?= isset($_SESSION['visite_enregistree']) ? 'status-refused' : 'status-accepted' ?>">
                        <?= isset($_SESSION['visite_enregistree']) ? '⚠️ VISITE DÉJÀ ENREGISTRÉE' : '✅ PRÊT À ENREGISTRER' ?>
                    </span>
                </div>
            </div>
            
            <!-- Actions de test -->
            <div class="status-card">
                <h2>🧪 Actions de test</h2>
                <div class="test-buttons">
                    <a href="?action=accept" class="btn btn-success">
                        ✅ Accepter les cookies
                    </a>
                    <a href="?action=refuse" class="btn btn-danger">
                        ❌ Refuser les cookies
                    </a>
                    <a href="?action=clear" class="btn btn-warning">
                        🗑️ Supprimer tous les cookies
                    </a>
                    <a href="?action=reset_session" class="btn btn-primary" style="background: #f59e0b;">
                        🔄 Réinitialiser la session
                    </a>
                    <a href="../index.php?page=accueil" class="btn btn-primary">
                        🏠 Retour au site
                    </a>
                </div>
            </div>
            
            <!-- Dernières visites -->
            <?php if (!empty($dernieres_visites)): ?>
            <div class="status-card">
                <h2>📋 5 dernières visites enregistrées</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Page</th>
                                <th>Navigateur</th>
                                <th>OS</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dernieres_visites as $visite): ?>
                            <tr>
                                <td><span class="badge badge-info"><?= htmlspecialchars($visite['ip_address']) ?></span></td>
                                <td><?= htmlspecialchars($visite['page_url']) ?></td>
                                <td><?= htmlspecialchars($visite['browser']) ?></td>
                                <td><?= htmlspecialchars($visite['os']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($visite['date_visite'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Instructions -->
            <div class="status-card">
                <h2>📝 Instructions de test</h2>
                <p style="background: #fef3c7; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b; margin-bottom: 20px; color: #92400e;">
                    <strong>⚠️ IMPORTANT :</strong> Le tracker enregistre 1 visite par session. Pour tester plusieurs enregistrements, 
                    cliquez sur <strong>"🔄 Réinitialiser la session"</strong> entre chaque test.
                </p>
                <ol style="line-height: 2; color: #475569;">
                    <li><strong>Cliquez sur "Supprimer tous les cookies"</strong> pour remettre à zéro</li>
                    <li><strong>Cliquez sur "Accepter les cookies"</strong></li>
                    <li><strong>Cliquez sur "Réinitialiser la session"</strong> ← Important !</li>
                    <li><strong>Retournez au site</strong> → Naviguez sur 1-2 pages</li>
                    <li><strong>Revenez ici</strong> → Le total visites DOIT avoir augmenté de 1 ✅</li>
                    <li><strong>Cliquez sur "Réinitialiser la session"</strong> à nouveau</li>
                    <li><strong>Retournez sur le site</strong> → Une nouvelle visite sera enregistrée</li>
                    <li><strong>Test du refus : Cliquez sur "Refuser les cookies"</strong></li>
                    <li><strong>Cliquez sur "Réinitialiser la session"</strong></li>
                    <li><strong>Naviguez sur le site</strong> → AUCUNE visite ne sera enregistrée ✅</li>
                </ol>
            </div>
            
            <div class="back-link">
                <a href="index.html">← Retour aux tests de sécurité</a>
            </div>
        </div>
    </div>
</body>
</html>
