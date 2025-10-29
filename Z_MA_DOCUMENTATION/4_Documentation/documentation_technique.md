# Documentation Technique - Site ES Moulon

## Vue d'Ensemble du Projet

Le site ES Moulon est une application web complète développée en PHP/MySQL permettant la gestion d'un club de football. Il se compose de deux parties :
- **Front-office** : Site public accessible à tous
- **Back-office** : Interface d'administration sécurisée

## Technologies Utilisées

### Langages
- **PHP 8+** : Logique serveur
- **MySQL** : Base de données
- **HTML5** : Structure des pages
- **CSS3** : Mise en forme
- **JavaScript** : Interactivité

### Outils de Développement
- **Laragon** : Environnement de développement local (Apache + MySQL + PHP)
- **VS Code** : Éditeur de code
- **Git / GitHub** : Gestion de versions
- **Mailpit** : Tests d'envoi d'emails en local

## Architecture du Projet

### Structure des Dossiers

```
ES_Moulon/
│
├── public/                     # Point d'entrée public
│   ├── index.php              # Routeur principal
│   ├── traitement_contact.php # Traitement formulaire contact
│   └── assets/                # Ressources statiques
│       ├── _front.css/        # Styles front-office
│       ├── _back.css/         # Styles back-office
│       ├── img/               # Images fixes
│       ├── picto/             # Icônes
│       └── uploads/           # Fichiers uploadés
│
├── pages/                      # Pages du front-office
│   ├── accueil.php
│   ├── actualites.php
│   ├── actualite_detail.php
│   ├── partenaires.php
│   ├── Le_club/               # Section "Le Club"
│   ├── Nos_equipes/           # Section "Nos Équipes"
│   ├── Regional1/             # Section "Régional 1"
│   └── Rejoignez_nous/        # Section "Rejoignez-nous"
│
├── includes/                   # Fichiers partagés
│   ├── config.php             # Configuration globale
│   ├── header.php             # Header HTML
│   ├── navbar.php             # Menu de navigation
│   ├── footer.php             # Pied de page
│   ├── scripts.php            # Scripts JS
│   ├── tracker.php            # Suivi des visites
│   └── EmailService.php       # Service d'envoi d'emails
│
├── BO/                         # Back-office
│   ├── admin.php              # Point d'entrée admin
│   └── _backoffice/
│       ├── _core/             # Logique métier
│       │   ├── functions.php
│       │   ├── roles.php
│       │   ├── menu.php
│       │   └── dashboard_data.php
│       ├── _includes/         # Templates back-office
│       │   ├── header_back.php
│       │   ├── sidebar.php
│       │   └── script_back.php
│       ├── _sections/         # Sections admin
│       │   ├── dashboard.php
│       │   ├── news.php
│       │   ├── joueurs.php
│       │   ├── equipes.php
│       │   ├── staff.php
│       │   ├── calendrier.php
│       │   ├── medias.php
│       │   ├── partenaires.php
│       │   ├── utilisateurs.php
│       │   └── ... (autres sections)
│       └── auth/              # Authentification
│           ├── login.php
│           ├── logout.php
│           ├── forgot.php
│           └── reset.php
│
└── RNCP_DWWM/                  # Dossier professionnel
    └── ... (documentation)
```

## Installation et Configuration

### Prérequis

- **PHP** : Version 8.0 ou supérieure
- **MySQL** : Version 5.7 ou supérieure
- **Serveur Web** : Apache (avec mod_rewrite) ou Nginx
- **Composer** : (optionnel, pour les dépendances futures)

### Installation en Local

#### 1. Cloner le Projet

```bash
git clone https://github.com/votre-user/es_moulon.git
cd es_moulon
```

#### 2. Configuration de la Base de Données

Créer une base de données MySQL :

```sql
CREATE DATABASE es_moulon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importer le schéma de base (si vous avez un fichier SQL) :

```bash
mysql -u root -p es_moulon < database/schema.sql
```

Ou créer les tables manuellement (voir le fichier `3_Realisation_Technique/base_de_donnees.md`).

#### 3. Configuration du Fichier config.php

Éditer le fichier `includes/config.php` :

```php
// Connexion BDD
$pdo = new PDO(
    'mysql:host=localhost;dbname=es_moulon;charset=utf8mb4',
    'votre_user',        // Modifier avec votre utilisateur MySQL
    'votre_password',    // Modifier avec votre mot de passe
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);
```

Adapter les URLs selon votre environnement :

```php
// Si votre projet est dans un sous-dossier
$APP_DIR = '/es_moulon';  // ou '' si à la racine

