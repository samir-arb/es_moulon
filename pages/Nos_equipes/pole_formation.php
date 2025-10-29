<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';

// Récupérer les équipes de l'école de foot avec leurs coachs
$sql = "
    SELECT 
        t.id_team,
        t.name,
        t.level,
        m.file_path AS team_photo,
        c.name AS category_name,

        -- Coach principal
        (
            SELECT u.first_name
            FROM users u
            INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
            INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
            WHERE ucf.id_team = t.id_team
              AND cf.function_name LIKE '%entraineur%'
              AND cf.function_name NOT LIKE '%adjoint%'
            ORDER BY ucf.start_date DESC
            LIMIT 1
        ) AS coach_firstname,

        (
            SELECT u.name
            FROM users u
            INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
            INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
            WHERE ucf.id_team = t.id_team
              AND cf.function_name LIKE '%entraineur%'
              AND cf.function_name NOT LIKE '%adjoint%'
            ORDER BY ucf.start_date DESC
            LIMIT 1
        ) AS coach_name,

        (
            SELECT m2.file_path
            FROM users u
            INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
            INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
            LEFT JOIN medias m2 ON u.id_media = m2.id_media
            WHERE ucf.id_team = t.id_team
              AND cf.function_name LIKE '%entraineur%'
              AND cf.function_name NOT LIKE '%adjoint%'
            ORDER BY ucf.start_date DESC
            LIMIT 1
        ) AS coach_photo,

        -- Coach adjoint
        (
            SELECT u.first_name
            FROM users u
            INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
            INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
            WHERE ucf.id_team = t.id_team
              AND cf.function_name LIKE '%adjoint%'
            ORDER BY ucf.start_date DESC
            LIMIT 1
        ) AS adjoint_firstname,

        (
            SELECT u.name
            FROM users u
            INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
            INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
            WHERE ucf.id_team = t.id_team
              AND cf.function_name LIKE '%adjoint%'
            ORDER BY ucf.start_date DESC
            LIMIT 1
        ) AS adjoint_name,

        (
            SELECT m2.file_path
            FROM users u
            INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
            INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
            LEFT JOIN medias m2 ON u.id_media = m2.id_media
            WHERE ucf.id_team = t.id_team
              AND cf.function_name LIKE '%adjoint%'
            ORDER BY ucf.start_date DESC
            LIMIT 1
        ) AS adjoint_photo

    FROM teams t
    LEFT JOIN categories c ON t.id_category = c.id_category
    LEFT JOIN medias m ON t.id_media = m.id_media
    WHERE c.name IN ('U16', 'U17', 'U18', 'U19','U20')
      AND (t.id_club_team = 1 OR t.id_club_team IS NULL)
    ORDER BY 
        CAST(SUBSTRING(c.name, 2) AS UNSIGNED),
        t.name
";

$result = $conn->query($sql);
$teams = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Récupérer le responsable
$responsable_sql = "
    SELECT 
        u.first_name, 
        u.name, 
        cf.function_name AS role_title,
        m.file_path AS photo
    FROM users u
    INNER JOIN users_club_functions ucf ON u.id_user = ucf.id_user
    INNER JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
    LEFT JOIN medias m ON u.id_media = m.id_media
    WHERE 
        LOWER(CONVERT(cf.function_name USING utf8mb4)) LIKE '%responsable%'
        AND LOWER(CONVERT(cf.function_name USING utf8mb4)) LIKE '%ecole%'
    AND (ucf.end_date IS NULL OR ucf.end_date >= CURDATE())
    ORDER BY ucf.start_date DESC
    LIMIT 1
";

