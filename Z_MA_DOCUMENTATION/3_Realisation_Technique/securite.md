# Sécurité - Mesures Implémentées

La sécurité était une de mes préoccupations principales. Voici toutes les mesures que j'ai mises en place pour protéger le site et ses utilisateurs.

## 1. Protection Contre les Injections SQL

### Utilisation de Requêtes Préparées (PDO)

**❌ Ce qu'il NE faut PAS faire :**
```php
$email = $_POST['email'];
$query = "SELECT * FROM users WHERE email = '$email'";
$result = $pdo->query($query);
```

**Risque :** Un attaquant pourrait injecter du SQL malveillant :
```
' OR '1'='1' --
```

**✅ Ce que j'ai fait partout :**
```php
$email = $_POST['email'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

**Pourquoi c'est sécurisé ?**
- PDO échappe automatiquement les caractères spéciaux
- Le code SQL et les données sont séparés
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

**Ajout d'une actualité :**
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

## 2. Gestion Sécurisée des Mots de Passe

### Hashage avec password_hash()

**❌ Erreur à éviter :**
```php
// Stocker le mot de passe en clair
$password = $_POST['password'];
$stmt = $pdo->prepare("INSERT INTO users (password) VALUES (:pwd)");
$stmt->execute(['pwd' => $password]);
```

**✅ Ma méthode :**

**Lors de la création d'un compte :**
```php
$password = $_POST['password'];

// Hashage sécurisé (bcrypt par défaut)
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

// Récupération de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

// Vérification du mot de passe
if ($user && password_verify($password, $user['password_hash'])) {
    // ✅ Mot de passe correct
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['role'] = $user['role'];
} else {
    // ❌ Échec
    $error = "Email ou mot de passe incorrect";
}
```

**Avantages de password_hash() :**
- Utilise bcrypt (algorithme robuste)
- Génère automatiquement un "salt" unique
- Coût adaptatif (peut être augmenté si les machines deviennent plus puissantes)
- `password_verify()` fait la comparaison de manière sécurisée

## 3. Gestion des Sessions

### Démarrage Sécurisé

```php
// Dans config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

**Pourquoi ce test ?**
- Évite l'erreur "session already started"
- Permet d'inclure config.php plusieurs fois sans problème

### Protection des Pages Admin

**Fichier `BO/admin.php` :**
```php
session_start();

// Vérification de la connexion
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: _backoffice/auth/login.php");
    exit;
}

// Récupération du rôle
$user_role = $_SESSION['role'] ?? 'ROLE_VISITOR';
```

**Chaque section du back-office commence par cette vérification.**

### Déconnexion Propre

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

## 4. Validation des Données

### Côté Serveur (PHP)

**Formulaire de contact :**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    $errors = [];
    
    // Validation du nom
    if (empty($name)) {
        $errors[] = "Le nom est obligatoire";
    } elseif (strlen($name) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères";
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
        $errors[] = "Le message doit contenir au moins 10 caractères";
    }
    
    // Si pas d'erreurs, on traite
    if (empty($errors)) {
        // Insertion en BDD avec requête préparée
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

### Côté Client (JavaScript)

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

**Important :** La validation JavaScript est pour l'UX (retour immédiat à l'utilisateur), mais **jamais** pour la sécurité. Un attaquant peut désactiver JavaScript.

## 5. Upload de Fichiers Sécurisé

### Vérifications Multiples

