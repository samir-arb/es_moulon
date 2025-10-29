# Code Back-End - Explications

## Architecture du Projet

J'ai organisé mon code pour séparer les responsabilités et faciliter la maintenance. Voici comment j'ai structuré la partie back-end.

## 1. Configuration et Connexion BDD

### Fichier `includes/config.php`

C'est le fichier central qui est inclus partout. Il contient :

```php
<?php
// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration timezone
date_default_timezone_set('Europe/Paris');

// Détection environnement (local ou production)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$APP_DIR = '/es_moulon';

// URLs de base
define('BASE_URL', $scheme . '://' . $host . '/es_moulon/public');
define('ASSETS_URL', BASE_URL . '/assets');

// Chemins serveur
define('ROOT', dirname(__DIR__));
define('INC', ROOT . '/includes');
define('PAGES', ROOT . '/pages');
```

**Pourquoi ces constantes ?**
- `BASE_URL` : pour générer les liens corrects (menu, images)
- `ASSETS_URL` : accès aux CSS/JS/images
- `ROOT`, `INC`, `PAGES` : chemins absolus pour les includes

### Connexion à la base de données

J'utilise **PDO** (et pas mysqli) car :
- Plus sécurisé avec les requêtes préparées
- Compatible avec plusieurs types de BDD
- Gestion d'erreurs plus propre

```php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli('localhost', 'root', '1508Samir@bien', 'es_moulon');
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    die('Erreur de connexion MySQL : ' . $e->getMessage());
}

// Connexion PDO (celle que j'utilise principalement)
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=es_moulon;charset=utf8mb4',
        'root',
        '1508Samir@bien',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion PDO : ' . $e->getMessage());
}
```

**Options PDO importantes :**
- `ERRMODE_EXCEPTION` : lance des exceptions en cas d'erreur (plus facile à gérer)
- `FETCH_ASSOC` : retourne des tableaux associatifs par défaut
- `EMULATE_PREPARES => false` : vraies requêtes préparées côté serveur

### Fonctions Helper

```php
function asset(string $path = ''): string {
    if (strpos($path, 'assets/') === 0) {
        return BASE_URL . '/' . ltrim($path, '/');
    }
    if (strpos($path, 'uploads/') === 0) {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
    return rtrim(ASSETS_URL, '/') . '/' . ltrim($path, '/');
}

function url(string $path = ''): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
```

Ces fonctions me permettent de générer les URLs facilement :
```php
<img src="<?= asset('img/logo.png') ?>">
<a href="<?= url('actualites') ?>">Actualités</a>
```

## 2. Système de Routage

### Fichier `public/index.php`

J'ai créé un mini-système de routage pour avoir des URLs propres.

```php
// Récupération du chemin demandé
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

if (strpos($uriPath, $basePath) === 0) {
    $path = substr($uriPath, strlen($basePath));
} else {
    $path = $uriPath;
}
$path = trim($path, '/');

// Page par défaut
if ($path === '' || $path === 'index.php') { 
    $path = 'accueil'; 
}

// Table de routage
$map = [
    'accueil' => PAGES.'/accueil.php',
    'actualites' => PAGES.'/actualites.php',
    'actualite' => PAGES.'/actualite_detail.php',
    'Regional1/calendrier' => PAGES.'/Regional1/calendrier.php',
    // ... etc
];

$file = $map[$path] ?? null;
```

**Avantage :** 
- URLs lisibles : `/actualites` au lieu de `?page=actualites`
- Facile à maintenir (tout est dans un tableau)
- Gestion du 404 si la page n'existe pas

**Rendu de la page :**
```php
$title = 'ES Moulon';
ob_start();
if ($file && is_file($file)) {
    include $file;
} else {
    http_response_code(404);
    echo '<h1>404</h1><p>Page introuvable.</p>';
}
$content = ob_get_clean();

// Affichage avec template
include INC.'/header.php';
include INC.'/navbar.php';
echo '<main>'.$content.'</main>';
include INC.'/footer.php';
include INC.'/scripts.php';
```

