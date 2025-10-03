<?php


// Protection de la page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: login.php');
    exit;
}

// Vérification des permissions
$allowed_roles = ['ROLE_ADMIN', 'ROLE_EDITOR', 'ROLE_MODERATOR'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header('Location: dashboard.php');
    exit;
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Vérifier si l'utilisateur a le droit de supprimer
    if ($user_role === 'ROLE_ADMIN' || $user_role === 'ROLE_MODERATOR') {
        $sql = "DELETE FROM news WHERE id_news = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = "Article supprimé avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
        }
        $stmt->close();
    } else {
        $_SESSION['flash']['danger'] = "Vous n'avez pas la permission de supprimer.";
    }
    
    header('Location: ../dashboard.php?section=news');
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_article'])) {
    $id = isset($_POST['id_news']) ? (int)$_POST['id_news'] : 0;
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    
    // Validation
    if (empty($title) || empty($content)) {
        $_SESSION['flash']['danger'] = "Le titre et le contenu sont obligatoires.";
    } else {
        if ($id > 0) {
            // MODIFICATION
            $sql = "UPDATE news SET title = ?, content = ?, status = ?, updated_at = NOW() WHERE id_news = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssii', $title, $content, $status, $id);
            $message = "Article modifié avec succès.";
        } else {
            // AJOUT
            $sql = "INSERT INTO news (title, content, status, id_user, published_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssii', $title, $content, $status, $user_id);
            $message = "Article ajouté avec succès.";
        }
        
        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $message;
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement.";
        }
        $stmt->close();
    }
    
    header('Location: news.php');
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
$edit_article = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "SELECT * FROM news WHERE id_news = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_article = $result->fetch_assoc();
    $stmt->close();
}

// --- LISTE DES ARTICLES ---
$sql = "
    SELECT n.*, u.first_name, u.name 
    FROM news n
    LEFT JOIN users u ON n.id_user = u.id_user
    ORDER BY n.published_at DESC
";
$result = $conn->query($sql);
$articles = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Articles - ES Moulon</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #1f2937;
            font-size: 1.5rem;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #1e40af;
            color: white;
        }
        .btn-primary:hover {
            background: #1e3a8a;
        }
        .btn-success {
            background: #10b981;
            color: white;
            font-size: 0.9rem;
            padding: 8px 16px;
        }
        .btn-warning {
            background: #f59e0b;
            color: white;
            font-size: 0.9rem;
            padding: 8px 16px;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
            font-size: 0.9rem;
            padding: 8px 16px;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1e40af;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }
        tr:hover {
            background: #f9fafb;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📄 Gestion des Articles</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="dashboard.php" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <?php if (!$edit_article): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouvel article
                </button>
            <?php endif; ?>
        </div>

        <?php
        // Messages flash
        if (isset($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $type => $message) {
                echo '<div class="alert alert-' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
            }
            unset($_SESSION['flash']);
        }
        ?>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit_article ? '' : 'display:none;' ?>">
            <h2 style="margin-bottom: 20px; color: #1f2937;">
                <?= $edit_article ? '✏️ Modifier l\'article' : '➕ Nouvel article' ?>
            </h2>
            
            <form method="POST" action="news.php">
                <?php if ($edit_article): ?>
                    <input type="hidden" name="id_news" value="<?= $edit_article['id_news'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Titre de l'article *</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="<?= $edit_article ? htmlspecialchars($edit_article['title']) : '' ?>" 
                        required>
                </div>

                <div class="form-group">
                    <label for="content">Contenu *</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        required><?= $edit_article ? htmlspecialchars($edit_article['content']) : '' ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="1" <?= ($edit_article && $edit_article['status'] == 1) ? 'selected' : '' ?>>Publié</option>
                        <option value="0" <?= ($edit_article && $edit_article['status'] == 0) ? 'selected' : '' ?>>Brouillon</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_article" class="btn btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="../dashboard.php?section=news" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- LISTE -->
        <div class="card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">Liste des articles (<?= count($articles) ?>)</h2>
            
            <?php if (empty($articles)): ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">Aucun article pour le moment.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td><?= $article['id_news'] ?></td>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($article['title']) ?></td>
                                    <td><?= htmlspecialchars($article['first_name'] . ' ' . $article['name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($article['published_at'])) ?></td>
                                    <td>
                                        <?php if ($article['status'] == 1): ?>
                                            <span class="badge badge-active">Publié</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">Brouillon</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="../dashboard.php?edit=<?= $article['id_news'] ?>" class="btn btn-warning">✏️ Modifier</a>
                                            <?php if ($user_role === 'ROLE_ADMIN' || $user_role === 'ROLE_MODERATOR'): ?>
                                                <a href="../dashboard.php?delete=<?= $article['id_news'] ?>" 
                                                   class="btn btn-danger" 
                                                   onclick="return confirm('Confirmer la suppression ?')">
                                                    🗑️ Supprimer
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