```php
function uploadFile($file) {
    global $pdo;
    
    // 1️⃣ Vérification que le fichier existe
    if (empty($file['tmp_name'])) {
        throw new Exception("Aucun fichier uploadé");
    }
    
    // 2️⃣ Vérification du type MIME
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception("Type de fichier non autorisé");
    }
    
    // 3️⃣ Vérification de la taille (max 5 MB)
    $max_size = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $max_size) {
        throw new Exception("Fichier trop volumineux (max 5 MB)");
    }
    
    // 4️⃣ Génération d'un nom unique et sécurisé
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_name = uniqid('img_', true) . '.' . $extension;
    
    // 5️⃣ Chemin sécurisé (en dehors de webroot si possible)
    $upload_dir = __DIR__ . '/../../public/assets/uploads/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $safe_name;
    
    // 6️⃣ Déplacement du fichier
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception("Erreur lors de l'upload");
    }
    
    // 7️⃣ Enregistrement en BDD
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

**Mesures de sécurité :**
- ✅ Vérification du type MIME réel (pas juste l'extension)
- ✅ Limite de taille
- ✅ Renommage avec `uniqid()` (évite les conflits et injections)
- ✅ Vérification que c'est bien un fichier uploadé avec `move_uploaded_file()`
- ✅ Stockage du chemin en BDD (pas de lien direct)

## 6. Protection XSS (Cross-Site Scripting)

### Échappement des Données

**❌ Dangereux :**
```php
<h1><?= $title ?></h1>
```

Si `$title` contient `<script>alert('XSS')</script>`, le code JavaScript sera exécuté.

**✅ Sécurisé :**
```php
<h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
```

**Exemples du projet :**

```php
// Affichage d'un nom d'équipe
<span class="team-name"><?= htmlspecialchars($team['name']) ?></span>

// Affichage d'un titre d'actualité
<h2><?= htmlspecialchars($news['title']) ?></h2>

// Dans un attribut alt
<img src="..." alt="<?= htmlspecialchars($player['name']) ?>">
```

### Particularité : Contenu HTML Autorisé

Pour les actualités, je veux autoriser certaines balises HTML (gras, lien, etc.).

**Option 1 : strip_tags() avec whitelist**
```php
$allowed_tags = '<p><br><b><strong><i><em><a><ul><ol><li>';
$safe_content = strip_tags($content, $allowed_tags);
```

**Option 2 : Utiliser HTMLPurifier (bibliothèque)**
C'est ce que j'aurais dû faire si j'avais plus de temps. Pour l'instant, j'ai limité les dégâts avec `strip_tags()`.

## 7. Protection CSRF (Cross-Site Request Forgery)

### Token CSRF pour les Formulaires Critiques

**Génération du token :**
```php
// Au début de la session
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

**Vérification côté serveur :**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token
    $token = $_POST['csrf_token'] ?? '';
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        die("Token CSRF invalide");
    }
    
    // Traitement du formulaire...
}
```

**⚠️ Honnêteté :** Je n'ai pas implémenté de token CSRF partout dans mon projet par manque de temps. C'est une amélioration à faire !

## 8. Système de Permissions (Back-Office)

### Contrôle d'Accès Basé sur les Rôles

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
    
    // Vérification des permissions
    $allowed_sections = $roles[$role] ?? [];
    return in_array($section, $allowed_sections);
}
```

**Utilisation :**
```php
// Dans admin.php
$current_section = $_GET['section'] ?? 'dashboard';

if (!hasPermission($user_role, $current_section, $menu_items, $roles)) {
    // Pas de permission → redirection
    $current_section = 'dashboard';
}
```

### Vérification Avant Actions Sensibles

**Exemple : Suppression d'un utilisateur (admin uniquement)**
```php
if (isset($_GET['delete_user'])) {
    // Vérification du rôle
    if ($_SESSION['role'] !== 'ROLE_ADMIN') {
        die("Action non autorisée");
    }
    
    $id_user = (int)$_GET['delete_user'];
    
    // Empêcher la suppression de son propre compte
    if ($id_user === $_SESSION['user_id']) {
        die("Vous ne pouvez pas supprimer votre propre compte");
    }
    
    // Suppression
    $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = :id");
    $stmt->execute(['id' => $id_user]);
}
```

## 9. Protection des Données Sensibles

### Fichier config.php

**❌ Mauvaise pratique :**
- Commiter le fichier avec les identifiants en dur
- Laisser des mots de passe en clair

**✅ Bonne pratique (idéale) :**
- Utiliser un fichier `.env` (non versionné)
- Charger les variables avec `vlucas/phpdotenv`

**Ce que j'ai fait :**
```php
// config.php (ne devrait PAS être sur GitHub en prod)
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

### Désactivation de l'Affichage d'Erreurs (Production)

```php
// En développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En production (à mettre)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

