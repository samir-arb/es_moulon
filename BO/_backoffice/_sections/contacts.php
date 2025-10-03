<?php
session_start();
require '../../includes/config.php';

// Protection de la page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";
    header('Location: login.php');
    exit;
}

// Vérification des permissions
$allowed_roles = ['ROLE_ADMIN'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";
    header('Location: dashboard.php');
    exit;
}

// --- SUPPRESSION ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $sql = "DELETE FROM contacts WHERE id_contact = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $_SESSION['flash']['success'] = "Message supprimé avec succès.";
    } else {
        $_SESSION['flash']['danger'] = "Erreur lors de la suppression.";
    }
    $stmt->close();
    
    header('Location: contacts.php');
    exit;
}

// --- MARQUER COMME LU ---
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    
    $sql = "UPDATE contacts SET is_read = 1 WHERE id_contact = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: contacts.php');
    exit;
}

// --- LISTE DES CONTACTS ---
$sql = "
    SELECT *
    FROM contacts
    ORDER BY is_read ASC, created_at DESC
";
$result = $conn->query($sql);
$contacts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
}

// Statistiques
$stats = [
    'total' => count($contacts),
    'non_lus' => count(array_filter($contacts, function($c) { return $c['is_read'] == 0; })),
    'lus' => count(array_filter($contacts, function($c) { return $c['is_read'] == 1; }))
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Contacts - ES Moulon</title>
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
        }
        .header h1 {
            color: #1f2937;
            font-size: 1.5rem;
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
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .contact-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .contact-item {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #e5e7eb;
            transition: all 0.2s;
        }
        .contact-item.unread {
            background: #eff6ff;
            border-left-color: #1e40af;
        }
        .contact-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .contact-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }
        .contact-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
        }
        .contact-email {
            color: #6b7280;
            font-size: 0.9rem;
        }
        .contact-date {
            color: #9ca3af;
            font-size: 0.85rem;
        }
        .contact-subject {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .contact-message {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .contact-actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            font-size: 0.85rem;
        }
        .btn-primary {
            background: #1e40af;
            color: white;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-unread {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-read {
            background: #f3f4f6;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📬 Messages de contact</h1>
            <p style="color: #6b7280; margin-top: 4px;">
                <a href="dashboard.php" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
            </p>
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
                <div class="stat-value" style="color: #1e40af;"><?= $stats['non_lus'] ?></div>
                <div class="stat-label">Non lus</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #10b981;"><?= $stats['lus'] ?></div>
                <div class="stat-label">Lus</div>
            </div>
        </div>

        <!-- LISTE -->
        <div class="card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">Messages reçus (<?= count($contacts) ?>)</h2>
            
            <?php if (empty($contacts)): ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">Aucun message pour le moment.</p>
            <?php else: ?>
                <div class="contact-list">
                    <?php foreach ($contacts as $contact): ?>
                        <div class="contact-item <?= $contact['is_read'] == 0 ? 'unread' : '' ?>">
                            <div class="contact-header">
                                <div>
                                    <div class="contact-name">
                                        👤 <?= htmlspecialchars($contact['name']) ?>
                                        <?php if ($contact['is_read'] == 0): ?>
                                            <span class="badge badge-unread">NOUVEAU</span>
                                        <?php else: ?>
                                            <span class="badge badge-read">Lu</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="contact-email">
                                        📧 <?= htmlspecialchars($contact['email']) ?>
                                        <?php if (!empty($contact['phone'])): ?>
                                            | 📱 <?= htmlspecialchars($contact['phone']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="contact-date">
                                    <?= date('d/m/Y à H:i', strtotime($contact['created_at'])) ?>
                                </div>
                            </div>
                            
                            <div class="contact-subject">
                                📝 Sujet : <?= htmlspecialchars($contact['subject']) ?>
                            </div>
                            
                            <div class="contact-message">
                                <?= nl2br(htmlspecialchars($contact['message'])) ?>
                            </div>
                            
                            <div class="contact-actions">
                                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>?subject=Re: <?= urlencode($contact['subject']) ?>" 
                                   class="btn btn-primary">
                                    ✉️ Répondre
                                </a>
                                <?php if ($contact['is_read'] == 0): ?>
                                    <a href="contacts.php?mark_read=<?= $contact['id_contact'] ?>" 
                                       class="btn btn-success">
                                        ✓ Marquer comme lu
                                    </a>
                                <?php endif; ?>
                                <a href="contacts.php?delete=<?= $contact['id_contact'] ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Confirmer la suppression ?')">
                                    🗑️ Supprimer
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
