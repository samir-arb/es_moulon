<?php
require_once __DIR__ . '/../../../includes/config.php';

// Protection de la page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash']['warning'] = "Vous devez être connecté.";

    header('Location: admin.php?section=contacts');
    exit;

}

// Vérification des permissions
$allowed_roles = ['ROLE_ADMIN'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['flash']['danger'] = "Vous n'avez pas accès à cette section.";

    header('Location: admin.php?section=contacts');
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
    
    header('Location: admin.php?section=contacts');
    exit;

}

// --- CHANGER LE STATUT (ex: accepté/rejeté/en attente) ---
if (isset($_GET['set_status']) && isset($_GET['id']) && in_array($_GET['set_status'], ['en attente','accepté','rejeté'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['set_status'];
    
    $sql = "UPDATE contacts SET status = ?, response_date = NOW() WHERE id_contact = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: admin.php?section=contacts');
    exit;

}

// --- LISTE DES CONTACTS ---
$sql = "
    SELECT *
    FROM contacts
    ORDER BY sent_at DESC
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
    'en_attente' => count(array_filter($contacts, function($c) { return $c['status'] === 'en attente'; })),
    'acceptes'   => count(array_filter($contacts, function($c) { return $c['status'] === 'accepté'; })),
    'rejetes'    => count(array_filter($contacts, function($c) { return $c['status'] === 'rejeté'; }))
];
?>

    <div class="container">
        <div class="header">
            <h1>📬 Messages de contact</h1>
            <p style="color: #6b7280; margin-top: 4px;">
                <a href="admin.php?section=dashboard" style="color: #1e40af; text-decoration: none;">← Retour au dashboard</a>
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
                <div class="stat-value" style="color: #1e40af;"><?= $stats['en_attente'] ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #10b981;"><?= $stats['acceptes'] ?></div>
                <div class="stat-label">Acceptés</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #ef4444;"><?= $stats['rejetes'] ?></div>
                <div class="stat-label">Rejetés</div>
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
                        <div class="contact-item">
                            <div class="contact-header">
                                <div>
                                    <div class="contact-name">
                                        👤 <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['name']) ?>
                                        <span class="badge"><?= htmlspecialchars($contact['contact_type']) ?></span>
                                    </div>
                                    <div class="contact-email">
                                        📧 <?= htmlspecialchars($contact['email']) ?>
                                        <?php if (!empty($contact['phone'])): ?>
                                            | 📱 <?= htmlspecialchars($contact['phone']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="contact-date">
                                    <?= date('d/m/Y', strtotime($contact['sent_at'])) ?>
                                </div>
                            </div>
                            
                            <div class="contact-message">
                                <?= nl2br(htmlspecialchars($contact['message'])) ?>
                            </div>

                            <div class="contact-status">
                                Statut : <strong><?= htmlspecialchars($contact['status']) ?></strong>
                                <?php if ($contact['response_date']): ?>
                                    (répondu le <?= date('d/m/Y H:i', strtotime($contact['response_date'])) ?>)
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($contact['response'])): ?>
                                <div class="contact-response">
                                    <strong>Réponse :</strong><br>
                                    <?= nl2br(htmlspecialchars($contact['response'])) ?>
                                </div>
                            <?php endif; ?>

                            <div class="contact-actions">
                                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>?subject=Réponse%20ES%20Moulon" 
                                   class="btn btn-primary">
                                    ✉️ Répondre
                                </a>
                                <a href="admin.php?section=contacts&set_status=accepté&id=<?= $contact['id_contact'] ?>" class="btn btn-success">✅ Accepter</a>
                                <a href="admin.php?section=contacts&set_status=rejeté&id=<?= $contact['id_contact'] ?>" class="btn btn-warning">❌ Rejeter</a>
                                <a href="admin.php?section=contacts&delete=<?= $contact['id_contact'] ?>" class="btn btn-danger" onclick="return confirm('Confirmer la suppression ?')">🗑️ Supprimer</a>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>


</body>
</html>