### Headers de Sécurité (à améliorer)

```php
// À ajouter dans config.php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
```

### Timeout de Session

```php
// Expiration après 2 heures d'inactivité
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();
```

## Récapitulatif des Mesures

| Menace | Protection | Implémentée |
|--------|-----------|-------------|
| Injection SQL | Requêtes préparées PDO | ✅ |
| Vol de mot de passe | `password_hash()` / `password_verify()` | ✅ |
| Accès non autorisé | Vérification de session | ✅ |
| XSS | `htmlspecialchars()` | ✅ |
| Upload malveillant | Vérification type/taille | ✅ |
| Permissions | Système de rôles | ✅ |
| CSRF | Token CSRF | ⚠️ Partiel |
| Headers sécurité | Headers HTTP | ⚠️ À améliorer |

## Points d'Amélioration

Si j'avais plus de temps :
- Implémenter les tokens CSRF partout
- Ajouter une limite de tentatives de connexion (brute force protection)
- Utiliser HTTPS (certificat SSL)
- Mettre en place un système de logs de sécurité
- Utiliser une bibliothèque comme HTMLPurifier pour le contenu riche
- Ajouter une vérification email lors de l'inscription
- Implémenter un système de récupération de mot de passe sécurisé

Malgré ces améliorations possibles, je pense avoir mis en place les bases d'une sécurité correcte pour un projet de ce niveau !

---

## 11. Conformit� RGPD - Syst�me de Consentement Cookies

### Mise en Place d'une Banni�re de Consentement

J'ai impl�ment� un syst�me complet de gestion du consentement des cookies conforme au RGPD.

**Fichiers cr��s :**
- `public/assets/_front.css/cookie-consent.css` (styles de la banni�re)
- `public/assets/js/cookie-consent.js` (gestion JavaScript des cookies)
- Banni�re HTML dans `includes/footer.php`

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

**Int�gration avec le syst�me de tracking :**

