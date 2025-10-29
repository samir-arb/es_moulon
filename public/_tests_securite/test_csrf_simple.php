<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🧪 Test CSRF - ES Moulon</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            width: 100%;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #1f2937;
        }
        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .test-section {
            background: #f9fafb;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .test-section h3 {
            color: #1f2937;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }
        .btn-danger:hover {
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6);
        }
        .info-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        code {
            background: #1f2937;
            color: #10b981;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
        }
        ul { margin-left: 25px; line-height: 1.8; }
        li { margin: 8px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🧪 Test de Protection CSRF</h1>
            <span class="badge badge-danger">⚠️ ENVIRONNEMENT DE TEST</span>
            
            <div class="warning-box">
                <strong>⚡ Important :</strong> Cette page permet de vérifier que votre site est bien protégé contre les attaques CSRF.
            </div>

            <!-- TEST 1 : AVEC TOKEN (normal) -->
            <div class="test-section">
                <h3>✅ TEST 1 : Formulaire normal (AVEC token CSRF)</h3>
                <p>Ce test simule un utilisateur légitime qui remplit le formulaire sur votre site.</p>
                
                <div class="info-box">
                    <strong>Données envoyées :</strong>
                    <ul>
                        <li>Prénom : Test</li>
                        <li>Nom : Utilisateur</li>
                        <li>Email : test@esmoulon.fr</li>
                        <li>Token CSRF : <code style="background:#10b981;color:white;">✅ PRÉSENT</code></li>
                    </ul>
                </div>

                <form method="POST" action="http://localhost/es_moulon/public/traitement_contact.php">
                    <input type="hidden" name="type_form" value="contact">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? 'TOKEN_MANQUANT' ?>">
                    <input type="hidden" name="prenom" value="Test">
                    <input type="hidden" name="nom" value="Utilisateur">
                    <input type="hidden" name="email" value="test@esmoulon.fr">
                    <input type="hidden" name="telephone" value="0612345678">
                    <input type="hidden" name="sujet" value="info">
                    <input type="hidden" name="message" value="Ceci est un test AVEC token CSRF (devrait fonctionner)">
                    <input type="hidden" name="accepte_conditions" value="1">
                    
                    <button type="submit">
                        ✅ Tester avec TOKEN (devrait fonctionner)
                    </button>
                </form>

                <div class="success-box" style="margin-top: 15px;">
                    <strong>Résultat attendu :</strong> Message vert "✅ Merci ! Votre message a bien été envoyé"
                </div>
            </div>

            <!-- TEST 2 : SANS TOKEN (attaque) -->
            <div class="test-section" style="border-left-color: #ef4444;">
                <h3>❌ TEST 2 : Attaque CSRF (SANS token)</h3>
                <p>Ce test simule un pirate qui essaie d'envoyer un formulaire sans le token de sécurité.</p>
                
                <div class="warning-box">
                    <strong>Données envoyées :</strong>
                    <ul>
                        <li>Prénom : SPAM</li>
                        <li>Nom : BOT</li>
                        <li>Email : pirate@mechant.com</li>
                        <li>Token CSRF : <code style="background:#ef4444;color:white;">❌ AUCUN</code></li>
                    </ul>
                </div>

                <form method="POST" action="http://localhost/es_moulon/public/traitement_contact.php">
                    <input type="hidden" name="type_form" value="contact">
                    <!-- ❌ PAS DE TOKEN CSRF -->
                    <input type="hidden" name="prenom" value="SPAM">
                    <input type="hidden" name="nom" value="BOT">
                    <input type="hidden" name="email" value="pirate@mechant.com">
                    <input type="hidden" name="telephone" value="0000000000">
                    <input type="hidden" name="sujet" value="info">
                    <input type="hidden" name="message" value="Ceci est une attaque CSRF (devrait être bloquée)">
                    <input type="hidden" name="accepte_conditions" value="1">
                    
                    <button type="submit" class="btn-danger">
                        🚨 Tester SANS TOKEN (devrait être bloqué)
                    </button>
                </form>

                <div class="warning-box" style="margin-top: 15px; background: #fee2e2; border-left-color: #ef4444;">
                    <strong>Résultat attendu :</strong> Message rouge "❌ Token CSRF invalide. Tentative d'attaque détectée !"
                </div>
            </div>
        </div>

  <!--  <div class="card" style="background: #1f2937; color: white;">
            <h3 style="color: white; margin-bottom: 15px;">📚 Comprendre le test</h3>
            <p style="line-height: 1.8; opacity: 0.9;">
                Le <strong>token CSRF</strong> est un code secret unique généré par votre serveur pour chaque session.
                Si un pirate essaie de soumettre un formulaire depuis son propre site, il n'aura pas ce code secret,
                et votre serveur rejettera automatiquement la requête. C'est comme un badge d'accès : 
                sans le bon badge, impossible d'entrer ! 🔒 
            </p>
        </div> -->
    </div>
</body>
</html>
