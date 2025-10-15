<?php
require_once __DIR__ . '/../../../includes/config.php';

/* --------- Sécurité --------- */
if (!isset($_SESSION['user_id']) || !($_SESSION['logged_in'] ?? false)) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: ../auth/login.php');
    exit;
}
$allowed_roles = ['ROLE_ADMIN','ROLE_SPORT_MANAGER','ROLE_COATCH'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Accès réservé aux administrateurs et responsables.";
    header('Location: /es_moulon/BO/admin.php?section=dashboard');
    exit;
}
$user_role = $_SESSION['role'];

/* --------- Suppression --------- */
if (isset($_GET['delete']) && ctype_digit($_GET['delete']) && $user_role === 'ROLE_ADMIN') {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users_club_functions WHERE id_user_club_function = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) $_SESSION['flash']['success'] = "Membre supprimé avec succès.";
    else $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    $stmt->close();
    header('Location: /es_moulon/BO/admin.php?section=staff'); exit;
}

/* --------- Ajout / Modification --------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_staff'])) {
    $id_ucf     = isset($_POST['id_user_club_function']) ? (int)$_POST['id_user_club_function'] : 0;
    $first_name = trim($_POST['first_name']);
    $name       = trim($_POST['name']);
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $id_team    = !empty($_POST['id_team']) ? (int)$_POST['id_team'] : null;
    $id_func    = (int)$_POST['id_club_function'];
    $id_media   = !empty($_POST['id_media']) ? (int)$_POST['id_media'] : null;

    if ($first_name === '' || $name === '' || $id_func === 0) {
        $_SESSION['flash']['danger'] = "Prénom, nom et fonction sont obligatoires.";
    } else {
        if ($id_ucf > 0) {
            $sql = "UPDATE users u
                      JOIN users_club_functions ucf ON u.id_user = ucf.id_user
                      SET u.first_name=?, u.name=?, u.birth_date=?, u.id_media=?,
                          ucf.id_team=?, ucf.id_club_function=?
                    WHERE ucf.id_user_club_function=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssiiii', $first_name, $name, $birth_date, $id_media, $id_team, $id_func, $id_ucf);
            if ($stmt->execute()) $_SESSION['flash']['success'] = "Membre modifié avec succès.";
            else $_SESSION['flash']['danger'] = "Erreur lors de la modification : ".$stmt->error;
            $stmt->close();
        } else {
            // création user
            $id_role = 4; $has_bo = 0;
            $stmtUser = $conn->prepare("INSERT INTO users(first_name, name, birth_date, id_role, has_backoffice_access, id_media) VALUES(?,?,?,?,?,?)");
            $stmtUser->bind_param('sssiii', $first_name, $name, $birth_date, $id_role, $has_bo, $id_media);
            if ($stmtUser->execute()) {
                $id_user = $stmtUser->insert_id;
                $stmtUser->close();
                // saison active
                $season_row = $conn->query("SELECT id_season FROM seasons WHERE is_active=1 LIMIT 1")->fetch_assoc();
                $id_season  = $season_row['id_season'] ?? null;
                // liaison
                $stmtL = $conn->prepare("INSERT INTO users_club_functions(id_user,id_team,id_club_function,id_season,start_date) VALUES(?,?,?,?,NOW())");
                $stmtL->bind_param('iiii', $id_user, $id_team, $id_func, $id_season);
                if ($stmtL->execute()) $_SESSION['flash']['success'] = "Membre ajouté avec succès.";
                else $_SESSION['flash']['danger'] = "Erreur lors de la liaison : ".$stmtL->error;
                $stmtL->close();
            } else {
                $_SESSION['flash']['danger'] = "Erreur lors de la création de l’utilisateur : ".$stmtUser->error;
            }
        }
    }
    header('Location: /es_moulon/BO/admin.php?section=staff'); exit;
}

/* --------- Récup pour édition --------- */
$edit_staff = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT ucf.*, u.first_name, u.name, u.birth_date, u.id_media,
                                   t.name AS team_name, cf.function_name, cf.function_type,
                                   m.file_path, m.file_name
                              FROM users_club_functions ucf
                              JOIN users u ON ucf.id_user = u.id_user
                         LEFT JOIN teams t ON ucf.id_team = t.id_team
                              JOIN club_functions cf ON ucf.id_club_function = cf.id_club_function
                         LEFT JOIN medias m ON u.id_media = m.id_media
                             WHERE ucf.id_user_club_function=?");
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $edit_staff = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

