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
    WHERE c.name IN ('seniors', 'veterans')
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

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>École de Foot - ES Moulon</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
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

    /* SECTION RESPONSABLE */
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
      font-size: 1.1em;
      font-weight: 600;
      color: #666;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .team-name {
      font-size: 2.5em;
      font-weight: 800;
      color: var(--green-primary);
      margin-bottom: 8px;
      text-transform: uppercase;
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
</head>

<body>
  <!-- HEADER RESPONSABLE -->
  <section class="responsable-header">
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
          "À l'ES Moulon, l'école de foot occupe une place centrale dans notre projet sportif et éducatif.
          Notre objectif est clair : offrir à chaque enfant un cadre structuré, bienveillant et stimulant
          pour découvrir le football tout en s'amusant.
          Encadrés par une équipe d'éducateurs passionnés, nos jeunes licenciés bénéficient de 1 à 2 séances
          d'entraînement par semaine, complétées par des plateaux le week-end.
          Ces moments permettent d'acquérir les bases essentielles du jeu à travers des matchs adaptés à leur âge."
        </p>

        <p class="responsable-quote">
          "Dès la catégorie U8/U9, l'approche devient plus approfondie : on y découvre les premières exigences
          techniques et tactiques, tout en renforçant les valeurs humaines indispensables à la pratique du football —
          respect, entraide, engagement. Plus qu'une école de football, c'est une école de la vie que nous proposons aux enfants."
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

</body>
</html>