J'utilise `ob_start()` / `ob_get_clean()` pour capturer le contenu de la page avant de l'afficher dans le template global.

## 3. Système d'Authentification

### Fichier `BO/_backoffice/auth/login.php`

La connexion vérifie l'email et le mot de passe.

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Récupération de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE email = :email AND is_active = TRUE
    ");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    
    // Vérification du mot de passe
    if ($user && password_verify($password, $user['password_hash'])) {
        // ✅ Connexion réussie
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['name'] = $user['last_name'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];
        
        header('Location: ../../admin.php');
        exit;
    } else {
        // ❌ Échec
        $error = "Email ou mot de passe incorrect";
    }
}
```

**Points de sécurité :**
- ✅ Requête préparée pour éviter les injections SQL
- ✅ `password_verify()` pour vérifier le hash
- ✅ Vérification que le compte est actif (`is_active = TRUE`)
- ✅ Pas de message détaillé sur l'erreur (on ne dit pas si c'est l'email ou le mot de passe qui est faux)

### Protection des pages admin

Fichier `BO/admin.php` :

```php
session_start();

// Redirection si non connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: _backoffice/auth/login.php");
    exit;
}

$user_role = $_SESSION['role'] ?? 'ROLE_VISITOR';
$current_section = $_GET['section'] ?? 'dashboard';
```

Toutes les pages du back-office commencent par cette vérification.

## 4. Système de Rôles et Permissions

### Fichier `BO/_backoffice/_core/roles.php`

J'ai créé un système simple pour gérer ce que chaque rôle peut faire.

```php
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
    // L'admin a tous les droits
    if ($role === 'ROLE_ADMIN') return true;
    
    // Vérification dans le tableau des permissions
    $allowed = $roles[$role] ?? [];
    return in_array($section, $allowed);
}
```

**Utilisation :**
```php
if (!hasPermission($user_role, $current_section, $menu_items, $roles)) {
    $current_section = 'dashboard'; // Redirection vers dashboard
}
```

## 5. Opérations CRUD

### Exemple : Gestion des Actualités

**CREATE - Ajouter une actualité**

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $id_author = $_SESSION['user_id'];
    
    // Génération du slug
    $slug = slugify($title);
    
    // Upload de l'image (si présente)
    $id_media = null;
    if (!empty($_FILES['image']['name'])) {
        $id_media = uploadImage($_FILES['image']);
    }
    
    // Insertion en BDD
    $stmt = $pdo->prepare("
        INSERT INTO news (title, slug, content, excerpt, is_published, id_author, id_media, published_at)
        VALUES (:title, :slug, :content, :excerpt, :published, :author, :media, NOW())
    ");
    
    $stmt->execute([
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'excerpt' => $excerpt,
        'published' => $is_published,
        'author' => $id_author,
        'media' => $id_media
    ]);
    
    header('Location: admin.php?section=news&success=add');
    exit;
}
```

**READ - Lister les actualités**

```php
$stmt = $pdo->query("
    SELECT 
        n.*,
        u.first_name, u.last_name,
        m.file_path AS image_path
    FROM news n
    LEFT JOIN users u ON n.id_author = u.id_user
    LEFT JOIN medias m ON n.id_media = m.id_media
    ORDER BY n.created_at DESC
");
$news_list = $stmt->fetchAll();
```

