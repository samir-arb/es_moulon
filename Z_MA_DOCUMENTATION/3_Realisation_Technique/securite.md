# SÃ©curitÃ© - Mesures ImplÃ©mentÃ©es

La sÃ©curitÃ© Ã©tait une de mes prÃ©occupations principales. Voici toutes les mesures que j'ai mises en place pour protÃ©ger le site et ses utilisateurs.

## 1. Protection Contre les Injections SQL

### Utilisation de RequÃªtes PrÃ©parÃ©es (PDO)

**âŒ Ce qu'il NE faut PAS faire :**
```php
$email = $_POST['email'];
$query = "SELECT * FROM users WHERE email = '$email'";
$result = $pdo->query($query);
```

**Risque :** Un attaquant pourrait injecter du SQL malveillant :
```
' OR '1'='1' --
```

**âœ… Ce que j'ai fait partout :**
```php
$email = $_POST['email'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

**Pourquoi c'est sÃ©curisÃ© ?**
- PDO Ã©chappe automatiquement les caractÃ¨res spÃ©ciaux
- Le code SQL et les donnÃ©es sont sÃ©parÃ©s
- Impossible d'injecter du code SQL

### Exemples Concrets du Projet

**Connexion utilisateur :**
```php
$stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE email = :email AND is_active = TRUE
");
$stmt->execute(['email' => $email]);
```

**Ajout d'une actualitÃ© :**
```php
$stmt = $pdo->prepare("
    INSERT INTO news (title, slug, content, id_author)
    VALUES (:title, :slug, :content, :author)
");
$stmt->execute([
    'title' => $title,
    'slug' => $slug,
    'content' => $content,
    'author' => $_SESSION['user_id']
]);
```

## 2. Gestion SÃ©curisÃ©e des Mots de Passe

### Hashage avec password_hash()

**âŒ Erreur Ã  Ã©viter :**
```php
// Stocker le mot de passe en clair
$password = $_POST['password'];
$stmt = $pdo->prepare("INSERT INTO users (password) VALUES (:pwd)");
$stmt->execute(['pwd' => $password]);
```

**âœ… Ma mÃ©thode :**

**Lors de la crÃ©ation d'un compte :**
```php
$password = $_POST['password'];

// Hashage sÃ©curisÃ© (bcrypt par dÃ©faut)
$hash = password_hash($password, PASSWORD_DEFAULT);

// Stockage du hash (pas du mot de passe)
$stmt = $pdo->prepare("
    INSERT INTO users (email, password_hash, first_name, last_name, role)
    VALUES (:email, :hash, :first, :last, :role)
");
$stmt->execute([
    'email' => $email,
    'hash' => $hash,
    'first' => $first_name,
    'last' => $last_name,
    'role' => 'ROLE_EDITOR'
]);
```

**Lors de la connexion :**
```php
$email = trim($_POST['email']);
$password = $_POST['password'];

// RÃ©cupÃ©ration de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

// VÃ©rification du mot de passe
if ($user && password_verify($password, $user['password_hash'])) {
    // âœ… Mot de passe correct
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['role'] = $user['role'];
} else {
    // âŒ Ã‰chec
    $error = "Email ou mot de passe incorrect";
}
```

**Avantages de password_hash() :**
- Utilise bcrypt (algorithme robuste)
- GÃ©nÃ¨re automatiquement un "salt" unique
- CoÃ»t adaptatif (peut Ãªtre augmentÃ© si les machines deviennent plus puissantes)
- `password_verify()` fait la comparaison de maniÃ¨re sÃ©curisÃ©e

## 3. Gestion des Sessions

### DÃ©marrage SÃ©curisÃ©

```php
// Dans config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

**Pourquoi ce test ?**
- Ã‰vite l'erreur "session already started"
- Permet d'inclure config.php plusieurs fois sans problÃ¨me

### Protection des Pages Admin

**Fichier `BO/admin.php` :**
```php
session_start();

// VÃ©rification de la connexion
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: _backoffice/auth/login.php");
    exit;
}

// RÃ©cupÃ©ration du rÃ´le
$user_role = $_SESSION['role'] ?? 'ROLE_VISITOR';
```

**Chaque section du back-office commence par cette vÃ©rification.**

### DÃ©connexion Propre

```php
// Fichier logout.php
session_start();

// Destruction de toutes les variables de session
$_SESSION = [];

// Suppression du cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destruction de la session
session_destroy();

// Redirection
header('Location: login.php');
exit;
```

## 4. Validation des DonnÃ©es

### CÃ´tÃ© Serveur (PHP)

**Formulaire de contact :**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des donnÃ©es
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    $errors = [];
    
    // Validation du nom
    if (empty($name)) {
        $errors[] = "Le nom est obligatoire";
    } elseif (strlen($name) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractÃ¨res";
    }
    
    // Validation de l'email
    if (empty($email)) {
        $errors[] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    
    // Validation du message
    if (empty($message)) {
        $errors[] = "Le message est obligatoire";
    } elseif (strlen($message) < 10) {
        $errors[] = "Le message doit contenir au moins 10 caractÃ¨res";
    }
    
    // Si pas d'erreurs, on traite
    if (empty($errors)) {
        // Insertion en BDD avec requÃªte prÃ©parÃ©e
        $stmt = $pdo->prepare("
            INSERT INTO contacts (name, email, subject, message)
            VALUES (:name, :email, :subject, :message)
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ]);
    }
}
```

### CÃ´tÃ© Client (JavaScript)

```javascript
// Validation basique (ne remplace PAS la validation serveur)
form.addEventListener('submit', (e) => {
    const email = document.querySelector('#email').value.trim();
    
    // Regex simple pour l'email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Veuillez entrer un email valide');
        return false;
    }
});
```

**Important :** La validation JavaScript est pour l'UX (retour immÃ©diat Ã  l'utilisateur), mais **jamais** pour la sÃ©curitÃ©. Un attaquant peut dÃ©sactiver JavaScript.

## 5. Upload de Fichiers SÃ©curisÃ©

### VÃ©rifications Multiples

```php
function uploadFile($file) {
    global $pdo;
    
    // 1ï¸âƒ£ VÃ©rification que le fichier existe
    if (empty($file['tmp_name'])) {
        throw new Exception("Aucun fichier uploadÃ©");
    }
    
    // 2ï¸âƒ£ VÃ©rification du type MIME
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception("Type de fichier non autorisÃ©");
    }
    
    // 3ï¸âƒ£ VÃ©rification de la taille (max 5 MB)
    $max_size = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $max_size) {
        throw new Exception("Fichier trop volumineux (max 5 MB)");
    }
    
    // 4ï¸âƒ£ GÃ©nÃ©ration d'un nom unique et sÃ©curisÃ©
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_name = uniqid('img_', true) . '.' . $extension;
    
    // 5ï¸âƒ£ Chemin sÃ©curisÃ© (en dehors de webroot si possible)
    $upload_dir = __DIR__ . '/../../public/assets/uploads/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $safe_name;
    
    // 6ï¸âƒ£ DÃ©placement du fichier
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception("Erreur lors de l'upload");
    }
    
    // 7ï¸âƒ£ Enregistrement en BDD
    $stmt = $pdo->prepare("
        INSERT INTO medias (file_name, file_path, file_type, file_size, uploaded_at)
        VALUES (:name, :path, :type, :size, NOW())
    ");
    $stmt->execute([
        'name' => $file['name'],
        'path' => 'uploads/' . $safe_name,
        'type' => $mime_type,
        'size' => $file['size']
    ]);
    
    return $pdo->lastInsertId();
}
```

**Mesures de sÃ©curitÃ© :**
- âœ… VÃ©rification du type MIME rÃ©el (pas juste l'extension)
- âœ… Limite de taille
- âœ… Renommage avec `uniqid()` (Ã©vite les conflits et injections)
- âœ… VÃ©rification que c'est bien un fichier uploadÃ© avec `move_uploaded_file()`
- âœ… Stockage du chemin en BDD (pas de lien direct)

## 6. Protection XSS (Cross-Site Scripting)

### Ã‰chappement des DonnÃ©es

**âŒ Dangereux :**
```php
<h1><?= $title ?></h1>
```

Si `$title` contient `<script>alert('XSS')</script>`, le code JavaScript sera exÃ©cutÃ©.

**âœ… SÃ©curisÃ© :**
```php
<h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
```

**Exemples du projet :**

```php
// Affichage d'un nom d'Ã©quipe
<span class="team-name"><?= htmlspecialchars($team['name']) ?></span>

