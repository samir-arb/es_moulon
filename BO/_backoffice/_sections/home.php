<?php
global $conn;
$res = $conn->query("SELECT * FROM home_blocks LIMIT 1");
$home = $res->fetch_assoc();
?>

<h2>Bloc Accueil</h2>
<form method="post" enctype="multipart/form-data">
    <label>Titre</label>
    <input type="text" name="title" value="<?= htmlspecialchars($home['title'] ?? '') ?>">

    <label>Sous-titre</label>
    <input type="text" name="subtitle" value="<?= htmlspecialchars($home['subtitle'] ?? '') ?>">

    <label>Texte</label>
    <textarea name="content"><?= htmlspecialchars($home['content'] ?? '') ?></textarea>

    <label>Image</label>
    <input type="file" name="image">

    <button type="submit">💾 Sauvegarder</button>
</form>
