# Code Front-End - Explications

## Architecture HTML/CSS

Pour le front-end, j'ai voulu faire quelque chose de moderne et responsive sans utiliser de framework comme Bootstrap. J'ai tout codé "à la main" pour bien comprendre les mécanismes.

## 1. Structure HTML

### Header du site

Fichier `includes/header.php` :

```php
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ES Moulon - Club de football">
    <title><?= $title ?? 'ES Moulon' ?></title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('_front.css/styles.css') ?>">
    <link rel="stylesheet" href="<?= asset('_front.css/actualites.css') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
</head>
<body class="<?= $pageClass ?? '' ?>">
```

**Points importants :**
- `viewport` : essentiel pour le responsive
- `$title` dynamique : chaque page peut définir son titre
- `$pageClass` : permet d'ajouter une classe CSS spécifique par page

### Navbar

Fichier `includes/navbar.php` - menu de navigation responsive :

```php
<nav class="navbar">
    <div class="navbar__container">
        <a href="<?= url('accueil') ?>" class="navbar__logo">
            <img src="<?= asset('img/logo.png') ?>" alt="ES Moulon">
        </a>
        
        <ul class="navbar__menu">
            <li class="navbar__item navbar__item--dropdown">
                <a href="#">Le Club</a>
                <ul class="navbar__submenu">
                    <li><a href="<?= url('Le_club/histoire_et_valeurs') ?>">Histoire et Valeurs</a></li>
                    <li><a href="<?= url('Le_club/infos_pratiques') ?>">Infos Pratiques</a></li>
                    <li><a href="<?= url('Le_club/organigrammes') ?>">Organigramme</a></li>
                </ul>
            </li>
            
            <li class="navbar__item">
                <a href="<?= url('actualites') ?>">Actualités</a>
            </li>
            
            <!-- etc... -->
        </ul>
        
        <!-- Bouton hamburger pour mobile -->
        <button class="navbar__toggle" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>
```

**Logique :**
- Menu classique en desktop
- Burger menu en mobile (JavaScript pour ouvrir/fermer)
- Sous-menus déroulants (dropdown)

## 2. CSS - Organisation

J'ai séparé mes fichiers CSS par "zone" :

```
assets/_front.css/
├── styles.css         (styles généraux + navbar + footer)
├── actualites.css     (page actualités et détail)
├── formulaires.css    (styles des formulaires)
└── generics.css       (utilitaires)
```

### Styles Généraux

```css
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --primary-color: #1a4d2e;
    --secondary-color: #ffa500;
    --text-color: #333;
    --bg-light: #f5f5f5;
    --transition: all 0.3s ease;
}

body {
    font-family: 'Arial', sans-serif;
    color: var(--text-color);
    line-height: 1.6;
}
```

**Pourquoi des variables CSS ?**
- Facile de changer les couleurs principales
- Cohérence sur tout le site
- Maint enabilité

### Responsive Design

J'ai utilisé une approche **mobile-first** : le CSS de base est pour mobile, puis j'ajoute des media queries pour les écrans plus grands.

```css
/* Base (mobile) */
.container {
    width: 100%;
    padding: 0 15px;
}

.navbar__menu {
    display: none; /* Masqué par défaut sur mobile */
    flex-direction: column;
}

/* Tablette */
@media (min-width: 768px) {
    .container {
        max-width: 720px;
        margin: 0 auto;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .container {
        max-width: 1200px;
    }
    
    .navbar__menu {
        display: flex; /* Visible en desktop */
        flex-direction: row;
    }
    
    .navbar__toggle {
        display: none; /* Burger caché en desktop */
    }
}
```

### Flexbox et Grid

J'ai beaucoup utilisé **Flexbox** pour les layouts :

```css
/* Cards des matchs sur l'accueil */
.match-cards {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.match-card {
    flex: 1;
    min-width: 300px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 20px;
}
```

Pour la grille des actualités :

```css
.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 40px;
}
```

**Avantage de Grid :**
- Grille responsive automatique avec `auto-fill`
- Colonnes de taille égale
- Espacement uniforme avec `gap`

## 3. JavaScript - Interactivité

