<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/EmailService.php';

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

// 🛡️ GÉNÉRATION TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
        try {
            // 1. Supprimer les fonctions dans le club
            $delete_functions = $conn->prepare("DELETE FROM users_club_functions WHERE id_user = ?");
            $delete_functions->bind_param("i", $id);
            $delete_functions->execute();
            $delete_functions->close();

            // 2. Supprimer l'utilisateur
            $sql = "DELETE FROM users WHERE id_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);

            if ($stmt->execute()) {
                $_SESSION['flash']['success'] = "Utilisateur et ses fonctions supprimés avec succès.";
            } else {
                $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['flash']['danger'] = "Erreur : " . $e->getMessage();
        }
    }

    header('Location: admin.php?section=utilisateurs');
    exit;
}

// --- AJOUT / MODIFICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    
    // 🛡️ VÉRIFICATION TOKEN CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash']['danger'] = "❌ Token CSRF invalide. Tentative d'attaque détectée !";
        header('Location: /es_moulon/BO/admin.php?section=utilisateurs');
        exit;
    }
    
    $id = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $id_role = (int)$_POST['id_role'];
    $status = isset($_POST['status']) ? 1 : 0;
    $has_backoffice_access = isset($_POST['has_backoffice_access']) ? 1 : 0;

    // Si aucun rôle sélectionné, utiliser ROLE_LICENSED (id 1) par défaut
    if ($id_role === 0) {
        $id_role = 1; // ROLE_LICENSED
    }

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $_SESSION['flash']['danger'] = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash']['danger'] = "Format d'email invalide.";
    } else {
        if ($id > 0) {
            // MODIFICATION
            $sql = "UPDATE users SET first_name = ?, name = ?, email = ?, id_role = ?, status = ?, has_backoffice_access = ? WHERE id_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssiiii', $first_name, $last_name, $email, $id_role, $status, $has_backoffice_access, $id);
            $message = "Utilisateur modifié avec succès.";

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

            // AJOUT - CORRECTION ICI
            $reset_token = bin2hex(random_bytes(32));
            $reset_token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $sql = "INSERT INTO users (first_name, name, email, password, id_role, status, has_backoffice_access, reset_token, reset_token_expiry, is_password_set) VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            // CORRECTION: 's' pour string, 'i' pour integer
            // first_name(s), name(s), email(s), id_role(i), status(i), has_backoffice_access(i), reset_token(s), reset_token_expiry(s)
            $stmt->bind_param('sssiisss', $first_name, $last_name, $email, $id_role, $status, $has_backoffice_access, $reset_token, $reset_token_expiry);

            if ($stmt->execute()) {
                $new_user_id = $conn->insert_id;

                // ENVOI EMAIL avec le service automatique
                $activation_link = "http://" . $_SERVER['HTTP_HOST'] . "/es_moulon/BO/_backoffice/auth/create_password.php?token=" . $reset_token;
                try {
                    $emailService = new EmailService();
                    $result = $emailService->sendActivationEmail($email, $first_name, $last_name, $activation_link);

                    if ($result['success']) {
                        // Email envoyé avec succès
                        $mailpitUrl = $emailService->getMailpitUrl();
                        if ($mailpitUrl) {
                            // En développement avec Mailpit
                            $_SESSION['flash']['success'] = "✅ Utilisateur créé ! Email envoyé (voir <a href='$mailpitUrl' target='_blank' style='color: #065f46; font-weight: bold;'>Mailpit</a>)";
                        } else {
                            // En production
                            $_SESSION['flash']['success'] = "✅ Utilisateur créé ! Un email d'invitation a été envoyé à " . $email;
                        }
                    } else {
                        // Erreur d'envoi
                        $_SESSION['flash']['warning'] = "⚠️ Utilisateur créé mais l'email n'a pas pu être envoyé. Lien d'activation : <a href='$activation_link' target='_blank' style='color: #92400e; font-weight: bold;'>Activer le compte</a>";
                    }
                } catch (Exception $e) {
                    $_SESSION['flash']['warning'] = "⚠️ Utilisateur créé. Erreur email : " . $e->getMessage();
                }

                $stmt->close();
                header('Location: /es_moulon/BO/admin.php?section=utilisateurs');
                exit;
            } else {
                if ($stmt->errno === 1062) {
                    $_SESSION['flash']['danger'] = "Cet email existe déjà.";
                } else {
                    $_SESSION['flash']['danger'] = "Erreur lors de l'enregistrement : " . $stmt->error;
                }
                $stmt->close();
            }
        }
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
    $sql = "SELECT u.*, r.role_name
            FROM users u
            INNER JOIN roles r ON u.id_role = r.id_role
            ORDER BY u.has_backoffice_access DESC, u.status DESC, u.name";
} else {
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
    <title>Gestion des Utilisateurs - ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_back.css/utilisateurs.css') ?>">

</head>

<body>
    <div class="container">
        <!-- EN-TÊTE -->
        <div class="header">
            <div>
                <h1>👥 Gestion des Utilisateurs</h1>
                <p style="color: #6b7280; margin-top: 4px;">
                    <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
                </p>
            </div>
            <button class="btn btn-primary" onclick="toggleForm()">➕ Nouvel utilisateur</button>
        </div>

        <!-- MESSAGES FLASH -->
        <?php
        if (isset($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $type => $message) {
                echo "<div class='alert alert-$type'>$message</div>";
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
                <div class="stat-value" style="color: #f59e0b;"><?= $stats['bo_access'] ?></div>
                <div class="stat-label">Accès BO</div>
            </div>
        </div>

        <!-- FORMULAIRE -->
        <div class="card" id="formSection" style="<?= $edit_user ? '' : 'display:none;' ?>">
            <h2><?= $edit_user ? '✏️ Modifier l\'utilisateur' : '➕ Nouvel utilisateur' ?></h2>

            <form method="POST" action="/es_moulon/BO/admin.php?section=utilisateurs">
                
                <!-- 🛡️ CHAMP CSRF CACHÉ -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
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
                        <label for="id_role">Rôle</label>
                        <select id="id_role" name="id_role">
                            <option value="1" <?= (!$edit_user || $edit_user['id_role'] == 1) ? 'selected' : '' ?>>ROLE_LICENSED (Licencié)</option>
                            <?php foreach ($roles_list as $role): ?>
                                <?php if ($role['id_role'] != 1): ?>
                                    <option value="<?= $role['id_role'] ?>"
                                        <?= ($edit_user && $edit_user['id_role'] == $role['id_role']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['role_name']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="checkbox-wrapper">
                            <label>
                                <input type="checkbox" id="status" name="status"
                                    <?= ($edit_user && $edit_user['status'] == 1) || !$edit_user ? 'checked' : '' ?>>
                                Compte actif
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="checkbox-wrapper">
                            <label>
                                <input type="checkbox" id="has_backoffice_access" name="has_backoffice_access"
                                    <?= ($edit_user && $edit_user['has_backoffice_access'] == 1) ? 'checked' : '' ?>>
                                Accès back-office
                            </label>
                            <small>⚠️ Décocher pour un simple licencié</small>
                        </div>
                    </div>
                </div>

                <div class="role-help">
                    💡 <strong>Par défaut :</strong> Rôle "ROLE_LICENSED" (licencié simple) SANS accès back-office
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_user" class="btn btn-success">💾 Enregistrer</button>
                    <a href="/es_moulon/BO/admin.php?section=utilisateurs" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <!-- LISTE DES UTILISATEURS -->
        <div class="card">
            <!-- FILTRES -->
            <div class="filter-tabs">
                <a href="/es_moulon/BO/admin.php?section=utilisateurs&filter=backoffice"
                    class="<?= $filter === 'backoffice' ? 'active' : '' ?>">
                    🔑 Accès BO
                </a>
                <a href="/es_moulon/BO/admin.php?section=utilisateurs&filter=all"
                    class="<?= $filter === 'all' ? 'active' : '' ?>">
                    👥 Tous les licenciés
                </a>
            </div>

            <!-- RECHERCHE -->
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Rechercher par nom ou email...">
            </div>

            <h2><?= $filter === 'all' ? 'Tous les licenciés' : 'Utilisateurs avec accès back-office' ?> (<?= count($users) ?>)</h2>

            <?php if (empty($users)): ?>
                <p style="text-align: center; color: #6b7280; padding: 20px;">Aucun utilisateur.</p>
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
                                            <span style="color: #1e40af; font-size: 0.75rem;">(Vous)</span>
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
                                            <span class="badge badge-active">✓</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">✗</span>
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
                                                class="btn btn-warning" title="Modifier">✏️</a>

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
                                                    class="btn btn-danger" title="Supprimer"
                                                    onclick="return confirm('⚠️ ATTENTION : Cela supprimera aussi toutes les fonctions de <?= htmlspecialchars($user['first_name'] . ' ' . $user['name']) ?> dans le club.\n\nConfirmer la suppression ?')">
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
        function toggleForm() {
            const form = document.getElementById('formSection');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');

            if (!searchInput) return;

            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const table = document.querySelector('tbody');

                if (!table) return;

                const rows = table.querySelectorAll('tr');
                let visibleCount = 0;

                rows.forEach(row => {
                    const nameCell = row.cells[1];
                    const emailCell = row.cells[2];

                    if (!nameCell || !emailCell) return;

                    const name = nameCell.textContent.toLowerCase();
                    const email = emailCell.textContent.toLowerCase();

                    if (name.includes(searchTerm) || email.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

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