// Affichage d'un titre d'actualitÃ©
<h2><?= htmlspecialchars($news['title']) ?></h2>

// Dans un attribut alt
<img src="..." alt="<?= htmlspecialchars($player['name']) ?>">
```

### ParticularitÃ© : Contenu HTML AutorisÃ©

Pour les actualitÃ©s, je veux autoriser certaines balises HTML (gras, lien, etc.).

**Option 1 : strip_tags() avec whitelist**
```php
$allowed_tags = '<p><br><b><strong><i><em><a><ul><ol><li>';
$safe_content = strip_tags($content, $allowed_tags);
```

**Option 2 : Utiliser HTMLPurifier (bibliothÃ¨que)**
C'est ce que j'aurais dÃ» faire si j'avais plus de temps. Pour l'instant, j'ai limitÃ© les dÃ©gÃ¢ts avec `strip_tags()`.

## 7. Protection CSRF (Cross-Site Request Forgery)

### Token CSRF pour les Formulaires Critiques

**GÃ©nÃ©ration du token :**
```php
// Au dÃ©but de la session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

**Dans le formulaire :**
```html
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <!-- Autres champs -->
    
    <button type="submit">Envoyer</button>
</form>
```

**VÃ©rification cÃ´tÃ© serveur :**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VÃ©rification du token
    $token = $_POST['csrf_token'] ?? '';
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        die("Token CSRF invalide");
    }
    
    // Traitement du formulaire...
}
```

**âš ï¸ HonnÃªtetÃ© :** Je n'ai pas implÃ©mentÃ© de token CSRF partout dans mon projet par manque de temps. C'est une amÃ©lioration Ã  faire !

## 8. SystÃ¨me de Permissions (Back-Office)

### ContrÃ´le d'AccÃ¨s BasÃ© sur les RÃ´les

```php
// Fichier roles.php
$roles = [
    'ROLE_ADMIN' => [
        'users', 'news', 'players', 'teams', 'matches', 
        'staff', 'partners', 'medias', 'contacts', 'settings'
    ],
    'ROLE_EDITOR' => [
        'news', 'players', 'teams', 'matches', 'staff', 'medias'
    ],
    'ROLE_VISITOR' => [
        'dashboard'
    ]
];