// URL de base
define('BASE_URL', $scheme . '://' . $host . ($isLocal ? $APP_DIR . '/public' : ''));
```

#### 4. Permissions des Dossiers

Donner les droits d'écriture au dossier uploads :

```bash
chmod 755 public/assets/uploads
```

#### 5. Créer un Compte Admin

Insérer un premier utilisateur admin en BDD :

```sql
INSERT INTO users (email, password_hash, first_name, last_name, role, is_active)
VALUES (
    'admin@esmoulon.fr',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'Admin',
    'ES Moulon',
    'ROLE_ADMIN',
    TRUE
);
```

**⚠️ Changer le mot de passe après la première connexion !**

#### 6. Accéder au Site

- **Front-office** : `http://localhost/es_moulon/public`
- **Back-office** : `http://localhost/es_moulon/public/admin.php` ou via le lien dans le footer

## Fonctionnement du Routage

### Front-Office (index.php)

Le fichier `public/index.php` agit comme un routeur simple :

```php
// Extraction du chemin demandé
$path = trim($_GET['path'] ?? 'accueil', '/');

// Table de routage
$map = [
    'accueil' => PAGES.'/accueil.php',
    'actualites' => PAGES.'/actualites.php',
    'Regional1/calendrier' => PAGES.'/Regional1/calendrier.php',
    // ... etc
];

// Chargement de la page
$file = $map[$path] ?? null;
if ($file && file_exists($file)) {
    include $file;
} else {
    // 404
}
```

**URLs résultantes :**
- `/accueil` → accueil.php
- `/actualites` → actualites.php
- `/Regional1/calendrier` → Regional1/calendrier.php

### Back-Office (admin.php)

Le back-office utilise des paramètres GET :

```php
$section = $_GET['section'] ?? 'dashboard';
$file = __DIR__ . '/_backoffice/_sections/' . $section . '.php';
```

**URLs :**
- `admin.php?section=dashboard`
- `admin.php?section=news`
- `admin.php?section=joueurs`

## Base de Données

### Tables Principales

#### users
Comptes administrateurs du back-office.

**Colonnes :**
- `id_user` : Identifiant unique
- `email` : Email (unique)
- `password_hash` : Mot de passe hashé
- `first_name` : Prénom
- `last_name` : Nom
- `role` : Rôle (ADMIN, EDITOR, VISITOR)
- `is_active` : Compte actif ou non

#### news
Articles/actualités du site.

**Colonnes :**
- `id_news` : Identifiant
- `title` : Titre
- `slug` : URL-friendly
- `content` : Contenu HTML
- `excerpt` : Résumé
- `is_published` : Publié ou brouillon
- `id_author` : Lien vers users
- `id_media` : Image principale
- `published_at` : Date de publication

#### teams
Équipes (club + adversaires).

**Colonnes :**
- `id_team` : Identifiant
- `name` : Nom de l'équipe
- `level` : Niveau (R1, R2, U19...)
- `category` : Catégorie
- `id_club_team` : 1 si équipe du club, 0 sinon
- `id_media` : Logo

#### players
Joueurs du club.

**Colonnes :**
- `id_player` : Identifiant
- `first_name`, `last_name` : Nom/prénom
- `date_of_birth` : Date de naissance
- `position` : Poste
- `jersey_number` : Numéro de maillot
- `id_team` : Équipe
- `id_photo` : Photo du joueur
- `bio` : Biographie

#### matches
Matchs joués ou à venir.

**Colonnes :**
- `id_match` : Identifiant
- `match_date` : Date/heure du match
- `id_home_team` : Équipe domicile
- `id_away_team` : Équipe extérieur
- `home_score`, `away_score` : Scores (NULL si pas joué)
- `match_type` : Type (Championnat, Coupe...)
- `location` : Lieu

#### staff
Membres du staff technique.

**Colonnes similaires à players**

#### partners
Partenaires/sponsors.

**Colonnes :**
- `id_partner` : Identifiant
- `name` : Nom du partenaire
- `description` : Description
- `website_url` : Site web
- `id_logo` : Logo
- `display_order` : Ordre d'affichage
- `is_active` : Actif ou non

#### medias
Fichiers uploadés (photos, logos).

**Colonnes :**
- `id_media` : Identifiant
- `file_name` : Nom original
- `file_path` : Chemin relatif
- `file_type` : Type MIME
- `file_size` : Taille en octets
- `title`, `alt_text` : Métadonnées
- `id_uploader` : Qui a uploadé

