<?php
/**
 * Configuration Email - ES Moulon
 * Détecte automatiquement l'environnement (dev/prod)
 * et utilise la bonne méthode d'envoi
 */

class EmailService {
    private $config;
    
    public function __construct() {
        // Détection automatique de l'environnement
        $isLocal = (
            $_SERVER['HTTP_HOST'] === 'localhost' || 
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.local') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.test') !== false
        );
        
        if ($isLocal) {
            // Configuration MAILPIT (Laragon en local)
            $this->config = [
                'type' => 'smtp',
                'host' => 'localhost',
                'port' => 1025,
                'auth' => false,
                'username' => '',
                'password' => '',
                'from_email' => 'noreply@esmoulon.local',
                'from_name' => 'ES Moulon (Dev)',
                'encryption' => '' // Pas de SSL/TLS pour Mailpit
            ];
        } else {
            // Configuration PRODUCTION (à personnaliser chez le client)
            // Par défaut : Gmail (mais peut être changé pour OVH, SendGrid, etc.)
            $this->config = [
                'type' => 'smtp',
                'host' => 'smtp.gmail.com', // Ou smtp.monhebergeur.com
                'port' => 587,
                'auth' => true,
                'username' => 'contact@esmoulon.fr', // Email du club
                'password' => 'MOT_DE_PASSE_APP', // Mot de passe application
                'from_email' => 'noreply@esmoulon.fr',
                'from_name' => 'ES Moulon',
                'encryption' => 'tls'
            ];
        }
    }
    
    /**
     * Envoie un email d'activation de compte
     */
    public function sendActivationEmail($to_email, $first_name, $last_name, $activation_link) {
        $subject = "Bienvenue sur ES Moulon - Créez votre mot de passe";
        
        $message = "Bonjour $first_name $last_name,\n\n";
        $message .= "Votre compte a été créé sur le back-office ES Moulon.\n\n";
        $message .= "Cliquez sur ce lien pour créer votre mot de passe :\n";
        $message .= $activation_link . "\n\n";
        $message .= "Ce lien est valide pendant 48 heures.\n\n";
        $message .= "Cordialement,\n";
        $message .= "L'équipe ES Moulon";
        
        return $this->send($to_email, $subject, $message);
    }
    
    /**
     * Méthode principale d'envoi
     */
    public function send($to_email, $subject, $message, $html = false) {
        // En-têtes standards
        $headers = [];
        $headers[] = "From: {$this->config['from_name']} <{$this->config['from_email']}>";
        $headers[] = "Reply-To: {$this->config['from_email']}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        if ($html) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }
        
        // Configuration SMTP pour Mailpit ou serveur de production
        if ($this->config['type'] === 'smtp') {
            ini_set('SMTP', $this->config['host']);
            ini_set('smtp_port', $this->config['port']);
            
            // Mailpit n'a pas besoin d'auth
            if (!$this->config['auth']) {
                ini_set('sendmail_from', $this->config['from_email']);
            }
        }
        
        // Envoi de l'email
        $result = mail($to_email, $subject, $message, implode("\r\n", $headers));
        
        return [
            'success' => $result,
            'environment' => $this->config['type'],
            'debug_info' => [
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'from' => $this->config['from_email']
            ]
        ];
    }
    
    /**
     * Retourne le lien Mailpit pour visualiser les emails en dev
     */
    public function getMailpitUrl() {
        if ($this->config['host'] === 'localhost' && $this->config['port'] === 1025) {
            return "http://localhost:8025"; // Interface web de Mailpit
        }
        return null;
    }
}