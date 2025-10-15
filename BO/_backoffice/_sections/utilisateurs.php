<?php
require_once __DIR__ . '/../../../includes/config.php';

// Protection de la page - ADMIN UNIQUEMENT
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'ROLE_ADMIN') {
    $_SESSION['flash']['danger'] = "Accès réservé aux administrateurs.";
    header('Location: /es_moulon/BO/admin.php?section=dashboard');
    exit;
}

// --- CHANGEMENT DE STATUT ---
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];

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

    header('Location: /es_moulon/BO/admin.php?section=utilisateurs');
    exit;
}

// --- DONNER/RETIRER ACCÈS BACK-OFFICE ---
if (isset($_GET['toggle_bo_access']) && is_numeric($_GET['toggle_bo_access'])) {
    $id = (int)$_GET['toggle_bo_access'];

    if ($id == $_SESSION['user_id']) {
        $_SESSION['flash']['warning'] = "Vous ne pouvez pas modifier votre propre accès.";
    } else {
        $sql = "UPDATE users SET has_backoffice_access = 1 - has_backoffice_access WHERE id_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $_SESSION['flash']['success'] = "Accès back-office modifié.";
        } else {
            $_SESSION['flash']['danger'] = "Erreur lors de la modification.";
        }
        $stmt->close();
    }

    echo "<script>window.location.href='/es_moulon/BO/admin.php?section=utilisateurs';</script>";
    exit;
}

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($id == $_SESSION['user_id']) {
        $_SESSION['flash']['danger'] = "Vous ne pouvez pas vous supprimer vous-même.";
    } else {
        // Vérifier s'il y a des dépendances
        $check = $conn->prepare("SELECT COUNT(*) as nb FROM users_club_functions WHERE id_user = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();
        $check->close();

        if ($res['nb'] > 0) {
            $_SESSION['flash']['warning'] = "Impossible de supprimer : cet utilisateur a des fonctions dans le club.";
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
    }

    header('Location: admin.php?section=utilisateurs');
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
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || $id_role === 0) {
        $_SESSION['flash']['danger'] = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash']['danger'] = "Format d'email invalide.";
    } else {
        if ($id > 0) {
            // MODIFICATION (sans toucher au mot de passe)
            $sql = "UPDATE users SET first_name = ?, name = ?, email = ?, id_role = ?, status = ? WHERE id_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssiii', $first_name, $last_name, $email, $id_role, $status, $id);
            $message = "Utilisateur modifié avec succès.";
        } else {
            // Vérification si l'email existe déjà
            $check = $conn->prepare("SELECT id_user FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $_SESSION['flash']['danger'] = "❌ Cet email existe déjà, veuillez en choisir un autre.";
                $check->close();
                header('Location: /es_moulon/BO/admin.php?section=utilisateurs');
                exit;
            }
            $check->close();

            // AJOUT (sans mot de passe)
            $reset_token = bin2hex(random_bytes(32));
            $reset_token_expiry = date('Y-m-d H:i:s', strtotime('+48 hours')); // Valide 48h
            
            $sql = "INSERT INTO users (first_name, name, email, password, id_role, status, reset_token, reset_token_expiry, is_password_set) VALUES (?, ?, ?, NULL, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssiiss', $first_name, $last_name, $email, $id_role, $status, $reset_token, $reset_token_expiry);
            
            if ($stmt->execute()) {
                // Envoyer l'email d'invitation
                $new_user_id = $conn->insert_id;
                $activation_link = "http://" . $_SERVER['HTTP_HOST'] . "/BO/backoffice/create-password.php?token=" . $reset_token;
                
                // Email basique (à améliorer avec PHPMailer)
                $subject = "Bienvenue sur ES Moulon - Créez votre mot de passe";
                $message_email = "Bonjour $first_name $last_name,\n\n";
                $message_email .= "Votre compte a été créé sur le back-office ES Moulon.\n\n";
                $message_email .= "Cliquez sur ce lien pour créer votre mot de passe :\n";
                $message_email .= $activation_link . "\n\n";
                $message_email .= "Ce lien est valide pendant 48 heures.\n\n";
                $message_email .= "Cordialement,\nL'équipe ES Moulon";
                
                $headers = "From: noreply@esmoulon.fr\r\n";
                $headers .= "Reply-To: admin@esmoulon.fr\r\n";
                
                if (mail($email, $subject, $message_email, $headers)) {
                    $_SESSION['flash']['success'] = "Utilisateur créé ! Un email d'invitation a été envoyé à " . $email;
                } else {
                    $_SESSION['flash']['warning'] = "Utilisateur créé mais l'email n'a pas pu être envoyé. Lien d'activation : " . $activation_link;
                }
                
                $stmt->close();
                header('Location: utilisateurs.php');
                exit;
            }
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

    header('Location: /es_moulon/BO/admin.php?section=utilisateurs');
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

// Filtre : tous les utilisateurs ou seulement ceux avec accès BO
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'backoffice';

// --- LISTE DES UTILISATEURS ---
if ($filter === 'all') {
    // Tous les utilisateurs
    $sql = "SELECT u.*, r.role_name
            FROM users u
            INNER JOIN roles r ON u.id_role = r.id_role
            ORDER BY u.has_backoffice_access DESC, u.status DESC, u.name";
} else {
    // Uniquement ceux avec accès back-office
    $sql = "SELECT u.*, r.role_name
            FROM users u
            INNER JOIN roles r ON u.id_role = r.id_role
            WHERE u.has_backoffice_access = 1
            ORDER BY u.status DESC, u.name";
}

$result = $conn->query($sql);
$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Liste des rôles (seulement ceux pour le back-office)
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
    'actifs' => count(array_filter($users, fn($u) => $u['status'] == 1)),
    'inactifs' => count(array_filter($users, fn($u) => $u['status'] == 0)),
    'bo_access' => count(array_filter($users, fn($u) => $u['has_backoffice_access'] == 1))
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= asset('_back.css/utilisateurs.css') ?>">
    <title>Gestion des Utilisateurs - ES Moulon</title>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🛡️ Gestion des Utilisateurs Back-Office</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="/es_moulon/BO/admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <?php if (!$edit_user): ?>
                <button class="btn btn-primary" onclick="document.getElementById('formSection').style.display='block'; window.scrollTo(0,0);">
                    ➕ Nouvel utilisateur
                </button>
            <?php endif; ?>
        </div>

        <?php
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
            <div class="stat-card">
                <div class="stat-value" style="color: #1e40af;"><?= $stats['bo_access'] ?></div>
                <div class="stat-label">Accès BO</div>
            </div>
        </div>

        <!-- FILTRES ET RECHERCHE -->
        <div class="filter-section">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; gap: 8px; align-items: center;">
                    <strong>Afficher :</strong>
                    <a href="/es_moulon/BO/admin.php?section=utilisateurs&filter=backoffice" 
                       class="btn <?= $filter === 'backoffice' ? 'btn-primary' : 'btn-secondary' ?>" 
                       style="padding: 8px 16px; font-size: 0.85rem;">
                        Accès Back-Office
                    </a>
                    <a href="/es_moulon/BO/admin.php?section=utilisateurs&filter=all" 
                       class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>" 
                       style="padding: 8px 16px; font-size: 0.85rem;">
                        Tous les licenciés
                    </a>
                </div>
                
                <div style="flex: 1; max-width: 400px;">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="🔍 Rechercher par nom, prénom ou email..." 
                           style="width: 100%; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.95rem;">
                </div>
            </div>
        </div>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit_user ? '' : 'display:none;' ?>">
            <h2 style="margin-bottom: 20px; color: #1f2937;">
                <?= $edit_user ? '✏️ Modifier l\'utilisateur' : '➕ Nouvel utilisateur' ?>
            </h2>

            <form method="POST" action="/es_moulon/BO/admin.php?section=utilisateurs">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="id_user" value="<?= $edit_user['id_user'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">Prénom *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?= $edit_user ? htmlspecialchars($edit_user['first_name']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Nom *</label>
                        <input type="text" id="name" name="name" 
                               value="<?= $edit_user ? htmlspecialchars($edit_user['name']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" 
                               value="<?= $edit_user ? htmlspecialchars($edit_user['email']) : '' ?>" required>
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

                    <?php if ($edit_user): ?>
                        <!-- En mode édition : option pour réinitialiser le mot de passe -->
                        <div class="form-group full-width">
                            <label style="display: block; padding: 16px; background: #eff6ff; border-left: 4px solid #1e40af; border-radius: 8px;">
                                <strong>🔐 Gestion du mot de passe :</strong><br>
                                <span style="color: #6b7280; font-size: 0.9rem; font-weight: normal;">
                                    Pour réinitialiser le mot de passe de cet utilisateur, utilisez le bouton "Envoyer un lien de réinitialisation" ci-dessous.
                                </span>
                            </label>
                        </div>
                    <?php else: ?>
                        <!-- En mode création : pas de champ mot de passe -->
                        <div class="form-group full-width">
                            <label style="display: block; padding: 16px; background: #d1fae5; border-left: 4px solid #10b981; border-radius: 8px;">
                                <strong>✉️ Email d'invitation :</strong><br>
                                <span style="color: #065f46; font-size: 0.9rem; font-weight: normal;">
                                    Un email sera automatiquement envoyé à l'utilisateur avec un lien pour créer son mot de passe.
                                </span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #f3f4f6;">
                    <div class="checkbox-wrapper" style="margin-bottom: 12px;">
                        <input type="checkbox" id="status" name="status"
                               <?= ($edit_user && $edit_user['status'] == 1) || !$edit_user ? 'checked' : '' ?>>
                        <label for="status" style="margin: 0; font-weight: normal;">Compte actif</label>
                    </div>

                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="has_backoffice_access" name="has_backoffice_access"
                               <?= ($edit_user && $edit_user['has_backoffice_access'] == 1) || !$edit_user ? 'checked' : '' ?>>
                        <label for="has_backoffice_access" style="margin: 0; font-weight: normal;">
                            Accès au back-office
                        </label>
                        <small style="display: block; color: #6b7280; margin-top: 4px;">
                            Si décoché, l'utilisateur sera un simple licencié sans accès au dashboard
                        </small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_user" class="btn btn-success">💾 Enregistrer</button>
                    <a href="/es_moulon/BO/admin.php?section=utilisateurs" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- LISTE DES UTILISATEURS -->
        <div class="card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">
                <?= $filter === 'all' ? 'Tous les licenciés' : 'Utilisateurs avec accès back-office' ?> 
                (<?= count($users) ?>)
            </h2>

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
                                <th>Accès BO</th>
                                <th>Statut</th>
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
                                        <?php if ($user['has_backoffice_access'] == 1): ?>
                                            <span class="badge badge-active">✓ Oui</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">✗ Non</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] == 1): ?>
                                            <span class="badge badge-active">✓ Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">✗ Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="/es_moulon/BO/admin.php?section=utilisateurs&edit=<?= $user['id_user'] ?>" 
                                               class="btn btn-warning">✏️</a>

                                            <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                                <a href="/es_moulon/BO/admin.php?section=utilisateurs&toggle_bo_access=<?= $user['id_user'] ?>"
                                                   class="btn <?= $user['has_backoffice_access'] == 1 ? 'btn-secondary' : 'btn-success' ?>"
                                                   title="<?= $user['has_backoffice_access'] == 1 ? 'Retirer accès BO' : 'Donner accès BO' ?>">
                                                    <?= $user['has_backoffice_access'] == 1 ? '🔒' : '🔓' ?>
                                                </a>

                                                <a href="/es_moulon/BO/admin.php?section=utilisateurs&toggle_status=<?= $user['id_user'] ?>"
                                                   class="btn <?= $user['status'] == 1 ? 'btn-secondary' : 'btn-success' ?>"
                                                   title="<?= $user['status'] == 1 ? 'Désactiver' : 'Activer' ?>">
                                                    <?= $user['status'] == 1 ? '⏸️' : '▶️' ?>
                                                </a>

                                                <a href="/es_moulon/BO/admin.php?section=utilisateurs&delete=<?= $user['id_user'] ?>"
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
  <script>
        // Attendre que la page soit chargée
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            
            if (!searchInput) {
                console.error('Champ de recherche introuvable');
                return;
            }

            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const table = document.querySelector('tbody');
                
                if (!table) {
                    console.error('Tableau introuvable');
                    return;
                }
                
                const rows = table.querySelectorAll('tr');
                let visibleCount = 0;

                rows.forEach(row => {
                    // Récupérer le nom complet (colonne 2, index 1)
                    const nameCell = row.cells[1];
                    // Récupérer l'email (colonne 3, index 2)
                    const emailCell = row.cells[2];
                    
                    if (!nameCell || !emailCell) {
                        return;
                    }
                    
                    const name = nameCell.textContent.toLowerCase();
                    const email = emailCell.textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || email.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Mise à jour du compteur
                const title = document.querySelector('.card h2');
                if (title) {
                    const originalText = title.getAttribute('data-original-text') || title.textContent.split('(')[0].trim();
                    title.setAttribute('data-original-text', originalText);
                    title.textContent = `${originalText} (${visibleCount})`;
                }
            });
        });
    </script>
</body>
</html>