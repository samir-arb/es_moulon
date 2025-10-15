<?php
// includes/tracker.php
// Ce fichier enregistre les visites du site public
// À inclure en haut de chaque page publique : require_once 'includes/tracker.php';

// Connexion à la base de données
require_once __DIR__ . '/config.php';

// Fonction pour enregistrer une visite
function enregistrerVisite($pdo) {
    try {
        // Récupérer les informations du visiteur
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'inconnu';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'inconnu';
        $page_url = $_SERVER['REQUEST_URI'] ?? '/';
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        
        // Nettoyer l'IP (gérer les proxies)
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        // Détection basique du navigateur
        $browser = 'Autre';
        if (strpos($user_agent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($user_agent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (strpos($user_agent, 'Opera') !== false) {
            $browser = 'Opera';
        }
        
        // Détection basique du système d'exploitation
        $os = 'Autre';
        if (strpos($user_agent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($user_agent, 'Mac') !== false) {
            $os = 'MacOS';
        } elseif (strpos($user_agent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($user_agent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($user_agent, 'iOS') !== false || strpos($user_agent, 'iPhone') !== false) {
            $os = 'iOS';
        }
        
        // Vérifier si ce n'est pas un bot
        $is_bot = false;
        $bots = ['bot', 'crawler', 'spider', 'scraper'];
        foreach ($bots as $bot) {
            if (stripos($user_agent, $bot) !== false) {
                $is_bot = true;
                break;
            }
        }
        
        // Ne pas enregistrer les bots (optionnel)
        if ($is_bot) {
            return false;
        }
        
        // Vérifier si l'utilisateur a déjà visité aujourd'hui (session)
        if (!isset($_SESSION['visite_enregistree'])) {
            
            // Insérer la visite dans la base de données
            $stmt = $pdo->prepare("
                INSERT INTO visites 
                (ip_address, user_agent, page_url, referer, browser, os, date_visite) 
                VALUES 
                (:ip, :user_agent, :page_url, :referer, :browser, :os, NOW())
            ");
            
            $stmt->execute([
                ':ip' => $ip_address,
                ':user_agent' => $user_agent,
                ':page_url' => $page_url,
                ':referer' => $referer,
                ':browser' => $browser,
                ':os' => $os
            ]);
            
            // Marquer la visite comme enregistrée dans la session
            $_SESSION['visite_enregistree'] = true;
            
            return true;
        }
        
        return false;
        
    } catch (PDOException $e) {
        // Logger l'erreur sans afficher à l'utilisateur
        error_log("Erreur tracker : " . $e->getMessage());
        return false;
    }
}

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enregistrer la visite
enregistrerVisite($pdo);