#### contacts
Messages du formulaire de contact.

**Colonnes :**
- `id_contact` : Identifiant
- `name`, `email` : Coordonnées
- `subject` : Sujet
- `message` : Message
- `is_read` : Lu ou non
- `created_at` : Date d'envoi

#### visits
Statistiques de visite (simple).

**Colonnes :**
- `id_visit` : Identifiant
- `visit_date` : Date
- `page_url` : URL visitée
- `ip_address` : IP du visiteur
- `user_agent` : Navigateur

## Système d'Authentification

### Connexion

1. Utilisateur saisit email + mot de passe
2. Recherche en BDD avec email
3. Vérification du hash avec `password_verify()`
4. Si OK : création de la session avec les infos user
5. Redirection vers le dashboard

### Vérification sur Chaque Page Admin

```php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
```

### Système de Rôles

Trois rôles définis :
- **ROLE_ADMIN** : Accès complet
- **ROLE_EDITOR** : Gestion du contenu (news, joueurs, etc.)
- **ROLE_VISITOR** : Accès en lecture seule

La fonction `hasPermission()` vérifie les droits avant d'afficher une section.

## Gestion des Erreurs

### Mode Développement

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Mode Production (à configurer)

```php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');
```

### Gestion des Erreurs PDO

```php
try {
    $stmt = $pdo->prepare("...");
    $stmt->execute([...]);
} catch (PDOException $e) {
    error_log('Erreur SQL : ' . $e->getMessage());
    die("Une erreur est survenue. Veuillez réessayer.");
}
```

## Performances

### Optimisations Implémentées

- Requêtes SQL optimisées (pas de SELECT *)
- Utilisation de `LEFT JOIN` pour les relations optionnelles
- Images compressées
- CSS modulaire (chargement seulement du nécessaire)
- Mise en cache des données du dashboard (fichier JSON)

### Améliorations Possibles

- Mise en cache avec Redis ou Memcached
- Minification CSS/JS
- Lazy loading des images
- CDN pour les assets
- Compression Gzip

## Sécurité

### Mesures en Place

✅ Requêtes préparées (PDO)  
✅ Hashage des mots de passe avec bcrypt  
✅ Vérification des sessions  
✅ Échappement HTML (`htmlspecialchars`)  
✅ Validation des uploads (type, taille)  
✅ Système de permissions par rôle  

### À Améliorer

⚠️ Tokens CSRF sur tous les formulaires  
⚠️ Limite de tentatives de connexion  
⚠️ HTTPS (certificat SSL)  
⚠️ Headers de sécurité HTTP  

## Maintenance

### Sauvegardes

**Base de données :**
```bash
mysqldump -u root -p es_moulon > backup_es_moulon_$(date +%Y%m%d).sql
```

**Fichiers uploadés :**
```bash
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz public/assets/uploads/
```

### Logs

Les logs PHP sont dans le fichier configuré par `error_log`.

Pour Apache (Laragon) :
```
laragon/bin/apache/apache-x.x.x/logs/error.log
```

### Mises à Jour

1. Sauvegarder la BDD et les fichiers
2. Tester en local
3. Déployer sur le serveur
4. Vérifier le fonctionnement

## Dépannage

### "Page blanche"

➡️ Vérifier les logs d'erreur PHP  
➡️ Activer `display_errors` temporairement  
➡️ Vérifier la connexion BDD dans config.php  

### "Session not started"

➡️ Vérifier que `session_start()` est appelé  
➡️ Vérifier les permissions du dossier de session PHP  

### Images non affichées

➡️ Vérifier les permissions du dossier uploads  
➡️ Vérifier les chemins dans la BDD (colonne file_path)  
➡️ Vérifier la fonction `asset()` dans config.php  

### Erreur de connexion BDD

➡️ Vérifier les identifiants dans config.php  
➡️ Vérifier que MySQL est démarré  
➡️ Vérifier que la BDD existe  

## Ressources et Documentation

### Documentations Officielles

- [PHP Manual](https://www.php.net/manual/fr/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [MDN Web Docs](https://developer.mozilla.org/) (HTML/CSS/JS)

### Outils Utiles

- **phpMyAdmin** : Gestion de la BDD (inclus dans Laragon)
- **Mailpit** : Test d'emails en local (inclus dans Laragon)
- **Git** : Gestion de versions

## Contact et Support

Pour toute question sur le projet :
- **Développeur** : [Votre Nom]
- **Email** : [Votre Email]
- **GitHub** : [Lien vers le repo]

---

*Documentation technique mise à jour le 28 octobre 2025*