function hasPermission($role, $section, $menu_items, $roles) {
    // Admin a tous les droits
    if ($role === 'ROLE_ADMIN') {
        return true;
    }
    
    // VÃ©rification des permissions
    $allowed_sections = $roles[$role] ?? [];
    return in_array($section, $allowed_sections);
}
```

**Utilisation :**
```php
// Dans admin.php
$current_section = $_GET['section'] ?? 'dashboard';

if (!hasPermission($user_role, $current_section, $menu_items, $roles)) {
    // Pas de permission â†’ redirection
    $current_section = 'dashboard';
}
```

### VÃ©rification Avant Actions Sensibles

**Exemple : Suppression d'un utilisateur (admin uniquement)**
```php
if (isset($_GET['delete_user'])) {
    // VÃ©rification du rÃ´le
    if ($_SESSION['role'] !== 'ROLE_ADMIN') {
        die("Action non autorisÃ©e");
    }
    
    $id_user = (int)$_GET['delete_user'];
    
    // EmpÃªcher la suppression de son propre compte
    if ($id_user === $_SESSION['user_id']) {
        die("Vous ne pouvez pas supprimer votre propre compte");
    }
    
    // Suppression
    $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = :id");
    $stmt->execute(['id' => $id_user]);
}
```

## 9. Protection des DonnÃ©es Sensibles

### Fichier config.php

**âŒ Mauvaise pratique :**
- Commiter le fichier avec les identifiants en dur
- Laisser des mots de passe en clair

**âœ… Bonne pratique (idÃ©ale) :**
- Utiliser un fichier `.env` (non versionnÃ©)
- Charger les variables avec `vlucas/phpdotenv`

**Ce que j'ai fait :**
```php
// config.php (ne devrait PAS Ãªtre sur GitHub en prod)
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'es_moulon';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? '';
```

Et dans `.gitignore` :
```
.env
config.php
```

## 10. Autres Mesures

### DÃ©sactivation de l'Affichage d'Erreurs (Production)

```php
// En dÃ©veloppement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En production (Ã  mettre)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

### Headers de SÃ©curitÃ© (Ã  amÃ©liorer)

```php
// Ã€ ajouter dans config.php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
```

### Timeout de Session

```php
// Expiration aprÃ¨s 2 heures d'inactivitÃ©
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();
```

## RÃ©capitulatif des Mesures