``php
// includes/tracker.php
function enregistrerVisite($pdo) {
    //  RGPD : V�rification du consentement AVANT tracking
    if (!isset($_COOKIE['cookie_consent']) || $_COOKIE['cookie_consent'] !== 'accepted') {
        // L'utilisateur n'a pas accept�  On ne collecte RIEN
        return false;
    }
    
    // Si consentement donn�, on enregistre la visite
    $stmt = $pdo->prepare("
        INSERT INTO visites (ip_address, user_agent, page_url, ...)
        VALUES (:ip, :user_agent, :page_url, ...)
    ");
    // ...
}
``

**R�sultat :**
-  Aucune donn�e collect�e sans consentement explicite
-  Choix persistant (cookie valable 1 an)
-  Possibilit� de modifier son choix (lien "G�rer les cookies" dans le footer)

### Tests de Conformit� RGPD

J'ai cr�� deux pages de test pour d�montrer la conformit� :

**1. Page de test interactive :**
- URL : `_tests_securite/test_rgpd_cookies.php`
- Permet de tester l'acceptation/refus des cookies
- Affiche en temps r�el le nombre de visites en BDD
- Prouve que le refus bloque effectivement le tracking

**2. Page de d�bogage technique :**
- URL : `debug_cookies.php`
- Affiche l'�tat des cookies et de la session
- Permet de r�initialiser les tests
- Utile pour la d�monstration en live devant le jury

---

## 12. Limitations Identifi�es et Am�liorations Futures

### Conservation des Donn�es de Tracking

**Probl�me identifi� :** 

Les donn�es de la table `visites` sont actuellement conserv�es **ind�finiment** dans la base de donn�es. Aucun syst�me de nettoyage automatique n'est mis en place.

**Impact RGPD :**

Le R�glement G�n�ral sur la Protection des Donn�es stipule que les donn�es personnelles ne doivent �tre conserv�es que pour **la dur�e strictement n�cessaire** aux finalit�s du traitement.

Pour des statistiques de site web, la CNIL recommande une conservation de **12 � 24 mois maximum**.

**Donn�es concern�es :**
- Adresses IP (donn�es personnelles identifiantes)
- User-Agent (empreinte technique du visiteur)
- Pages visit�es (comportement de navigation)
- Syst�me d'exploitation et navigateur

**Solutions envisag�es :**

**1. Nettoyage manuel (d�j� possible) :**
``sql
-- Supprimer les visites de plus de 12 mois
DELETE FROM visites 
WHERE date_visite < DATE_SUB(NOW(), INTERVAL 12 MONTH);
``

**2. Nettoyage automatique via t�che CRON (recommand� en production) :**
``bash
# Ex�cuter chaque nuit � 2h du matin
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
error_log("Nettoyage RGPD : $deleted visites supprim�es");
?>
``

**3. Bouton de nettoyage dans le back-office (option pratique) :**
- Ajouter une section "Param�tres RGPD" dans le back-office
- Bouton " Nettoyer les anciennes visites (> 12 mois)"
- Accessible uniquement aux administrateurs
- Affiche le nombre de lignes supprim�es

**4. Archivage avant suppression (option avanc�e) :**
- Cr�er une table `visites_archive`
- D�placer les anciennes donn�es avant suppression
- Permet de garder des statistiques historiques anonymis�es

**Priorit� :** 

**Moyenne** - Cette am�lioration n'�tait pas critique pour la phase de d�veloppement car :
1. La priorit� absolue �tait le **syst�me de consentement** (maintenant impl�ment� )
2. En phase de d�veloppement, conserver toutes les donn�es permet d'analyser les tendances
3. Le volume de donn�es reste faible pour un site en d�veloppement

**En production**, je recommanderais d'activer le nettoyage automatique (option 2) combin� � un bouton manuel (option 3) pour laisser le contr�le � l'administrateur.

### Anonymisation des Donn�es

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

**Avantage :** On garde les statistiques (pages visit�es, dates) sans les donn�es personnelles.

**Inconv�nient :** Moins d'informations pour l'analyse d�taill�e.

### D�claration CNIL

**Important :** En France, un site qui collecte des donn�es personnelles (IP, cookies) doit th�oriquement �tre d�clar� � la CNIL ou figurer dans le registre des traitements de l'entreprise (RGPD).

Pour ce projet scolaire, cette d�claration n'est pas n�cessaire, mais en production r�elle, il faudrait :
1. Tenir un registre des traitements
2. R�diger une politique de confidentialit� compl�te
3. Nommer un DPO (D�l�gu� � la Protection des Donn�es) si l'entreprise > 250 salari�s

### Documentation Utilisateur

J'ai cr�� une page "Politique de confidentialit�" accessible via le footer qui explique :
- Quelles donn�es sont collect�es
- Pourquoi elles sont collect�es
- Combien de temps elles sont conserv�es
- Comment exercer ses droits (acc�s, rectification, suppression)

---

## Conclusion sur la S�curit�

Malgr� ces quelques points d'am�lioration identifi�s, j'estime avoir mis en place un niveau de s�curit� **satisfaisant** pour un projet de ce niveau :

 **Points forts :**
- 100% des requ�tes SQL utilisent des requ�tes pr�par�es (z�ro risque d'injection SQL)
- Syst�me de consentement RGPD fonctionnel et test�
- Mots de passe hash�s avec bcrypt
- Upload de fichiers s�curis� avec v�rifications multiples
- Syst�me de r�les et permissions dans le back-office
- Protection XSS via htmlspecialchars()
- Tests de s�curit� cr��s pour d�monstration

 **Points � am�liorer (identifi�s et document�s) :**
- Tokens CSRF � g�n�raliser sur tous les formulaires
- Conservation des donn�es limit�e dans le temps (RGPD)
- Headers de s�curit� HTTP � renforcer
- Limite de tentatives de connexion (anti brute-force)

**Mon approche :** Plut�t que de cacher ces limitations, je pr�f�re les **documenter clairement** et proposer des **solutions concr�tes**. Cela montre ma capacit� � analyser un syst�me, identifier ses failles, et planifier des am�liorations r�alistes.
