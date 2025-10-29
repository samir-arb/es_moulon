<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Debug Cookies & Tracking</title>
    <?php
    // IMPORTANT : Traiter les actions AVANT tout affichage HTML
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'set_accept':
                setcookie('cookie_consent', 'accepted', time() + (365 * 24 * 60 * 60), '/', '', false, false);
                setcookie('tracking_consent', 'yes', time() + (365 * 24 * 60 * 60), '/', '', false, false);
                header("Location: debug_cookies.php?msg=accepted");
                exit;
                
            case 'set_refuse':
                setcookie('cookie_consent', 'refused', time() + (365 * 24 * 60 * 60), '/', '', false, false);
                setcookie('tracking_consent', 'no', time() + (365 * 24 * 60 * 60), '/', '', false, false);
                header("Location: debug_cookies.php?msg=refused");
                exit;
                
            case 'clear':
                setcookie('cookie_consent', '', time() - 3600, '/', '', false, false);
                setcookie('tracking_consent', '', time() - 3600, '/', '', false, false);
                setcookie('PHPSESSID', '', time() - 3600, '/', '', false, false);
                header("Location: debug_cookies.php?msg=cleared");
                exit;
                
            case 'reset_session':
                session_start();
                session_unset();
                session_destroy();
                session_start(); // Redémarrer une nouvelle session
                header("Location: debug_cookies.php?msg=session_reset");
                exit;
        }
    }
    ?>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #334155;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        h1 { color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 10px; }
        h2 { color: #3b82f6; margin-top: 30px; }
        .info-box {
            background: #1e293b;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .error-box {
            background: #1e293b;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .warning-box {
            background: #1e293b;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .code {
            background: #0f172a;
            padding: 10px 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #475569;
        }
        th {
            background: #1e293b;
            color: #10b981;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            font-weight: bold;
        }
        .btn:hover { background: #2563eb; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug RGPD - Cookies & Tracking</h1>
        <p>Cette page permet de diagnostiquer les problèmes de cookies et de tracking.</p>

        <?php
        // Afficher le message après redirection
        if (isset($_GET['msg'])) {
            switch ($_GET['msg']) {
                case 'accepted':
                    echo '<div class="info-box"><span class="success">✅ Cookie défini sur ACCEPTED avec succès !</span><br>La page a été rechargée automatiquement.</div>';
                    break;
                case 'refused':
                    echo '<div class="error-box"><span class="error">❌ Cookie défini sur REFUSED avec succès !</span><br>La page a été rechargée automatiquement.</div>';
                    break;
                case 'cleared':
                    echo '<div class="warning-box"><span class="warning">🗑️ Tous les cookies ont été supprimés !</span><br>La page a été rechargée automatiquement.</div>';
                    break;
                case 'session_reset':
                    echo '<div class="info-box"><span class="success">🔄 Session réinitialisée !</span><br>Vous pouvez maintenant enregistrer une nouvelle visite.</div>';
                    break;
            }
        }
        ?>

        <h2>📊 État des cookies</h2>
        <?php
        $cookie_consent = $_COOKIE['cookie_consent'] ?? null;
        $tracking_consent = $_COOKIE['tracking_consent'] ?? null;
        
        if ($cookie_consent === 'accepted') {
            echo '<div class="info-box"><span class="success">✅ Cookie consent = accepted</span><br>Le tracking DOIT fonctionner.</div>';
        } elseif ($cookie_consent === 'refused') {
            echo '<div class="error-box"><span class="error">❌ Cookie consent = refused</span><br>Le tracking NE DOIT PAS fonctionner.</div>';
        } else {
            echo '<div class="warning-box"><span class="warning">⚠️ Cookie consent = non défini</span><br>L\'utilisateur n\'a pas encore choisi. La bannière doit apparaître.</div>';
        }
        
        echo '<div class="code">';
        echo '<strong>Tous les cookies actuels :</strong><br>';
        if (empty($_COOKIE)) {
            echo 'Aucun cookie trouvé.';
        } else {
            foreach ($_COOKIE as $key => $value) {
                echo htmlspecialchars($key) . ' = ' . htmlspecialchars($value) . '<br>';
            }
        }
        echo '</div>';
        
        // Afficher l'état de la session
        session_start();
        echo '<div class="code">';
        echo '<strong>État de la session :</strong><br>';
        echo 'Session ID : ' . session_id() . '<br>';
        if (isset($_SESSION['visite_enregistree'])) {
            echo '<span class="warning">⚠️ Visite déjà enregistrée dans cette session</span><br>';
            echo '<em>→ Cliquez sur "Réinitialiser la session" pour enregistrer une nouvelle visite</em>';
        } else {
            echo '<span class="success">✅ Aucune visite enregistrée dans cette session</span><br>';
            echo '<em>→ La prochaine navigation enregistrera une visite</em>';
        }
        echo '</div>';
        ?>

        <h2>📁 État de la base de données</h2>
        <?php
        require_once __DIR__ . '/../includes/config.php';
        
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM visites");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo '<div class="info-box"><span class="success">✅ Connexion BDD OK</span><br>';
            echo '<strong>Total visites enregistrées :</strong> ' . $total . '</div>';
            
            // Dernières visites
            $stmt = $pdo->query("SELECT * FROM visites ORDER BY date_visite DESC LIMIT 10");
            $visites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($visites)) {
                echo '<h3>📋 10 dernières visites</h3>';
                echo '<table>';
                echo '<thead><tr><th>IP</th><th>Page</th><th>Navigateur</th><th>OS</th><th>Date</th></tr></thead>';
                echo '<tbody>';
                foreach ($visites as $v) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($v['ip_address']) . '</td>';
                    echo '<td>' . htmlspecialchars($v['page_url']) . '</td>';
                    echo '<td>' . htmlspecialchars($v['browser']) . '</td>';
                    echo '<td>' . htmlspecialchars($v['os']) . '</td>';
                    echo '<td>' . date('d/m H:i', strtotime($v['date_visite'])) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<div class="warning-box">⚠️ Aucune visite enregistrée pour le moment.</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="error-box"><span class="error">❌ Erreur BDD :</span> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>

        <h2>🧪 Actions de test</h2>
        <a href="?action=set_accept" class="btn btn-success">✅ Définir cookie = accepted</a>
        <a href="?action=set_refuse" class="btn btn-danger">❌ Définir cookie = refused</a>
        <a href="?action=clear" class="btn">🗑️ Supprimer tous les cookies</a>
        <a href="?action=reset_session" class="btn" style="background: #f59e0b;">🔄 Réinitialiser la session</a>
        <a href="index.php?page=accueil" class="btn">🏠 Retour au site</a>

        <h2>🛠️ Checklist de débogage</h2>
        <div class="code">
            <strong>⚠️ IMPORTANT : Le tracker enregistre 1 visite par session</strong><br>
            Pour tester plusieurs enregistrements, vous devez cliquer sur "🔄 Réinitialiser la session" entre chaque test.<br>
            <br>
            <strong>📝 Procédure de test complète :</strong><br>
            1. Cliquez sur "Supprimer tous les cookies"<br>
            2. Cliquez sur "Définir cookie = accepted"<br>
            3. Cliquez sur "Réinitialiser la session"<br>
            4. Allez sur le site et naviguez (1 visite sera enregistrée)<br>
            5. Revenez ici → Le total doit avoir augmenté de 1<br>
            6. Cliquez sur "Réinitialiser la session" à nouveau<br>
            7. Retournez sur le site → Une nouvelle visite sera enregistrée<br>
            <br>
            <strong>Test du refus :</strong><br>
            1. Cliquez sur "Définir cookie = refused"<br>
            2. Cliquez sur "Réinitialiser la session"<br>
            3. Naviguez sur le site → AUCUNE visite ne sera enregistrée ✅<br>
            <br>
            <strong>Pourquoi 1 seule visite par session ?</strong><br>
            C'est normal : cela évite de compter plusieurs fois le même visiteur.<br>
            En production, chaque nouveau visiteur aura une nouvelle session.<br>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #475569;">
            <a href="_tests_securite/" class="btn">← Retour aux tests de sécurité</a>
            <a href="index.php?page=accueil" class="btn">🏠 Retour au site</a>
        </div>
    </div>
</body>
</html>
