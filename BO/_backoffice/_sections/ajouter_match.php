<?php
require_once __DIR__ . '/../../../includes/config.php';

// Vérification accès admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['ROLE_ADMIN', 'ROLE_SPORT_MANAGER'])) {
    $_SESSION['flash']['danger'] = "Accès non autorisé.";
    header('Location: ../admin.php?section=dashboard');
    exit;
}

// Récupérer les saisons
$seasons_query = $conn->query("SELECT id_season, name FROM seasons ORDER BY is_active DESC, start_date DESC");
$seasons = $seasons_query->fetch_all(MYSQLI_ASSOC);

// Récupérer les logos disponibles
$logos_query = $conn->query("SELECT id_media, file_name, file_path FROM medias WHERE media_type = 'logo' ORDER BY file_name ASC");
$logos = $logos_query->fetch_all(MYSQLI_ASSOC);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $match_date = $_POST['match_date'] ?? '';
    $match_time = $_POST['match_time'] ?? '';
    $home_team_name = trim($_POST['home_team_name'] ?? '');
    $away_team_name = trim($_POST['away_team_name'] ?? '');
    $home_logo = isset($_POST['home_logo']) && $_POST['home_logo'] !== '' ? (int)$_POST['home_logo'] : null;
    $away_logo = isset($_POST['away_logo']) && $_POST['away_logo'] !== '' ? (int)$_POST['away_logo'] : null;
    $location = trim($_POST['location'] ?? '');
    $phase = $_POST['phase'] ?? null;
    $competition_level = $_POST['competition_level'] ?? 'R1';
    $match_type = $_POST['match_type'] ?? 'championnat';
    $id_season = (int)($_POST['id_season'] ?? 1);
    $home_score = !empty($_POST['home_score']) ? (int)$_POST['home_score'] : null;
    $away_score = !empty($_POST['away_score']) ? (int)$_POST['away_score'] : null;

    // Validation
    $errors = [];

    if (empty($match_date)) $errors[] = "La date du match est obligatoire.";
    if (empty($match_time)) $errors[] = "L'heure du match est obligatoire.";
    if (empty($home_team_name)) $errors[] = "Le nom de l'équipe domicile est obligatoire.";
    if (empty($away_team_name)) $errors[] = "Le nom de l'équipe extérieure est obligatoire.";
    if ($home_team_name === $away_team_name) $errors[] = "Les deux équipes doivent être différentes.";
    if (empty($location)) $errors[] = "Le lieu du match est obligatoire.";

    // Si pas d'erreurs, créer/récupérer les équipes et insérer le match
    if (empty($errors)) {
        $datetime = $match_date . ' ' . $match_time . ':00';

        // Fonction pour créer ou récupérer une équipe
        function getOrCreateTeam($conn, $teamName, $logoId, $level)
        {
            // Chercher si l'équipe existe déjà
            $stmt = $conn->prepare("SELECT id_team FROM teams WHERE name = ?");
            $stmt->bind_param('s', $teamName);
            $stmt->execute();
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();

            if ($id) return (int)$id;

            // Créer la nouvelle équipe
            $stmt2 = $conn->prepare("INSERT INTO teams (name, level, id_media, id_club_team, id_category) VALUES (?, ?, ?, 0, 1)");
            $stmt2->bind_param('ssi', $teamName, $level, $logoId);
            $stmt2->execute();
            $newId = $stmt2->insert_id;
            $stmt2->close();

            return (int)$newId;
        }

        $id_home = getOrCreateTeam($conn, $home_team_name, $home_logo, $competition_level);
        $id_away = getOrCreateTeam($conn, $away_team_name, $away_logo, $competition_level);

        $stmt = $conn->prepare("
            INSERT INTO matches (match_date, location, phase, competition_level, match_type, id_home_team, id_away_team, id_season, home_score, away_score)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sssssiiii', $datetime, $location, $phase, $competition_level, $match_type, $id_home, $id_away, $id_season, $home_score, $away_score);

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = "✅ Match ajouté avec succès !";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $errors[] = "Erreur lors de l'ajout : " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un match - ES Moulon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #009639 0%, #007a2e 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 150, 57, 0.3);
        }

        .header h1 {
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        .back-link {
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 15px;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .required {
            color: #e74c3c;
        }

        input[type="date"],
        input[type="time"],
        input[type="text"],
        input[type="number"],
        select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #009639;
            box-shadow: 0 0 0 3px rgba(0, 150, 57, 0.1);
        }

        .logo-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
        }

        .logo-option {
            position: relative;
            cursor: pointer;
            border: 3px solid transparent;
            border-radius: 8px;
            padding: 8px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .logo-option:hover {
            border-color: #009639;
            background: #f8f9fa;
        }

        .logo-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .logo-option input[type="radio"]:checked+img {
            border-color: #009639;
        }

        .logo-option img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 5px;
            background: white;
        }

        .logo-name {
            font-size: 0.7em;
            color: #666;
            margin-top: 5px;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #009639 0%, #007a2e 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 150, 57, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            text-decoration: none;
        }

        .conditional-fields {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <a href="/es_moulon/BO/admin.php?section=calendrier" class="back-link">
                ← Retour au calendrier
            </a>
            <h1>➕ Ajouter un match</h1>
            <p style="opacity:0.9;">Saisissez les informations du match</p>
        </div>

        <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['flash']['success'] ?>
            </div>
            <?php unset($_SESSION['flash']['success']); ?>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>❌ Erreurs détectées :</strong>
                <ul style="margin-top:10px; padding-left:20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" id="matchForm">
                <div class="form-grid">
                    <!-- Date et heure -->
                    <div class="form-group">
                        <label for="match_date">
                            📅 Date du match <span class="required">*</span>
                        </label>
                        <input type="date" name="match_date" id="match_date" required
                            value="<?= $_POST['match_date'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="match_time">
                            🕐 Heure <span class="required">*</span>
                        </label>
                        <input type="time" name="match_time" id="match_time" required
                            value="<?= $_POST['match_time'] ?? '15:00' ?>">
                    </div>

                    <!-- Type de match -->
                    <div class="form-group">
                        <label for="match_type">
                            ⚽ Type de match <span class="required">*</span>
                        </label>
                        <select name="match_type" id="match_type" required>
                            <option value="championnat">Championnat</option>
                            <option value="coupe">Coupe</option>
                            <option value="amical">Amical</option>
                        </select>
                    </div>

                    <!-- Saison -->
                    <div class="form-group">
                        <label for="id_season">📆 Saison</label>
                        <select name="id_season" id="id_season">
                            <?php foreach ($seasons as $s): ?>
                                <option value="<?= $s['id_season'] ?>" selected>
                                    <?= htmlspecialchars($s['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Équipe Domicile -->
                <div class="form-group full-width">
                    <label for="home_team_name">
                        🏠 Équipe domicile <span class="required">*</span>
                    </label>
                    <input type="text" name="home_team_name" id="home_team_name"
                        placeholder="Ex: ES Moulon" required
                        value="<?= htmlspecialchars($_POST['home_team_name'] ?? '') ?>">
                </div>

                <?php if (!empty($logos)): ?>
                    <div class="form-group full-width">
                        <label>🎨 Logo équipe domicile (optionnel)</label>
                        <div class="logo-selector" id="homeLogoSelector">
                            <div class="logo-option">
                                <input type="radio" name="home_logo" value="" id="home_logo_none" checked>
                                <label for="home_logo_none" style="cursor:pointer;">
                                    <div style="width:60px; height:60px; display:flex; align-items:center; justify-content:center; border:2px dashed #ccc; border-radius:8px;">
                                        ❌
                                    </div>
                                    <div class="logo-name">Aucun</div>
                                </label>
                            </div>
                            <?php foreach ($logos as $logo): ?>
                                <div class="logo-option">
                                    <input type="radio" name="home_logo" value="<?= $logo['id_media'] ?>" id="home_logo_<?= $logo['id_media'] ?>">
                                    <label for="home_logo_<?= $logo['id_media'] ?>" style="cursor:pointer;">
                                        <img src="<?= htmlspecialchars($logo['file_path']) ?>" alt="<?= htmlspecialchars($logo['file_name']) ?>">
                                        <div class="logo-name"><?= htmlspecialchars(pathinfo($logo['file_name'], PATHINFO_FILENAME)) ?></div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Équipe Extérieure -->
                <div class="form-group full-width">
                    <label for="away_team_name">
                        ✈️ Équipe extérieure <span class="required">*</span>
                    </label>
                    <input type="text" name="away_team_name" id="away_team_name"
                        placeholder="Ex: FC Chartres" required
                        value="<?= htmlspecialchars($_POST['away_team_name'] ?? '') ?>">
                </div>

                <?php if (!empty($logos)): ?>
                    <div class="form-group full-width">
                        <label>🎨 Logo équipe extérieure (optionnel)</label>
                        <div class="logo-selector">
                            <div class="logo-option">
                                <input type="radio" name="away_logo" value="" id="away_logo_none" checked>
                                <label for="away_logo_none" style="cursor:pointer;">
                                    <div style="width:60px; height:60px; display:flex; align-items:center; justify-content:center; border:2px dashed #ccc; border-radius:8px;">
                                        ❌
                                    </div>
                                    <div class="logo-name">Aucun</div>
                                </label>
                            </div>
                            <?php foreach ($logos as $logo): ?>
                                <div class="logo-option">
                                    <input type="radio" name="away_logo" value="<?= $logo['id_media'] ?>" id="away_logo_<?= $logo['id_media'] ?>">
                                    <label for="away_logo_<?= $logo['id_media'] ?>" style="cursor:pointer;">
                                        <img src="<?= htmlspecialchars($logo['file_path']) ?>" alt="<?= htmlspecialchars($logo['file_name']) ?>">
                                        <div class="logo-name"><?= htmlspecialchars(pathinfo($logo['file_name'], PATHINFO_FILENAME)) ?></div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Lieu -->
                <div class="form-group full-width">
                    <label for="location">
                        📍 Lieu du match <span class="required">*</span>
                    </label>
                    <input type="text" name="location" id="location"
                        placeholder="Ex: Stade du Moulon" required
                        value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                </div>

                <!-- Champs conditionnels (Phase et Niveau) -->
                <div class="conditional-fields" id="conditionalFields">
                    <h3 style="color:#009639; margin-bottom:15px;">⚙️ Informations complémentaires</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="phase">🔄 Phase</label>
                            <select name="phase" id="phase">
                                <option value="">-- Aucune --</option>
                                <option value="Aller">Aller</option>
                                <option value="Retour">Retour</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="competition_level">🏆 Niveau</label>
                            <select name="competition_level" id="competition_level">
                                <option value="R1">R1</option>
                                <option value="R2">R2</option>
                                <option value="R3">R3</option>
                                <option value="N3">N3</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section Résultat -->
                <div style="margin-top:30px; padding-top:30px; border-top:2px dashed #e0e0e0;">
                    <h3 style="color:#009639; margin-bottom:20px;">🎯 Résultat du match (optionnel)</h3>
                    <p style="color:#666; margin-bottom:20px; font-size:0.95em;">
                        Laissez vide si le match n'a pas encore eu lieu
                    </p>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="home_score">🏠 Score domicile</label>
                            <input type="number" name="home_score" id="home_score"
                                min="0" max="50" placeholder="Ex: 2"
                                value="<?= $_POST['home_score'] ?? '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="away_score">✈️ Score extérieur</label>
                            <input type="number" name="away_score" id="away_score"
                                min="0" max="50" placeholder="Ex: 1"
                                value="<?= $_POST['away_score'] ?? '' ?>">
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:15px; margin-top:30px; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">
                        ✅ Ajouter le match
                    </button>
                    <a href="/es_moulon/BO/admin.php?section=calendrier" class="btn btn-secondary">
                        ❌ Annuler
                    </a>
                </div>
            </form>
        </div>

        <div class="card" style="background:#e3f2fd; border-left:4px solid #2196f3;">
            <h3 style="color:#1976d2; margin-bottom:15px;">💡 Astuces</h3>
            <ul style="list-style:none; padding:0;">
                <li style="padding:8px 0; padding-left:25px; position:relative;">
                    <span style="position:absolute; left:0; color:#2196f3; font-weight:bold;">✓</span>
                    Pour les <strong>matchs de coupe</strong>, la phase et le niveau ne sont pas nécessaires
                </li>
                <li style="padding:8px 0; padding-left:25px; position:relative;">
                    <span style="position:absolute; left:0; color:#2196f3; font-weight:bold;">✓</span>
                    Si l'équipe n'existe pas, elle sera créée automatiquement
                </li>
                <li style="padding:8px 0; padding-left:25px; position:relative;">
                    <span style="position:absolute; left:0; color:#2196f3; font-weight:bold;">✓</span>
                    Vous pouvez associer un logo depuis votre bibliothèque média
                </li>
                <li style="padding:8px 0; padding-left:25px; position:relative;">
                    <span style="position:absolute; left:0; color:#2196f3; font-weight:bold;">✓</span>
                    Laissez les scores vides pour les matchs à venir
                </li>
            </ul>
        </div>
    </div>

    <script>
        const matchTypeSelect = document.getElementById('match_type');
        const conditionalFields = document.getElementById('conditionalFields');
        const phaseSelect = document.getElementById('phase');

        // Gérer l'affichage conditionnel des champs
        function updateConditionalFields() {
            const matchType = matchTypeSelect.value;

            if (matchType === 'coupe' || matchType === 'amical') {
                conditionalFields.style.display = 'none';
                phaseSelect.value = '';
                phaseSelect.removeAttribute('required');
            } else {
                conditionalFields.style.display = 'block';
            }
        }

        matchTypeSelect.addEventListener('change', updateConditionalFields);

        // Initialiser au chargement
        updateConditionalFields();

        // Validation formulaire
        document.getElementById('matchForm').addEventListener('submit', function(e) {
            const homeTeam = document.getElementById('home_team_name').value.trim();
            const awayTeam = document.getElementById('away_team_name').value.trim();

            if (homeTeam.toLowerCase() === awayTeam.toLowerCase()) {
                e.preventDefault();
                alert('❌ Les deux équipes doivent être différentes !');
                return false;
            }

            const homeScore = document.getElementById('home_score').value;
            const awayScore = document.getElementById('away_score').value;

            if ((homeScore && !awayScore) || (!homeScore && awayScore)) {
                e.preventDefault();
                alert('❌ Si vous renseignez un score, vous devez renseigner les deux scores !');
                return false;
            }
        });

        // Auto-remplissage du lieu
        document.getElementById('home_team_name').addEventListener('input', function() {
            const teamName = this.value.toLowerCase();
            const locationField = document.getElementById('location');

            if (teamName.includes('moulon') && !locationField.value) {
                locationField.value = 'Stade du Moulon';
            }
        });

        // Effet visuel sur les logos sélectionnés
        document.querySelectorAll('.logo-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Retirer la sélection des autres logos du même groupe
                const name = this.name;
                document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                    r.closest('.logo-option').style.borderColor = 'transparent';
                });

                // Mettre en évidence le logo sélectionné
                if (this.checked && this.value !== '') {
                    this.closest('.logo-option').style.borderColor = '#009639';
                    this.closest('.logo-option').style.background = 'rgba(0, 150, 57, 0.05)';
                }
            });
        });
    </script>
</body>

</html>