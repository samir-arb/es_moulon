<?php
// Supprime les accents et normalise une chaîne (utile pour slug ou URL)
function noAccent(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace(
        ['à','â','ä','á','ã','å','î','ï','ì','í','ô','ö','ò','ó','õ','ø','ù','û','ü','ú',
         'é','è','ê','ë','ç','ÿ','ñ'],
        ['a','a','a','a','a','a','i','i','i','i','o','o','o','o','o','o','u','u','u','u',
         'e','e','e','e','c','y','n'],
        $text
    );
    $text = preg_replace('#([^.a-z0-9]+)#i', '-', $text);
    $text = preg_replace('#-{2,}#','-',$text);
    $text = trim($text, '-');
    return $text;
}

// Génère un token aléatoire (utile pour mot de passe, CSRF, etc.)
function str_random(int $length = 16): string {
    $alphabet = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    return substr(str_shuffle(str_repeat($alphabet, $length)), 0, $length);
}

// Vérifie si l’utilisateur a un rôle donné
function hasRole(string $role): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Vérifie si l’utilisateur est connecté
function isLogged(): bool {
    return !empty($_SESSION['user_id']);
}

// Gestion des messages flash
function setFlash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}
function displayFlash(): void {
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $msg) {
            echo '<div class="alert alert-'.$type.'">'.htmlspecialchars($msg).'</div>';
        }
        unset($_SESSION['flash']);
    }
}