J'ai écrit du JavaScript vanilla (pas de jQuery ni framework) pour gérer l'interactivité.

### Menu Mobile

Fichier `includes/scripts.php` :

```javascript
const navbarToggle = document.querySelector('.navbar__toggle');
const navbarMenu = document.querySelector('.navbar__menu');

navbarToggle.addEventListener('click', () => {
    navbarMenu.classList.toggle('active');
    navbarToggle.classList.toggle('active');
});

// Fermeture automatique quand on clique sur un lien
document.querySelectorAll('.navbar__menu a').forEach(link => {
    link.addEventListener('click', () => {
        navbarMenu.classList.remove('active');
        navbarToggle.classList.remove('active');
    });
});
```

**Logique :**
- Clic sur le bouton hamburger → toggle la classe `active`
- En CSS, `.navbar__menu.active` affiche le menu
- Clic sur un lien → ferme automatiquement le menu

### Gestion des Sous-Menus

```javascript
document.querySelectorAll('.navbar__item--dropdown').forEach(item => {
    const link = item.querySelector('a');
    const submenu = item.querySelector('.navbar__submenu');
    
    // Sur mobile : toggle au clic
    link.addEventListener('click', (e) => {
        if (window.innerWidth < 1024) {
            e.preventDefault();
            submenu.classList.toggle('show');
        }
    });
});
```

Sur desktop, les sous-menus s'ouvrent au hover (géré en CSS). Sur mobile, c'est au clic.

### Validation de Formulaire

Formulaire de contact (`pages/Rejoignez_nous/nous_contactez.php`) :

```javascript
const form = document.querySelector('.contact-form');

form.addEventListener('submit', (e) => {
    const name = document.querySelector('#name').value.trim();
    const email = document.querySelector('#email').value.trim();
    const message = document.querySelector('#message').value.trim();
    
    if (name === '' || email === '' || message === '') {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires');
        return false;
    }
    
    // Validation email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Veuillez entrer une adresse email valide');
        return false;
    }
    
    return true;
});
```

**Validation côté client :**
- ✅ Champs obligatoires
- ✅ Format email
- ⚠️ Attention : ça ne remplace pas la validation côté serveur !

### Animation au Scroll

Sur la page d'accueil, certains éléments apparaissent progressivement au scroll :

```javascript
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
        }
    });
}, observerOptions);

document.querySelectorAll('.animate-on-scroll').forEach(el => {
    observer.observe(el);
});
```

Et le CSS associé :

```css
.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.animate-on-scroll.fade-in {
    opacity: 1;
    transform: translateY(0);
}
```

## 4. Cards des Matchs (Page d'Accueil)

C'est une partie que j'ai soignée car c'est la première chose qu'on voit.

### HTML

```php
<?php if ($dernier_resultat): ?>
<div class="match-card match-card--result">
    <h3 class="match-card__title">Dernier Résultat</h3>
    <div class="match-card__date">
        <?= date('d/m/Y à H:i', strtotime($dernier_resultat['match_date'])) ?>
    </div>
    
    <div class="match-card__teams">
        <!-- Équipe domicile -->
        <div class="team">
            <?php if (!empty($dernier_resultat['home_logo'])): ?>
                <img src="<?= asset($dernier_resultat['home_logo']) ?>" alt="<?= $dernier_resultat['home_team_name'] ?>">
            <?php else: ?>
                <span class="team__emoji">⚽</span>
            <?php endif; ?>
            <span class="team__name"><?= $dernier_resultat['home_team_name'] ?></span>
        </div>
        
        <!-- Score -->
        <div class="match-card__score">
            <span class="score"><?= $dernier_resultat['home_score'] ?></span>
            <span class="separator">-</span>
            <span class="score"><?= $dernier_resultat['away_score'] ?></span>
        </div>
        
        <!-- Équipe extérieur -->
        <div class="team">
            <?php if (!empty($dernier_resultat['away_logo'])): ?>
                <img src="<?= asset($dernier_resultat['away_logo']) ?>" alt="<?= $dernier_resultat['away_team_name'] ?>">
            <?php else: ?>
                <span class="team__emoji">🔴</span>
            <?php endif; ?>
            <span class="team__name"><?= $dernier_resultat['away_team_name'] ?></span>
        </div>
    </div>
</div>
<?php endif; ?>
```