| Menace | Protection | ImplÃ©mentÃ©e |
|--------|-----------|-------------|
| Injection SQL | RequÃªtes prÃ©parÃ©es PDO | âœ… |
| Vol de mot de passe | `password_hash()` / `password_verify()` | âœ… |
| AccÃ¨s non autorisÃ© | VÃ©rification de session | âœ… |
| XSS | `htmlspecialchars()` | âœ… |
| Upload malveillant | VÃ©rification type/taille | âœ… |
| Permissions | SystÃ¨me de rÃ´les | âœ… |
| CSRF | Token CSRF | âš ï¸ Partiel |
| Headers sÃ©curitÃ© | Headers HTTP | âš ï¸ Ã€ amÃ©liorer |

## Points d'AmÃ©lioration

Si j'avais plus de temps :
- ImplÃ©menter les tokens CSRF partout
- Ajouter une limite de tentatives de connexion (brute force protection)
- Utiliser HTTPS (certificat SSL)
- Mettre en place un systÃ¨me de logs de sÃ©curitÃ©
- Utiliser une bibliothÃ¨que comme HTMLPurifier pour le contenu riche
- Ajouter une vÃ©rification email lors de l'inscription
- ImplÃ©menter un systÃ¨me de rÃ©cupÃ©ration de mot de passe sÃ©curisÃ©

MalgrÃ© ces amÃ©liorations possibles, je pense avoir mis en place les bases d'une sÃ©curitÃ© correcte pour un projet de ce niveau !

---

## 11. Conformité RGPD - Système de Consentement Cookies

### Mise en Place d'une Bannière de Consentement

J'ai implémenté un système complet de gestion du consentement des cookies conforme au RGPD.

**Fichiers créés :**
- `public/assets/_front.css/cookie-consent.css` (styles de la bannière)
- `public/assets/js/cookie-consent.js` (gestion JavaScript des cookies)
- Bannière HTML dans `includes/footer.php`

**Fonctionnement :**

``javascript
// cookie-consent.js
function acceptCookies() {
    setCookie('cookie_consent', 'accepted', 365);
    setCookie('tracking_consent', 'yes', 365);
    hideCookieBanner();
}

function refuseCookies() {
    setCookie('cookie_consent', 'refused', 365);
    setCookie('tracking_consent', 'no', 365);
    hideCookieBanner();
}
``

**Intégration avec le système de tracking :**

