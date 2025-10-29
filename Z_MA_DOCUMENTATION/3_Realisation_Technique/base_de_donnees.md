# Base de Données - ES Moulon

## Structure de la Base de Données

La base de données `es_moulon` contient toutes les informations nécessaires au fonctionnement du site. J'ai essayé de bien normaliser les tables pour éviter les redondances tout en gardant des requêtes simples à écrire.

### Tables Principales

#### 1. `users` - Utilisateurs du back-office
Stocke les comptes des administrateurs et éditeurs.

```sql
CREATE TABLE users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role ENUM('ROLE_ADMIN', 'ROLE_EDITOR', 'ROLE_VISITOR') DEFAULT 'ROLE_VISITOR',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Points importants :**
- Le mot de passe est hashé avec `password_hash()` (bcrypt)
- Le rôle détermine les permissions dans le back-office
- `is_active` permet de désactiver un compte sans le supprimer

#### 2. `news` - Actualités
Contient toutes les actualités publiées sur le site.

```sql
CREATE TABLE news (
    id_news INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content TEXT,
    excerpt TEXT,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_author INT,
    id_media INT,
    is_published BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_author) REFERENCES users(id_user),
    FOREIGN KEY (id_media) REFERENCES medias(id_media)
);
```

**Logique :**
- Le `slug` permet d'avoir des URLs propres (ex: `/actualite/victoire-importante`)
- Chaque article a une image principale (id_media)
- `is_published` permet de préparer des articles en brouillon
- L'auteur est lié à la table users

#### 3. `teams` - Équipes
Liste toutes les équipes (club + adversaires).

```sql
CREATE TABLE teams (
    id_team INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    level VARCHAR(100),
    category VARCHAR(100),
    id_club_team BOOLEAN DEFAULT FALSE,
    id_media INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_media) REFERENCES medias(id_media)
);
```

**Particularité :**
- `id_club_team` = 1 pour les équipes du club, 0 pour les adversaires
- Le logo de l'équipe est optionnel (id_media peut être NULL)
- `level` : R1, R2, U19, U17, etc.

#### 4. `players` - Joueurs
Tous les joueurs du club.

```sql
CREATE TABLE players (
    id_player INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    position VARCHAR(50),
    jersey_number INT,
    id_team INT,
    id_photo INT,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_team) REFERENCES teams(id_team),
    FOREIGN KEY (id_photo) REFERENCES medias(id_media)
);
```

**Détails :**
- Chaque joueur appartient à une équipe
- Photo optionnelle (certains joueurs n'ont pas de photo)
- `position` : Gardien, Défenseur, Milieu, Attaquant

#### 5. `matches` - Matchs
Historique et calendrier des rencontres.

```sql
CREATE TABLE matches (
    id_match INT PRIMARY KEY AUTO_INCREMENT,
    match_date DATETIME NOT NULL,
    id_home_team INT NOT NULL,
    id_away_team INT NOT NULL,
    home_score INT,
    away_score INT,
    match_type VARCHAR(100),
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_home_team) REFERENCES teams(id_team),
    FOREIGN KEY (id_away_team) REFERENCES teams(id_team)
);
```

**Logique des matchs :**
- Si `home_score` et `away_score` sont NULL → match à venir
- Si les scores sont renseignés → match joué
- `match_type` : Championnat, Coupe, Amical
- Au moins une des deux équipes doit être une équipe du club

#### 6. `staff` - Staff technique
Entraîneurs, éducateurs, etc.

```sql
CREATE TABLE staff (
    id_staff INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    id_team INT,
    id_photo INT,
    bio TEXT,
    email VARCHAR(255),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_team) REFERENCES teams(id_team),
    FOREIGN KEY (id_photo) REFERENCES medias(id_media)
);
```

#### 7. `partners` - Partenaires
Les sponsors et partenaires du club.

```sql
CREATE TABLE partners (
    id_partner INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    website_url VARCHAR(255),
    id_logo INT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_logo) REFERENCES medias(id_media)
);
```

**Utilité :**
- `display_order` permet de contrôler l'ordre d'affichage
- `is_active` pour masquer temporairement un partenaire

#### 8. `medias` - Médiathèque
Tous les fichiers uploadés (photos, logos).

```sql
CREATE TABLE medias (
    id_media INT PRIMARY KEY AUTO_INCREMENT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    title VARCHAR(255),
    alt_text VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_uploader INT,
    FOREIGN KEY (id_uploader) REFERENCES users(id_user)
);
```

**Gestion des médias :**
- Le chemin complet est stocké dans `file_path`
- `alt_text` pour l'accessibilité (images)
- On garde la trace de qui a uploadé le fichier

#### 9. `contacts` - Messages du formulaire
Stocke les messages envoyés via le formulaire de contact.

```sql
CREATE TABLE contacts (
    id_contact INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 10. `visits` - Statistiques de visite
Permet de compter les visites du site (j'ai ajouté ça pour le dashboard).

```sql
CREATE TABLE visits (
    id_visit INT PRIMARY KEY AUTO_INCREMENT,
    visit_date DATE NOT NULL,
    page_url VARCHAR(500),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Relations Entre les Tables

### Schéma des Relations

```
users (1) ----< (N) news
users (1) ----< (N) medias

teams (1) ----< (N) players
teams (1) ----< (N) staff
teams (1) ----< (N) matches (home_team)
teams (1) ----< (N) matches (away_team)

medias (1) ----< (N) news (image)
medias (1) ----< (N) teams (logo)
medias (1) ----< (N) players (photo)
medias (1) ----< (N) staff (photo)
medias (1) ----< (N) partners (logo)
```

### Clés Étrangères

J'ai défini des clés étrangères pour maintenir l'intégrité des données :
- Un joueur doit appartenir à une équipe existante
- Une actualité ne peut pas référencer un auteur supprimé (ou on met ON DELETE SET NULL)
- Un match doit avoir deux équipes valides

## Requêtes Importantes

### Récupérer le dernier résultat du club

```php
$stmt = $pdo->query("
    SELECT 
        m.*,
        home.name AS home_team_name,
        away.name AS away_team_name,
        mh.file_path AS home_logo,
        ma.file_path AS away_logo
    FROM matches m
    LEFT JOIN teams home ON m.id_home_team = home.id_team
    LEFT JOIN medias mh ON home.id_media = mh.id_media
    LEFT JOIN teams away ON m.id_away_team = away.id_team
    LEFT JOIN medias ma ON away.id_media = ma.id_media
    WHERE m.match_date < NOW() 
      AND m.home_score IS NOT NULL
      AND (home.id_club_team = 1 OR away.id_club_team = 1)
    ORDER BY m.match_date DESC 
    LIMIT 1
");
```

**Explication :**
- Je joins les tables `teams` et `medias` pour récupérer les noms et logos
- Condition : match déjà joué (date passée + scores renseignés)
- Une des deux équipes doit être du club (id_club_team = 1)
- Je trie par date descendante et prends le dernier

### Lister les joueurs d'une équipe avec leurs photos

```php
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        m.file_path AS photo_url
    FROM players p
    LEFT JOIN medias m ON p.id_photo = m.id_media
    WHERE p.id_team = :team_id
    ORDER BY p.position, p.jersey_number
");
$stmt->execute(['team_id' => $team_id]);
```

### Compter les actualités publiées

```php
$stmt = $pdo->query("
    SELECT COUNT(*) as total 
    FROM news 
    WHERE is_published = TRUE
");
$count = $stmt->fetch()['total'];
```

## Sécurité

### Injections SQL
J'utilise **toujours des requêtes préparées** quand il y a des données utilisateur :

```php
// ❌ MAUVAIS
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $pdo->query($sql);

// ✅ BON
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
```

### Gestion des Mots de Passe

```php
// Lors de l'inscription/création
$hash = password_hash($password, PASSWORD_DEFAULT);

// INSERT dans la BDD avec $hash

// Lors de la connexion
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($password_saisi, $user['password_hash'])) {
    // ✅ Connexion OK
}
```

## Points d'Amélioration

Si j'avais plus de temps, j'aurais pu :
- Ajouter une table `categories` pour classer les actualités
- Créer une table `comments` pour les commentaires sur les articles
- Mettre en place un système de cache avec une table dédiée
- Ajouter des index sur les colonnes fréquemment utilisées dans les WHERE

Mais pour un premier projet complet, je pense que cette structure est correcte et fonctionnelle !
