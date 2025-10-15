<?php
require_once __DIR__ . '/../../../includes/config.php';

// Sécurité
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: /es_moulon/BO/admin.php?section=login');
    exit;
}

$allowed_roles = ['ROLE_ADMIN'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: /es_moulon/BO/admin.php?section=dashboard');
    exit;
}

// ===== INFOS GÉNÉRALES =====
$stmt = $pdo->query("SELECT * FROM club_info LIMIT 1");
$club = $stmt->fetch(PDO::FETCH_ASSOC);

// ===== HORAIRES =====
$days = [
    'monday' => 'Lundi',
    'tuesday' => 'Mardi',
    'wednesday' => 'Mercredi',
    'thursday' => 'Jeudi',
    'friday' => 'Vendredi',
    'saturday' => 'Samedi',
    'sunday' => 'Dimanche',
];

// ===== DOCUMENTS =====
$stmt_docs = $pdo->query("
    SELECT d.*, m.file_name, m.file_path
    FROM club_documents d
    LEFT JOIN medias m ON d.id_media = m.id_media
    ORDER BY d.display_order ASC
");
$documents = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

// Sauvegarde infos générales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_info'])) {
    $stadium_name = trim($_POST['stadium_name']);
    $address = trim($_POST['address']);
    $google_maps_url = trim($_POST['google_maps_url']);
    $id_media = !empty($_POST['id_media']) ? (int)$_POST['id_media'] : null;

    if ($club) {
        $stmt = $pdo->prepare("UPDATE club_info SET stadium_name=?, address=?, google_maps_url=?, id_media=?, updated_at=NOW()");
        $stmt->execute([$stadium_name, $address, $google_maps_url, $id_media]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO club_info (stadium_name, address, google_maps_url, id_media) VALUES (?,?,?,?)");
        $stmt->execute([$stadium_name, $address, $google_maps_url, $id_media]);
    }

    $_SESSION['flash']['success'] = "Informations du club mises à jour.";
    header('Location: /es_moulon/BO/admin.php?section=club_info');
    exit;
}

// Sauvegarde horaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hours'])) {
    foreach ($days as $key => $label) {
        $open = $_POST[$key . '_open'] ?: null;
        $close = $_POST[$key . '_close'] ?: null;
        $pdo->prepare("UPDATE club_info SET {$key}_open=?, {$key}_close=?")->execute([$open, $close]);
    }
    $_SESSION['flash']['success'] = "Horaires mis à jour.";
    header('Location: /es_moulon/BO/admin.php?section=club_info');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🏟️ Infos du Club — Back Office</title>
    <link rel="stylesheet" href="<?= asset('_back.css/joueurs.css') ?>">
</head>
<body>
<div class="container">

    <div class="header">
        <div>
            <h1>🏟️ Informations du club</h1>
            <p><a href="/es_moulon/BO/admin.php?section=dashboard">← Retour</a></p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
            <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endforeach; unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- SECTION 1 : INFOS GÉNÉRALES -->
    <div class="card">
        <h2>🏟️ Informations générales</h2>
        <form method="POST">
            <input type="hidden" name="save_info" value="1">

            <div class="form-grid">
                <div class="form-group">
                    <label>Nom du stade</label>
                    <input type="text" name="stadium_name" value="<?= htmlspecialchars($club['stadium_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Adresse complète</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($club['address'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Lien Google Maps</label>
                    <input type="text" name="google_maps_url" value="<?= htmlspecialchars($club['google_maps_url'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Image du stade (via Médias)</label>
                    <select name="id_media">
                        <option value="">-- Aucune --</option>
                        <?php
                        $stmt_medias = $pdo->query("SELECT id_media, file_name FROM medias WHERE file_type LIKE 'image/%'");
                        foreach ($stmt_medias as $m):
                        ?>
                            <option value="<?= $m['id_media'] ?>" <?= ($club && $club['id_media'] == $m['id_media']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['file_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">💾 Enregistrer</button>
            </div>
        </form>
    </div>

    <!-- SECTION 2 : HORAIRES -->
    <div class="card">
        <h2>🕒 Horaires d’ouverture</h2>
        <form method="POST">
            <input type="hidden" name="save_hours" value="1">
            <table>
                <thead>
                    <tr><th>Jour</th><th>Ouverture</th><th>Fermeture</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $key => $label): ?>
                    <tr>
                        <td><strong><?= $label ?></strong></td>
                        <td><input type="time" name="<?= $key ?>_open" value="<?= htmlspecialchars($club[$key . '_open'] ?? '') ?>"></td>
                        <td><input type="time" name="<?= $key ?>_close" value="<?= htmlspecialchars($club[$key . '_close'] ?? '') ?>"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">💾 Enregistrer les horaires</button>
            </div>
        </form>
    </div>

    <!-- SECTION 3 : DOCUMENTS -->
    <div class="card">
        <h2>📄 Documents téléchargeables</h2>
        <p>Liste des fichiers liés au club (règlement, formulaires, etc.)</p>
        <table>
            <thead>
                <tr><th>Ordre</th><th>Titre</th><th>Fichier</th><th>Statut</th></tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr>
                    <td><?= $doc['display_order'] ?></td>
                    <td><?= htmlspecialchars($doc['title']) ?></td>
                    <td>
                        <?php if ($doc['file_path']): ?>
                            <a href="<?= asset($doc['file_path']) ?>" target="_blank"><?= htmlspecialchars($doc['file_name']) ?></a>
                        <?php else: ?>
                            <span style="color:#aaa;">Aucun</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $doc['is_active'] ? '✅ Actif' : '❌ Inactif' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