$responsable_result = $conn->query($responsable_sql);
$responsable = $responsable_result ? $responsable_result->fetch_assoc() : null;
?>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f5f5;
      color: #333;
    }

    :root {
      --green-primary: #009639;
      --green-light: #00b34a;
      --green-dark: #007a3d;
      --white: #ffffff;
    }

    /* ============================================
       OPTION 1 : DESIGN MODERNE AVEC GRADIENT
       ============================================ */
    .responsable-header.option1 {
      background: linear-gradient(135deg, #1a472a 0%, #2d5a3d 50%, #1a472a 100%);
      padding: 80px 20px;
      position: relative;
      overflow: hidden;
      border: none;
    }

    .responsable-header.option1::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
      pointer-events: none;
    }

    .responsable-header.option1 .responsable-inner {
      position: relative;
      z-index: 1;
    }

    .responsable-header.option1 .responsable-photo,
    .responsable-header.option1 .responsable-photo-placeholder {
      border: 6px solid #4CAF50;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3),
                  0 0 0 12px rgba(76, 175, 80, 0.2);
    }

    .responsable-header.option1 .responsable-text {
      color: #ffffff;
    }

    .responsable-header.option1 .responsable-quote {
      color: rgba(255, 255, 255, 0.95);
      background: rgba(255, 255, 255, 0.08);
      padding: 20px 25px;
      border-left: 4px solid #4CAF50;
      border-radius: 8px;
      margin-bottom: 25px;
    }

    .responsable-header.option1 .responsable-signature {
      color: #ffffff;
      padding: 15px 25px;
      background: rgba(76, 175, 80, 0.2);
      border-radius: 8px;
      border-left: 4px solid #4CAF50;
    }

    .responsable-header.option1 .responsable-signature strong {
      color: #4CAF50;
      font-size: 1.1em;
    }

    /* ============================================
       OPTION 2 : DESIGN CARTE ÉLÉGANTE
       ============================================ */
    .responsable-header.option2 {
      background: #f8f9fa;
      padding: 80px 20px;
      border: none;
    }

    .responsable-header.option2 .responsable-inner {
      background: white;
      padding: 50px;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
    }

    .responsable-header.option2 .responsable-inner::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 8px;
      height: 100%;
      background: linear-gradient(180deg, #4CAF50 0%, #2d5a3d 100%);
    }

    .responsable-header.option2 .responsable-photo,
    .responsable-header.option2 .responsable-photo-placeholder {
      border: 8px solid #f0f0f0;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      position: relative;
    }

    .responsable-header.option2 .responsable-photo-wrapper {
      position: relative;
    }

    .responsable-header.option2 .responsable-photo-wrapper::after {
      content: '⚽';
      position: absolute;
      bottom: 10px;
      right: 10px;
      width: 50px;
      height: 50px;
      background: #4CAF50;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5em;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      border: 4px solid white;
    }

    .responsable-header.option2 .responsable-quote {
      color: #555;
      background: #f8f9fa;
      padding: 25px 30px;
      border-radius: 12px;
      margin-bottom: 20px;
      position: relative;
      border-left: 5px solid #4CAF50;
    }

    .responsable-header.option2 .responsable-quote:first-of-type::before {
      content: '"';
      position: absolute;
      top: -10px;
      left: 10px;
      font-size: 4em;
      color: #4CAF50;
      opacity: 0.3;
      font-family: Georgia, serif;
      line-height: 1;
    }

    .responsable-header.option2 .responsable-signature {
      text-align: right;
      padding: 20px 30px;
      background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
      border-radius: 12px;
      border: 2px solid #e0e0e0;
    }

    .responsable-header.option2 .responsable-signature strong {
      color: #1a472a;
      font-size: 1.15em;
      display: block;
      margin-bottom: 5px;
    }

    /* ============================================
       OPTION 3 : DESIGN HÉRO MODERNE
       ============================================ */
    .responsable-header.option3 {
      background: #ffffff;
      padding: 0;
      border: none;
      position: relative;
    }

    .responsable-header.option3::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 200px;
      background: linear-gradient(135deg, #1a472a 0%, #4CAF50 100%);
      clip-path: polygon(0 0, 100% 0, 100% 70%, 0 100%);
    }

    .responsable-header.option3 .responsable-inner {
      position: relative;
      z-index: 1;
      padding: 60px 20px 80px;
      max-width: 1200px;
      margin: 120px auto 0;
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      flex-direction: column;
      text-align: center;
      gap: 30px;
    }

    .responsable-header.option3 .responsable-photo,
    .responsable-header.option3 .responsable-photo-placeholder {
      width: 200px;
      height: 200px;
      border: 8px solid white;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      margin-top: -80px;
    }

    .responsable-header.option3 .responsable-photo-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    .responsable-header.option3 .responsable-photo-wrapper::after {
      content: 'RESPONSABLE';
      position: absolute;
      top: -100px;
      background: #4CAF50;
      color: white;
      padding: 8px 25px;
      border-radius: 20px;
      font-size: 0.75em;
      font-weight: 700;
      letter-spacing: 2px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .responsable-header.option3 .responsable-text {
      max-width: 900px;
      margin: 0 auto;
    }

    .responsable-header.option3 .responsable-quote {
      color: #555;
      background: #f8f9fa;
      padding: 30px 40px;
      border-radius: 15px;
      margin-bottom: 20px;
      border-left: 6px solid #4CAF50;
      text-align: left;
    }

    .responsable-header.option3 .responsable-signature {
      text-align: center;
      padding: 25px;
      background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f4 100%);
      border-radius: 15px;
      border: 2px solid #e0e0e0;
      margin-top: 30px;
    }

    .responsable-header.option3 .responsable-signature strong {
      color: #1a472a;
      font-size: 1.3em;
      display: block;
      margin-bottom: 8px;
    }

    /* ============================================
       OPTION 4 : DESIGN PRO CLUB DE FOOT ⭐⭐⭐
       ============================================ */
    .responsable-header.option4 {
      background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
      padding: 80px 20px 60px;
      border: none;
      position: relative;
    }

    .responsable-header.option4::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, #009639 0%, #00b34a 50%, #009639 100%);
    }

    .responsable-header.option4 .responsable-inner {
      max-width: 1100px;
      margin: 0 auto;
      display: flex;
      align-items: flex-start;
      gap: 50px;
      background: white;
      padding: 50px;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
      border-left: 6px solid #009639;
    }

    .responsable-header.option4 .responsable-photo-wrapper {
      position: relative;
      flex-shrink: 0;
    }

    .responsable-header.option4 .responsable-photo,
    .responsable-header.option4 .responsable-photo-placeholder {
      width: 200px;
      height: 200px;
      border: 6px solid white;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15),
                  0 0 0 2px #009639;
      border-radius: 50%;
      object-fit: cover;
    }

    .responsable-header.option4 .responsable-photo-placeholder {
      background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f4 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 5em;
      color: #009639;
    }

    .responsable-header.option4 .responsable-text {
      flex: 1;
    }

    .responsable-header.option4 .responsable-quote {
      color: #444;
      font-size: 1em;
      line-height: 1.7;
      margin-bottom: 16px;
      padding-left: 20px;
      border-left: 3px solid #009639;
      font-style: normal;
    }

    .responsable-header.option4 .responsable-quote:first-of-type {
      font-size: 1.05em;
      font-weight: 500;
      color: #333;
    }

    .responsable-header.option4 .responsable-signature {
      margin-top: 30px;
      padding: 20px 25px;
      background: linear-gradient(135deg, #f0f9f4 0%, #ffffff 100%);
      border-radius: 10px;
      border-left: 4px solid #009639;
      text-align: left;
    }

    .responsable-header.option4 .responsable-signature strong {
      color: #009639;
      font-size: 1.1em;
      font-weight: 700;
      display: block;
      margin-bottom: 4px;
    }

    .responsable-header.option4 .responsable-signature small {
      color: #666;
      font-size: 0.9em;
      font-style: italic;
    }

    @media (max-width: 1024px) {
      .responsable-header.option4 .responsable-inner {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 40px 30px;
      }

      .responsable-header.option4 .responsable-quote {
        border-left: none;
        border-top: 3px solid #009639;
        padding-left: 0;
        padding-top: 15px;
      }

      .responsable-header.option4 .responsable-signature {
        text-align: center;
        border-left: none;
        border-top: 4px solid #009639;
      }
    }

    @media (max-width: 768px) {
      .responsable-header.option4 .responsable-inner {
        padding: 30px 20px;
      }

      .responsable-header.option4 .responsable-photo,
      .responsable-header.option4 .responsable-photo-placeholder {
        width: 160px;
        height: 160px;
      }
    }

    /* SECTION RESPONSABLE - BASE */
    .responsable-header {
      background: #fff;
      border-top: 6px solid var(--green-primary);
      border-bottom: 6px solid var(--green-primary);
      padding: 60px 20px;
    }

    .responsable-inner {
      max-width: 1100px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 40px;
    }

    .responsable-photo-wrapper {
      flex-shrink: 0;
    }

    .responsable-photo,
    .responsable-photo-placeholder {
      width: 180px;
      height: 180px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid var(--green-primary);
    }

    .responsable-photo-placeholder {
      background: #e9ecef;
      font-size: 5em;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .responsable-text {
      flex: 1;
      font-size: 0.95em;
      color: #444;
      line-height: 1.8;
      font-weight: 400;
      font-style: italic;
    }

    .responsable-quote {
      margin-bottom: 20px;
      text-align: justify;
      color: #555;
    }

    .responsable-signature {
      text-align: right;
      font-size: 0.95em;
      font-style: normal;
      color: #333;
      margin-top: 30px;
    }

    .responsable-signature strong {
      color: var(--green-dark);
      font-weight: 600;
    }

    /* SECTIONS ÉQUIPES */
    .teams-wrapper {
      background: var(--white);
      padding: 80px 0;
    }

    .team-section {
      padding: 60px 20px;
      border-bottom: 1px solid #e0e0e0;
    }

    .team-section:last-child {
      border-bottom: none;
    }

    .team-section:nth-child(even) {
      background: #f9f9f9;
    }

    .team-content {
      max-width: 1400px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      align-items: center;
    }

    /* PHOTO ÉQUIPE */
    .team-photo-container {
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .team-photo {
      width: 100%;
      height: 400px;
      object-fit: cover;
      object-position: center;
    }

    .team-photo-placeholder {
      width: 100%;
      height: 400px;
      background: linear-gradient(135deg, var(--green-light), var(--green-primary));
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 8em;
      color: var(--white);
    }

    /* INFO ÉQUIPE */
    .team-info {
      padding: 20px 0;

    }

    .team-category {
      font-size: 1.3em;
      font-weight: 600;
      color: #666;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .team-name {
      font-size: 2.2em;
      font-weight: 800;
      color: var(--green-primary);
      margin-bottom: 10px;
      text-transform: uppercase;
      letter-spacing: 2px;
      line-height: 1.2;
      white-space: nowrap;    
      word-break: keep-all;     
      overflow-wrap: normal;
      
    }

    .team-level {
      font-size: 1em;
      color: #999;
      margin-bottom: 30px;
      font-weight: 500;
    }

    .separator-line {
      width: 80px;
      height: 4px;
      background: var(--green-primary);
      margin: 25px 0;
    }
    
    /* COACHS */
    .coaches-container {
      display: flex;
      gap: 30px;
      margin-top: 30px;
    }

    .coach-item {
      flex: 1;
      text-align: center;
    }

    .coach-photo,
    .coach-photo-placeholder {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--green-primary);
      margin: 0 auto 15px;
    }

    .coach-photo-placeholder {
      background: #e0e0e0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3.5em;
      color: #999;
    }

    .coach-label {
      font-size: 0.75em;
      text-transform: uppercase;
      color: #999;
      letter-spacing: 1px;
      margin-bottom: 5px;
      font-weight: 600;
    }

    .coach-name {
      font-weight: 700;
      color: var(--green-dark);
      font-size: 1em;
      text-transform: uppercase;
    }

    .coach-name.undefined {
      color: #999;
      font-style: italic;
    }

    /* SECTIONS ALTERNÉES */
    .team-section:nth-child(odd) .team-content {
      direction: rtl;
    }

    .team-section:nth-child(odd) .team-content > * {
      direction: ltr;
    }

    /* RESPONSIVE */
    @media (max-width: 1024px) {
      .responsable-inner {
        flex-direction: column;
        text-align: center;
      }

      .responsable-signature {
        text-align: center;
      }

      .team-content,
      .team-section:nth-child(odd) .team-content {
        grid-template-columns: 1fr;
        direction: ltr;
      }

      .coaches-container {
        justify-content: center;
      }
    }

    @media (max-width: 768px) {
      .responsable-photo,
      .responsable-photo-placeholder {
        width: 150px;
        height: 150px;
      }

      .team-name {
        font-size: 2em;
      }

      .team-photo {
        height: 300px;
      }

      .coaches-container {
        flex-direction: column;
        align-items: center;
      }

      .coach-photo,
      .coach-photo-placeholder {
        width: 130px;
        height: 130px;
      }
    }

    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #999;
    }

    .empty-state span {
      font-size: 5em;
      display: block;
      margin-bottom: 20px;
    }
  </style>

  <!-- HEADER RESPONSABLE -->
  <!-- 🎨 Design Pro Club de Foot (Option 4) -->
  <section class="responsable-header option4">
    <div class="responsable-inner">
      <div class="responsable-photo-wrapper">
        <?php if ($responsable && !empty($responsable['photo'])): ?>
          <img src="<?= asset($responsable['photo']) ?>" alt="Photo responsable" class="responsable-photo">
        <?php else: ?>
          <div class="responsable-photo-placeholder">👤</div>
        <?php endif; ?>
      </div>

      <div class="responsable-text">
        <p class="responsable-quote">
          "Le pôle formation regroupe nos équipes U16 à U18, dernières étapes de la filière jeune avant l'intégration potentielle en catégorie senior.C’est une période décisive dans la construction du joueur, tant sur le plan sportif que personnel.
          Notre mission est de consolider les acquis techniques et tactiques tout en développant l’intelligence de jeu, la rigueur, l’autonomie et la capacité d’adaptation. Le travail physique devient plus spécifique, la gestion de l’effort et la stratégie prennent une place plus importante, et l’exigence monte d’un cran."
          
        </p>

        <p class="responsable-quote">
          "Encadrés par une équipe investie, nos jeunes bénéficient de séances intensives et d’un suivi individuel pour les accompagner au mieux dans leur progression. Le projet du joueur devient central : à ce stade, nous préparons chacun à évoluer vers les séniors, dans la continuité du projet club.
          Le pôle formation, c’est le lien entre le football jeune et le monde adulte. Une étape exigeante, mais passionnante."
        </p>

        <p class="responsable-signature">
          <strong><?= $responsable ? htmlspecialchars($responsable['first_name'] . ' ' . strtoupper($responsable['name'])) : 'Responsable à définir' ?></strong>,
          <?= $responsable['role_title'] ?? 'Responsable école de foot' ?>
        </p>
      </div>
    </div>
  </section>

  <!-- SECTIONS ÉQUIPES -->
  <div class="teams-wrapper">
    <?php if (empty($teams)): ?>
      <div class="empty-state">
        <span>⚽</span>
        <p>Aucune équipe enregistrée pour le moment.</p>
      </div>
    <?php else: ?>
      <?php foreach ($teams as $team): ?>
        <section class="team-section">
          <div class="team-content">
            <!-- PHOTO ÉQUIPE -->
            <div class="team-photo-container">
              <?php if (!empty($team['team_photo'])): ?>
                <img src="<?= asset($team['team_photo']) ?>" alt="Photo équipe" class="team-photo">
              <?php else: ?>
                <div class="team-photo-placeholder">⚽</div>
              <?php endif; ?>
            </div>

            <!-- INFO ÉQUIPE -->
            <div class="team-info">
              <div class="team-category"><?= htmlspecialchars($team['category_name']) ?></div>
              <h2 class="team-name"><?= htmlspecialchars($team['name']) ?></h2>
              <div class="team-level">Niveau : <?= htmlspecialchars($team['level']) ?></div>
              
              <div class="separator-line"></div>

              <!-- COACHS -->
              <div class="coaches-container">
                <!-- COACH PRINCIPAL -->
                <div class="coach-item">
                  <div class="coach-label">Entraîneur</div>
                  <?php if (!empty($team['coach_photo'])): ?>
                    <img src="<?= asset($team['coach_photo']) ?>" alt="Photo coach" class="coach-photo">
                  <?php else: ?>
                    <div class="coach-photo-placeholder">👤</div>
                  <?php endif; ?>
                  
                  <?php if (!empty($team['coach_firstname'])): ?>
                    <div class="coach-name">
                      <?= htmlspecialchars($team['coach_firstname']) ?><br>
                      <?= htmlspecialchars(strtoupper($team['coach_name'])) ?>
                    </div>
                  <?php else: ?>
                    <div class="coach-name undefined">À définir</div>
                  <?php endif; ?>
                </div>

                <!-- COACH ADJOINT -->
                <?php if (!empty($team['adjoint_firstname'])): ?>
                <div class="coach-item">
                  <div class="coach-label">Adjoint</div>
                  <?php if (!empty($team['adjoint_photo'])): ?>
                    <img src="<?= asset($team['adjoint_photo']) ?>" alt="Photo adjoint" class="coach-photo">
                  <?php else: ?>
                    <div class="coach-photo-placeholder">👤</div>
                  <?php endif; ?>
                  
                  <div class="coach-name">
                    <?= htmlspecialchars($team['adjoint_firstname']) ?><br>
                    <?= htmlspecialchars(strtoupper($team['adjoint_name'])) ?>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </section>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