### CSS des Cards

```css
.match-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}

.match-card:hover {
    transform: translateY(-5px);
}

.match-card__teams {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
}

.team {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.team img {
    width: 60px;
    height: 60px;
    object-fit: contain;
    background: white;
    border-radius: 50%;
    padding: 10px;
}

.team__emoji {
    font-size: 50px;
}

.match-card__score {
    display: flex;
    align-items: center;
    gap: 15px;
}

.score {
    font-size: 48px;
    font-weight: bold;
}
```

**Effet visuel :**
- Dégradé de couleur pour le fond
- Ombre portée
- Animation au survol (légère élévation)
- Logos en cercle avec fond blanc

## 5. Page Actualités

### Grille d'Actualités

```php
<div class="news-grid">
    <?php foreach ($news_list as $news): ?>
    <article class="news-card">
        <?php if (!empty($news['image_path'])): ?>
        <div class="news-card__image">
            <img src="<?= asset($news['image_path']) ?>" alt="<?= htmlspecialchars($news['title']) ?>">
        </div>
        <?php endif; ?>
        
        <div class="news-card__content">
            <h2 class="news-card__title">
                <a href="<?= url('actualite?slug=' . $news['slug']) ?>">
                    <?= htmlspecialchars($news['title']) ?>
                </a>
            </h2>
            
            <p class="news-card__excerpt">
                <?= htmlspecialchars($news['excerpt']) ?>
            </p>
            
            <div class="news-card__meta">
                <span class="news-card__date">
                    <?= date('d/m/Y', strtotime($news['published_at'])) ?>
                </span>
                <a href="<?= url('actualite?slug=' . $news['slug']) ?>" class="news-card__link">
                    Lire la suite →
                </a>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
</div>
```

### CSS

```css
.news-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: var(--transition);
}

.news-card:hover {
    box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    transform: translateY(-3px);
}

.news-card__image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.news-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.news-card:hover .news-card__image img {
    transform: scale(1.05); /* Zoom léger au survol */
}

.news-card__content {
    padding: 20px;
}

.news-card__title a {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.news-card__title a:hover {
    color: var(--secondary-color);
}
```

**Effets :**
- Zoom de l'image au survol
- Élévation de la card
- Changement de couleur du titre

## 6. Formulaires

### Style des Inputs

```css
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: var(--text-color);
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(26, 77, 46, 0.1);
}

.form-button {
    background: var(--primary-color);
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: var(--transition);
}

.form-button:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
```

## 7. Accessibilité

J'ai essayé de respecter quelques bonnes pratiques :

```html
<!-- Attributs alt sur les images -->
<img src="..." alt="Description de l'image">

<!-- Labels associés aux inputs -->
<label for="email">Email</label>
<input type="email" id="email" name="email">

<!-- Attributs ARIA pour le menu mobile -->
<button class="navbar__toggle" aria-label="Ouvrir le menu">

<!-- Titres hiérarchisés (h1, h2, h3...) -->
<h1>Titre principal</h1>
<h2>Section</h2>
<h3>Sous-section</h3>
```

## 8. Performance

### Optimisation des images
- Images compressées avant upload
- Utilisation de `object-fit: cover` pour garder les proportions
- Lazy loading natif : `<img loading="lazy">`

### CSS optimisé
- Pas de framework lourd (Bootstrap fait ~150KB)
- CSS modulaire (on ne charge que ce dont on a besoin)
- Variables CSS pour éviter la répétition

## Points d'Amélioration

Si je devais améliorer le front-end :
- Ajouter des animations plus poussées (avec GSAP par exemple)
- Utiliser un pré-processeur CSS (SASS/SCSS)
- Implémenter un système de dark mode
- Améliorer encore l'accessibilité (tests avec un lecteur d'écran)
- Optimiser les performances (minification CSS/JS, sprites CSS)

Mais globalement, je suis satisfait du résultat : le site est moderne, responsive et fluide !
