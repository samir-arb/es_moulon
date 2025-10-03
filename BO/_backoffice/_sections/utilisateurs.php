<?php
session_start();
require '../../includes/config.php';

// Protection de la page - ADMIN UNIQUEMENT
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'ROLE_ADMIN') {
    $_SESSION['flash']['danger'] = "Accès réservé aux administrateurs.";
    header('Location: dashboard.php');
    exit;
}

// --- CHANGEMENT DE STATUT ---
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    
    // Ne pas se désactiver soi-même
    if ($id == $_SESSION['user_id']) {
        $_SESSION['flash']['warning'] = "Vous ne pouvez pas modifier votre propre statut.";
    } else {
        $sql = "UPDATE users SET status = 1 - status WHERE id_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = "Statut modifié avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de la modification.";
        }
        $stmt->close();
    }
    
    header('Location: utilisateurs.php');
    exit;
}

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Ne pas se supprimer soi-même
    if ($id == $_SESSION['user_id']) {
        $_SESSION['flash']['danger'] = "Vous ne pouvez pas vous supprimer vous-même.";
    } else {
        $sql = "DELETE FROM users WHERE id_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = "Utilisateur supprimé avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
        }
        $stmt->close();
    }
    
    header('Location: utilisateurs.php');
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $id = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $id_role = (int)$_POST['id_role'];
    $status = isset($_POST['status']) ? 1 : 0;
    $password = $_POST['password'];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || $id_role === 0) {
        $_SESSION['flash']['danger'] = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash']['danger'] = "Format d'email invalide.";
    } else {
        if ($id > 0) {
            // MODIFICATION
            if (!empty($password)) {
                // Avec changement de mot de passe
                $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET first_name = ?, name = ?, email = ?, password = ?, id_role = ?, status = ? WHERE id_user = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssssii', $first_name, $last_name, $email, $hashed_pwd, $id_role, $status, $id);
            } else {
                // Sans changement de mot de passe
                $sql = "UPDATE users SET first_name = ?, name = ?, email = ?, id_role = ?, status = ? WHERE id_user = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssiii', $first_name, $last_name, $email, $id_role, $status, $id);
            }
            $message = "Utilisateur modifié avec succès.";
        } else {
            // AJOUT
            if (empty($password)) {
                $_SESSION['flash']['danger'] = "Le mot de passe est obligatoire pour un nouvel utilisateur.";
                header('Location: utilisateurs.php');
                exit;
            }
            
            $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (first_name, name, email, password, id_role, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssii', $first_name, $last_name, $email, $hashed_pwd, $id_role, $status);
            $message = "Utilisateur créé avec succès.";
        }
        
        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = $message;
        } else {
            if ($stmt->errno === 1062) {
                $_SESSION['flash']['danger'] = "Cet email existe déjà.";
            } else {
                $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement.";
            }
        }
        $stmt->close();
    }
    
    header('Location: utilisateurs.php');
    exit;
}

// --- RÉCUPÉRATION POUR MODIFICATION ---
$edit_user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $sql = "SELECT * FROM users WHERE id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
}

// --- LISTE DES UTILISATEURS ---
$sql = "
    SELECT u.*, r.role_name
    FROM users u
    INNER JOIN roles r ON u.id_role = r.id_role
    ORDER BY u.status DESC, u.name
";
$result = $conn->query($sql);
$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Liste des rôles
$roles_list = [];
$roles_result = $conn->query("SELECT id_role, role_name FROM roles ORDER BY role_name");
if ($roles_result) {
    while ($row = $roles_result->fetch_assoc()) {
        $roles_list[] = $row;
    }
}

// Statistiques
$stats = [
    'total' => count($users),
    'actifs' => count(array_filter($users, function($u) { return $u['status'] == 1; })),
    'inactifs' => count(array_filter($users, function($u) { return $u['status'] == 0; }))
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - ES Moulon</title>
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
            font-size: 0.9rem;
        }
        .btn-primary {
            background: #1e40af;
            color: white;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
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
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e40af;
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 4px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
        }
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-wrapper input[type="checkbox"] {
            width: auto;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
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
        .badge-role {
            background: #dbeafe;
            color: #1e40af;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🛡️ Gestion des Utilisateurs</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="dashboard.php" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <?php if (!$edit_user): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouvel utilisateur
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

        <!-- STATISTIQUES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #10b981;"><?= $stats['actifs'] ?></div>
                <div class="stat-label">Actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #ef4444;"><?= $stats['inactifs'] ?></div>
                <div class="stat-label">Inactifs</div>
            </div>
        </div>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit_user ? '' : 'display:none;' ?>">
            <h2 style="margin-bottom: 20px; color: #1f2937;">
                <?= $edit_user ? '✏️ Modifier l\'utilisateur' : '➕ Nouvel utilisateur' ?>
            </h2>
            
            <form method="POST" action="utilisateurs.php">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="id_user" value="<?= $edit_user['id_user'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">Prénom *</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="<?= $edit_user ? htmlspecialchars($edit_user['first_name']) : '' ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="name">Nom *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?= $edit_user ? htmlspecialchars($edit_user['name']) : '' ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= $edit_user ? htmlspecialchars($edit_user['email']) : '' ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="id_role">Rôle *</label>
                        <select id="id_role" name="id_role" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($roles_list as $role): ?>
                                <option value="<?= $role['id_role'] ?>" 
                                    <?= ($edit_user && $edit_user['id_role'] == $role['id_role']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            Mot de passe <?= $edit_user ? '(laisser vide pour ne pas changer)' : '*' ?>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="<?= $edit_user ? 'Ne pas changer' : 'Minimum 6 caractères' ?>"
                            <?= $edit_user ? '' : 'required' ?>
                            minlength="6">
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="checkbox-wrapper">
                            <input 
                                type="checkbox" 
                                id="status" 
                                name="status" 
                                <?= ($edit_user && $edit_user['status'] == 1) || !$edit_user ? 'checked' : '' ?>>
                            <label for="status" style="margin: 0; font-weight: normal;">Compte actif</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_user" class="btn btn-success">
                        💾 Enregistrer
                    </button>
                    <a href="utilisateurs.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- LISTE -->
        <div class="card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">Liste des utilisateurs (<?= count($users) ?>)</h2>
            
            <?php if (empty($users)): ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">Aucun utilisateur.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Date création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id_user'] ?></td>
                                    <td style="font-weight: 600;">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['name']) ?>
                                        <?php if ($user['id_user'] == $_SESSION['user_id']): ?>
                                            <span style="color: #1e40af; font-size: 0.85rem;">(Vous)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge badge-role">
                                            <?= htmlspecialchars($user['role_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] == 1): ?>
                                            <span class="badge badge-active">✓ Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">✗ Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="utilisateurs.php?edit=<?= $user['id_user'] ?>" 
                                               class="btn btn-warning">
                                                ✏️
                                            </a>
                                            <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                                <a href="utilisateurs.php?toggle_status=<?= $user['id_user'] ?>" 
                                                   class="btn <?= $user['status'] == 1 ? 'btn-secondary' : 'btn-success' ?>"
                                                   title="<?= $user['status'] == 1 ? 'Désactiver' : 'Activer' ?>">
                                                    <?= $user['status'] == 1 ? '⏸️' : '▶️' ?>
                                                </a>
                                                <a href="utilisateurs.php?delete=<?= $user['id_user'] ?>" 
                                                   class="btn btn-danger" 
                                                   onclick="return confirm('Confirmer la suppression de <?= htmlspecialchars($user['first_name'] . ' ' . $user['name']) ?> ?')">
                                                    🗑️
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