**UPDATE - Modifier une actualité**

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_news = (int)$_POST['id_news'];
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    $stmt = $pdo->prepare("
        UPDATE news 
        SET title = :title, 
            content = :content, 
            is_published = :published,
            updated_at = NOW()
        WHERE id_news = :id
    ");
    
    $stmt->execute([
        'title' => $title,
        'content' => $content,
        'published' => $is_published,
        'id' => $id_news
    ]);
    
    header('Location: admin.php?section=news&success=edit');
    exit;
}
```

**DELETE - Supprimer une actualité**

```php
if (isset($_GET['delete'])) {
    $id_news = (int)$_GET['delete'];
    
    // On pourrait vérifier les permissions ici
    
    $stmt = $pdo->prepare("DELETE FROM news WHERE id_news = :id");
    $stmt->execute(['id' => $id_news]);
    
    header('Location: admin.php?section=news&success=delete');
    exit;
}
```

## 6. Upload de Fichiers

### Fonction d'upload sécurisée

```php
function uploadImage($file) {
    global $pdo;
    
    // Vérification du type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Type de fichier non autorisé");
    }
    
    // Vérification de la taille (max 5 MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Fichier trop volumineux (max 5 MB)");
    }
    
    // Génération nom unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = uniqid() . '_' . time() . '.' . $extension;
    $upload_dir = __DIR__ . '/../../public/assets/uploads/';
    $upload_path = $upload_dir . $new_name;
    
    // Déplacement du fichier
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception("Erreur lors de l'upload");
    }
    
    // Enregistrement en BDD
    $stmt = $pdo->prepare("
        INSERT INTO medias (file_name, file_path, file_type, file_size)
        VALUES (:name, :path, :type, :size)
    ");
    
    $stmt->execute([
        'name' => $file['name'],
        'path' => 'uploads/' . $new_name,
        'type' => $file['type'],
        'size' => $file['size']
    ]);
    
    return $pdo->lastInsertId();
}
```

**Sécurité :**
- ✅ Vérification du type MIME
- ✅ Limite de taille
- ✅ Renommage du fichier (évite les conflits et injection)
- ✅ Stockage du chemin en BDD

## 7. Requêtes Complexes

### Affichage du dernier résultat sur la page d'accueil

```php
$stmt = $pdo->query("
    SELECT 
        m.id_match,
        m.match_date,
        m.home_score,
        m.away_score,
        m.match_type,
        th.name AS home_team_name,
        th.id_club_team AS home_is_club,
        mh.file_path AS home_logo,
        ta.name AS away_team_name,
        ta.id_club_team AS away_is_club,
        ma.file_path AS away_logo
    FROM matches m
    INNER JOIN teams th ON m.id_home_team = th.id_team
    INNER JOIN teams ta ON m.id_away_team = ta.id_team
    LEFT JOIN medias mh ON th.id_media = mh.id_media
    LEFT JOIN medias ma ON ta.id_media = ma.id_media
    WHERE (th.id_club_team = 1 OR ta.id_club_team = 1)
      AND m.home_score IS NOT NULL
      AND m.away_score IS NOT NULL
      AND m.match_date < NOW()
    ORDER BY m.match_date DESC
    LIMIT 1
");
$dernier_resultat = $stmt->fetch(PDO::FETCH_ASSOC);
```

**Explication :**
- `INNER JOIN` : je récupère les infos des deux équipes
- `LEFT JOIN` : les logos sont optionnels (peuvent être NULL)
- Conditions : match joué (scores renseignés, date passée) avec au moins une équipe du club
- `LIMIT 1` : je ne veux que le dernier

## 8. Gestion des Erreurs

J'ai configuré PHP pour afficher les erreurs en développement :

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Pour les requêtes SQL, j'utilise des `try/catch` :

```php
try {
    $stmt = $pdo->prepare("...");
    $stmt->execute([...]);
} catch (PDOException $e) {
    error_log('Erreur SQL : ' . $e->getMessage());
    die("Une erreur est survenue");
}
```

## Points d'Amélioration

Si j'avais plus de temps :
- Créer une vraie classe Database pour gérer les connexions
- Implémenter un système de cache (fichiers ou Redis)
- Ajouter des logs plus détaillés
- Utiliser un framework comme Laravel ou Symfony
- Créer une API REST pour séparer front et back

Mais pour un premier projet "from scratch", je suis content du résultat !