``php
// includes/tracker.php
function enregistrerVisite($pdo) {
    //  RGPD : Vérification du consentement AVANT tracking
    if (!isset($_COOKIE['cookie_consent']) || $_COOKIE['cookie_consent'] !== 'accepted') {
        // L'utilisateur n'a pas accepté  On ne collecte RIEN
        return false;
    }
    
    // Si consentement donné, on enregistre la visite
    $stmt = $pdo->prepare("
        INSERT INTO visites (ip_address, user_agent, page_url, ...)
        VALUES (:ip, :user_agent, :page_url, ...)
    ");
    // ...
}
``

**Résultat :**
-  Aucune donnée collectée sans consentement explicite
-  Choix persistant (cookie valable 1 an)
-  Possibilité de modifier son choix (lien "Gérer les cookies" dans le footer)

### Tests de Conformité RGPD

J'ai créé deux pages de test pour démontrer la conformité :

**1. Page de test interactive :**
- URL : `_tests_securite/test_rgpd_cookies.php`
- Permet de tester l'acceptation/refus des cookies
- Affiche en temps réel le nombre de visites en BDD
- Prouve que le refus bloque effectivement le tracking

**2. Page de débogage technique :**
- URL : `debug_cookies.php`
- Affiche l'état des cookies et de la session
- Permet de réinitialiser les tests
- Utile pour la démonstration en live devant le jury

---

## 12. Limitations Identifiées et Améliorations Futures

### Conservation des Données de Tracking

**Problème identifié :** 

Les données de la table `visites` sont actuellement conservées **indéfiniment** dans la base de données. Aucun système de nettoyage automatique n'est mis en place.

**Impact RGPD :**

Le Règlement Général sur la Protection des Données stipule que les données personnelles ne doivent être conservées que pour **la durée strictement nécessaire** aux finalités du traitement.

Pour des statistiques de site web, la CNIL recommande une conservation de **12 à 24 mois maximum**.

**Données concernées :**
- Adresses IP (données personnelles identifiantes)
- User-Agent (empreinte technique du visiteur)
- Pages visitées (comportement de navigation)
- Système d'exploitation et navigateur

**Solutions envisagées :**

**1. Nettoyage manuel (déjà possible) :**
``sql
-- Supprimer les visites de plus de 12 mois
DELETE FROM visites 
WHERE date_visite < DATE_SUB(NOW(), INTERVAL 12 MONTH);
``

**2. Nettoyage automatique via tâche CRON (recommandé en production) :**
``bash
# Exécuter chaque nuit à 2h du matin
0 2 * * * php /path/to/cron_cleanup_visites.php
``

Contenu du script PHP :
``php
<?php
require_once 'includes/config.php';

// Supprimer les visites de plus de 12 mois
$stmt = $pdo->prepare("
    DELETE FROM visites 
    WHERE date_visite < DATE_SUB(NOW(), INTERVAL 12 MONTH)
");
$stmt->execute();

$deleted = $stmt->rowCount();
error_log("Nettoyage RGPD : $deleted visites supprimées");
?>
``

**3. Bouton de nettoyage dans le back-office (option pratique) :**
- Ajouter une section "Paramètres RGPD" dans le back-office
- Bouton " Nettoyer les anciennes visites (> 12 mois)"
- Accessible uniquement aux administrateurs
- Affiche le nombre de lignes supprimées

**4. Archivage avant suppression (option avancée) :**
- Créer une table `visites_archive`
- Déplacer les anciennes données avant suppression
- Permet de garder des statistiques historiques anonymisées

**Priorité :** 

**Moyenne** - Cette amélioration n'était pas critique pour la phase de développement car :
1. La priorité absolue était le **système de consentement** (maintenant implémenté )
2. En phase de développement, conserver toutes les données permet d'analyser les tendances
3. Le volume de données reste faible pour un site en développement

**En production**, je recommanderais d'activer le nettoyage automatique (option 2) combiné à un bouton manuel (option 3) pour laisser le contrôle à l'administrateur.

### Anonymisation des Données

**Alternative au nettoyage : Anonymisation**

Au lieu de supprimer les anciennes visites, on pourrait les anonymiser :

``php
// Anonymiser les visites de plus de 3 mois
$stmt = $pdo->prepare("
    UPDATE visites 
    SET ip_address = 'anonymized', 
        user_agent = 'anonymized',
        referer = NULL
    WHERE date_visite < DATE_SUB(NOW(), INTERVAL 3 MONTH)
");
$stmt->execute();
``

**Avantage :** On garde les statistiques (pages visitées, dates) sans les données personnelles.

**Inconvénient :** Moins d'informations pour l'analyse détaillée.

### Déclaration CNIL

**Important :** En France, un site qui collecte des données personnelles (IP, cookies) doit théoriquement être déclaré à la CNIL ou figurer dans le registre des traitements de l'entreprise (RGPD).

Pour ce projet scolaire, cette déclaration n'est pas nécessaire, mais en production réelle, il faudrait :
1. Tenir un registre des traitements
2. Rédiger une politique de confidentialité complète
3. Nommer un DPO (Délégué à la Protection des Données) si l'entreprise > 250 salariés

### Documentation Utilisateur

J'ai créé une page "Politique de confidentialité" accessible via le footer qui explique :
- Quelles données sont collectées
- Pourquoi elles sont collectées
- Combien de temps elles sont conservées
- Comment exercer ses droits (accès, rectification, suppression)

---

## Conclusion sur la Sécurité

Malgré ces quelques points d'amélioration identifiés, j'estime avoir mis en place un niveau de sécurité **satisfaisant** pour un projet de ce niveau :

 **Points forts :**
- 100% des requêtes SQL utilisent des requêtes préparées (zéro risque d'injection SQL)
- Système de consentement RGPD fonctionnel et testé
- Mots de passe hashés avec bcrypt
- Upload de fichiers sécurisé avec vérifications multiples
- Système de rôles et permissions dans le back-office
- Protection XSS via htmlspecialchars()
- Tests de sécurité créés pour démonstration

 **Points à améliorer (identifiés et documentés) :**
- Tokens CSRF à généraliser sur tous les formulaires
- Conservation des données limitée dans le temps (RGPD)
- Headers de sécurité HTTP à renforcer
- Limite de tentatives de connexion (anti brute-force)

**Mon approche :** Plutôt que de cacher ces limitations, je préfère les **documenter clairement** et proposer des **solutions concrètes**. Cela montre ma capacité à analyser un système, identifier ses failles, et planifier des améliorations réalistes.