/* --------- Listes --------- */
$staff_sportif = [];
$res = $conn->query("SELECT ucf.id_user_club_function, u.first_name, u.name, u.birth_date,
                            t.name AS team_name, t.level AS team_level,
                            cf.function_name, cf.function_type,
                            m.file_path AS photo
                       FROM users_club_functions ucf
                       JOIN users u ON ucf.id_user=u.id_user
                       JOIN club_functions cf ON ucf.id_club_function=cf.id_club_function
                  LEFT JOIN teams t ON ucf.id_team=t.id_team
                  LEFT JOIN medias m ON u.id_media=m.id_media
                      WHERE cf.function_type='sportif' AND cf.function_name <> 'Joueur'
                   ORDER BY cf.ordre_affichage, t.name, u.name");
if ($res) while ($row = $res->fetch_assoc()) $staff_sportif[] = $row;

$staff_admin = [];
$res = $conn->query("SELECT ucf.id_user_club_function, u.first_name, u.name, u.birth_date,
                            cf.function_name, cf.function_type,
                            m.file_path AS photo
                       FROM users_club_functions ucf
                       JOIN users u ON ucf.id_user=u.id_user
                       JOIN club_functions cf ON ucf.id_club_function=cf.id_club_function
                  LEFT JOIN medias m ON u.id_media=m.id_media
                      WHERE cf.function_type='administratif'
                   ORDER BY cf.ordre_affichage, u.name");
if ($res) while ($row = $res->fetch_assoc()) $staff_admin[] = $row;

$teams_list = [];
$res = $conn->query("SELECT t.id_team, t.name, t.level, c.name AS category
                       FROM teams t JOIN categories c ON t.id_category=c.id_category
                   ORDER BY c.name, t.name");
if ($res) while ($row = $res->fetch_assoc()) $teams_list[] = $row;

$functions_sportif = $functions_admin = [];
$res = $conn->query("SELECT id_club_function, function_name, function_type
                       FROM club_functions
                      WHERE function_name <> 'Joueur'
                   ORDER BY function_type, function_name");
if ($res) while ($row = $res->fetch_assoc()) {
    if ($row['function_type'] === 'sportif') $functions_sportif[] = $row; else $functions_admin[] = $row;
}

$medias_list = [];
$res = $conn->query("SELECT id_media, file_name, file_path FROM medias WHERE file_type LIKE 'image/%' ORDER BY uploaded_at DESC");
if ($res) while ($m = $res->fetch_assoc()) $medias_list[] = $m;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion du Staff & Administration - ES Moulon</title>
  <link rel="stylesheet" href="<?= asset('_back.css/staff.css') ?>">
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <h1>👔 Staff & Administration</h1>
      <p><a href="/es_moulon/BO/admin.php?section=dashboard">← Retour au dashboard</a></p>
    </div>
    <?php if (!$edit_staff): ?>
      <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">➕ Ajouter un membre</button>
    <?php endif; ?>
  </div>

  <?php if (isset($_SESSION['flash'])): foreach ($_SESSION['flash'] as $t=>$m): ?>
    <div class="alert alert-<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; unset($_SESSION['flash']); endif; ?>

  <!-- FORM -->
  <div class="card" id="formSection" style="<?= $edit_staff ? '' : 'display:none;' ?>">
    <h2><?= $edit_staff ? '✏️ Modifier le membre' : '➕ Nouveau membre' ?></h2>

    <form method="POST" action="/es_moulon/BO/admin.php?section=staff">
      <?php if ($edit_staff): ?>
        <input type="hidden" name="id_user_club_function" value="<?= (int)$edit_staff['id_user_club_function'] ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-group">
          <label for="first_name">Prénom *</label>
          <input type="text" id="first_name" name="first_name" value="<?= $edit_staff ? htmlspecialchars($edit_staff['first_name']) : '' ?>" required>
        </div>

        <div class="form-group">
          <label for="name">Nom *</label>
          <input type="text" id="name" name="name" value="<?= $edit_staff ? htmlspecialchars($edit_staff['name']) : '' ?>" required>
        </div>

        <div class="form-group">
          <label for="birth_date">Date de naissance</label>
          <input type="date" id="birth_date" name="birth_date" value="<?= $edit_staff['birth_date'] ?? '' ?>">
        </div>

        <div class="form-group">
          <label for="id_club_function">Fonction *</label>
          <select id="id_club_function" name="id_club_function" required onchange="toggleTeamField(this)">
            <option value="">-- Sélectionner --</option>
            <optgroup label="Staff Sportif">
              <?php foreach ($functions_sportif as $f): ?>
                <option value="<?= $f['id_club_function'] ?>" data-type="sportif" <?= ($edit_staff && $edit_staff['id_club_function']==$f['id_club_function'])?'selected':''; ?>>
                  <?= htmlspecialchars($f['function_name']) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
            <optgroup label="Bureau & Administration">
              <?php foreach ($functions_admin as $f): ?>
                <option value="<?= $f['id_club_function'] ?>" data-type="administratif" <?= ($edit_staff && $edit_staff['id_club_function']==$f['id_club_function'])?'selected':''; ?>>
                  <?= htmlspecialchars($f['function_name']) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          </select>
        </div>

        <div class="form-group" id="team-field" style="<?= ($edit_staff && $edit_staff['function_type']==='sportif')?'':'display:none;' ?>">
          <label for="id_team">Équipe</label>
          <select id="id_team" name="id_team">
            <option value="">-- Aucune équipe --</option>
            <?php foreach ($teams_list as $t): ?>
              <option value="<?= $t['id_team'] ?>" <?= ($edit_staff && $edit_staff['id_team']==$t['id_team'])?'selected':''; ?>>
                <?= htmlspecialchars($t['category'].' - '.$t['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- PHOTO -->
        <div class="form-group">
          <label for="id_media">Photo</label>
          <select id="id_media" name="id_media">
            <option value="">-- Aucune photo --</option>
            <?php foreach ($medias_list as $m): ?>
              <option
                value="<?= $m['id_media'] ?>"
                data-image="<?= asset($m['file_path']) ?>"
                <?= ($edit_staff && (int)$edit_staff['id_media'] === (int)$m['id_media']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['file_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small style="color:#6b7280;display:block;margin-top:4px;">
            Pas de photo ? <a href="#" onclick="openMediaPopup();return false;" style="color:#1e40af;font-weight:600;">Uploadez-en une ici</a>
          </small>

          <div id="imagePreview" style="margin-top:12px;">
            <?php if ($edit_staff && !empty($edit_staff['file_path'])): ?>
              <img src="<?= asset($edit_staff['file_path']) ?>" alt="Aperçu"
                   style="max-width:150px;max-height:150px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);object-fit:cover;">
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" name="save_staff" class="btn btn-success">💾 Enregistrer</button>
        <a href="/es_moulon/BO/admin.php?section=staff" class="btn btn-secondary">Annuler</a>
      </div>
    </form>
  </div>

  <!-- ONGLETS -->
  <div class="tabs">
    <button class="tab active" onclick="showTab(event,'sportif')">⚽ Staff Sportif (<?= count($staff_sportif) ?>)</button>
    <button class="tab" onclick="showTab(event,'admin')">🏛️ Bureau & Administration (<?= count($staff_admin) ?>)</button>
  </div>

  <!-- STAFF SPORTIF -->
  <div id="sportif-tab" class="tab-content active">
    <div class="card">
      <?php if (empty($staff_sportif)): ?>
        <p class="no-data">Aucun membre du staff sportif pour le moment.</p>
      <?php else:
        $by_function = [];
        foreach ($staff_sportif as $m) $by_function[$m['function_name']][] = $m;
      ?>
        <div class="staff-grid">
          <?php foreach ($by_function as $fn => $members): ?>
            <div class="function-block">
              <h3><?= htmlspecialchars($fn) ?>s <span class="member-count"><?= count($members) ?></span></h3>
              <div class="members-grid">
                <?php foreach ($members as $m):
                  $age = $m['birth_date'] ? (new DateTime())->diff(new DateTime($m['birth_date']))->y : null;
                ?>
                  <div class="member-card">
                    <div class="member-photo">
                      <?php if (!empty($m['photo'])): ?>
                        <img src="<?= asset($m['photo']) ?>" alt="<?= htmlspecialchars($m['first_name'].' '.$m['name']) ?>">
                      <?php else: ?>
                        <div class="member-initials"><?= strtoupper($m['first_name'][0].$m['name'][0]) ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="member-info">
                      <div class="member-name"><?= htmlspecialchars($m['first_name'].' '.$m['name']) ?></div>
                      <?php if (!empty($m['team_name'])): ?>
                        <div class="member-team">📋 <?= htmlspecialchars(($m['team_level'] ?? '').' - '.$m['team_name']) ?></div>
                      <?php endif; ?>
                      <?php if ($age): ?><div class="member-age"><?= $age ?> ans</div><?php endif; ?>
                      <div class="member-actions">
                        <a href="/es_moulon/BO/admin.php?section=staff&edit=<?= $m['id_user_club_function'] ?>" class="btn btn-warning btn-small">✏️</a>
                        <?php if ($user_role === 'ROLE_ADMIN'): ?>
                          <a href="/es_moulon/BO/admin.php?section=staff&delete=<?= $m['id_user_club_function'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Supprimer <?= htmlspecialchars($m['first_name'].' '.$m['name']) ?> ?')">🗑️</a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- BUREAU ADMIN -->
  <div id="admin-tab" class="tab-content">
    <div class="card">
      <?php if (empty($staff_admin)): ?>
        <p class="no-data">Aucun membre du bureau pour le moment.</p>
      <?php else: ?>
        <div class="admin-grid">
          <?php foreach ($staff_admin as $m):
            $age = $m['birth_date'] ? (new DateTime())->diff(new DateTime($m['birth_date']))->y : null;
          ?>
            <div class="admin-card">
              <div class="admin-photo">
                <?php if (!empty($m['photo'])): ?>
                  <img src="<?= asset($m['photo']) ?>" alt="<?= htmlspecialchars($m['first_name'].' '.$m['name']) ?>">
                <?php else: ?>
                  <div class="admin-initials"><?= strtoupper($m['first_name'][0].$m['name'][0]) ?></div>
                <?php endif; ?>
              </div>
              <div class="admin-info">
                <div class="admin-name"><?= htmlspecialchars($m['first_name'].' '.$m['name']) ?></div>
                <div class="admin-function">📋 <?= htmlspecialchars($m['function_name']) ?></div>
                <?php if ($age): ?><div class="admin-age"><?= $age ?> ans</div><?php endif; ?>
                <div class="admin-actions">
                  <a href="/es_moulon/BO/admin.php?section=staff&edit=<?= $m['id_user_club_function'] ?>" class="btn btn-warning">✏️</a>
                  <?php if ($user_role === 'ROLE_ADMIN'): ?>
                    <a href="/es_moulon/BO/admin.php?section=staff&delete=<?= $m['id_user_club_function'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer <?= htmlspecialchars($m['first_name'].' '.$m['name']) ?> ?')">🗑️</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============== JS (unique, propre) ============== -->
<script>
(function () {
  const $ = (sel, ctx=document) => ctx.querySelector(sel);

  // Tabs
  window.showTab = function (ev, tab) {
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    ev.currentTarget.classList.add('active');
    document.getElementById(tab + '-tab').classList.add('active');
  };

  // Afficher / cacher le champ équipe selon la fonction
  window.toggleTeamField = function (select) {
    const type = select.options[select.selectedIndex]?.dataset?.type;
    const field = $('#team-field');
    if (type === 'sportif') field.style.display = 'block';
    else { field.style.display = 'none'; const team = $('#id_team'); if (team) team.value = ''; }
  };

  // Aperçu image instantané (utilise l’URL absolue dans data-image)
  function previewImageFromSelect() {
    const sel = $('#id_media');
    const preview = $('#imagePreview');
    if (!sel || !preview) return;
    const url = sel.options[sel.selectedIndex]?.dataset?.image || '';
    preview.innerHTML = url
      ? `<img src="${url}" alt="Aperçu"
              style="max-width:150px;max-height:150px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);object-fit:cover;">`
      : '';
  }

  // Popup Médias -> recharge parent à la fermeture
  window.openMediaPopup = function () {
    const popup = window.open('/es_moulon/BO/admin.php?section=medias', 'uploadWindow', 'width=900,height=600');
    const tick = setInterval(() => {
      if (popup.closed) { clearInterval(tick); location.reload(); }
    }, 500);
  };

  document.addEventListener('DOMContentLoaded', () => {
    // init preview si une image est déjà sélectionnée
    previewImageFromSelect();
    const mediaSelect = $('#id_media');
    if (mediaSelect) mediaSelect.addEventListener('change', previewImageFromSelect);

    // init champ équipe si une fonction est déjà choisie
    const funcSelect = $('#id_club_function');
    if (funcSelect && funcSelect.value) toggleTeamField(funcSelect);
  });
})();
</script>
</body>
</html>
