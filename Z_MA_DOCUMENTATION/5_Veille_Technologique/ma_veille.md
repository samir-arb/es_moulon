# Ma Veille Technologique

## Pourquoi la Veille ?

Quand j'ai commencé le développement web, je me suis vite rendu compte que ce domaine évolue très rapidement. Ce qui était moderne il y a 2 ans peut être dépassé aujourd'hui. J'ai donc compris l'importance de rester à jour.

La veille technologique, c'est pas juste "lire des articles de temps en temps". C'est vraiment une démarche active pour :
- Découvrir de nouvelles technologies
- Comprendre les évolutions du web
- Améliorer mes pratiques de développement
- Résoudre des problèmes rencontrés dans mes projets
- Anticiper les futures compétences à acquérir

## Ma Méthode de Veille

### Organisation

Je consacre environ **2-3 heures par semaine** à la veille, réparties comme ça :
- **Tous les matins (15 min)** : Lecture rapide de mes newsletters
- **2 fois par semaine (1h)** : Lecture approfondie d'articles / visionnage de vidéos
- **Le weekend** : Expérimentation si j'ai découvert quelque chose d'intéressant

### Mes Sources Principales

#### Sites Web & Blogs

**En français :**
- **Grafikart.fr** : Excellents tutoriels PHP, JavaScript, CSS. J'ai beaucoup appris grâce à ses vidéos.
- **OpenClassrooms** : Pour approfondir certains concepts
- **Alsacreations** : Bons articles sur HTML/CSS et accessibilité
- **PHP.net** : Documentation officielle (que je consulte régulièrement)

**En anglais :**
- **CSS-Tricks** : Pour tout ce qui touche au CSS (Flexbox, Grid, animations)
- **Dev.to** : Communauté de développeurs avec plein d'articles variés
- **MDN Web Docs** : LA référence pour HTML/CSS/JavaScript
- **Smashing Magazine** : Articles de qualité sur le webdesign et le dev

#### YouTube

- **Grafikart** (FR) : Tutoriels PHP, JavaScript, workflows
- **The Net Ninja** (EN) : Tutoriels sur pleins de technos
- **Traversy Media** (EN) : Projets complets, très pédagogique
- **Kevin Powell** (EN) : Spécialiste CSS, très bon pour le responsive

#### Newsletters

- **JavaScript Weekly** : Toutes les nouveautés JS
- **CSS Weekly** : Astuces et articles CSS
- **PHP Annotated Monthly** : Actualités PHP (par JetBrains)

#### Réseaux Sociaux

- **Twitter/X** : Je suis des dev influents (Dan Abramov, Wes Bos, etc.)
- **Reddit** : r/webdev, r/PHP, r/learnprogramming
- **LinkedIn** : Groupes de dev web

### Stockage et Organisation

J'utilise plusieurs outils :
- **Notion** : Pour organiser mes notes et fiches de veille
- **Pocket** : Pour sauvegarder les articles à lire plus tard
- **GitHub Stars** : Pour marquer les dépôts intéressants
- **Carnet papier** : Pour les idées qui me viennent en lisant

## Thèmes de Veille

### 1. PHP & Back-End

**Ce que je suis :**
- Nouveautés de PHP (je suis passé de PHP 7 à PHP 8 pendant ma formation)
- Bonnes pratiques (PSR, design patterns)
- Frameworks : Laravel, Symfony (même si j'utilise pas encore)
- Sécurité web (injections SQL, XSS, CSRF)

**Exemple d'apprentissage concret :**
J'ai découvert les **types de retour stricts** en PHP 8 :
```php
function calculateAge(string $birthdate): int {
    // ...
}
```
Ça m'a aidé à écrire du code plus robuste.

### 2. Front-End (HTML/CSS/JS)

**Ce que je suis :**
- CSS moderne (Flexbox, Grid, variables CSS)
- JavaScript ES6+ (arrow functions, destructuring, promises)
- Frameworks JS (React, Vue.js) - pour plus tard
- Accessibilité web (ARIA, sémantique HTML)
- Performance (lazy loading, optimisation images)

**Exemple d'apprentissage :**
J'ai appris le **CSS Grid** grâce à CSS-Tricks. Avant, je galerais avec les float et les tableaux. Grid a complètement changé ma façon de faire des layouts.

```css
.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}
```

### 3. Base de Données

- Optimisation des requêtes SQL
- Indexation
- Normalisation vs dénormalisation
- NoSQL (MongoDB) - par curiosité

**Ce que j'ai appris :**
L'importance des **index** sur les colonnes souvent utilisées dans les WHERE :
```sql
CREATE INDEX idx_match_date ON matches(match_date);
```
Ça a amélioré les performances de ma requête "dernier résultat".

### 4. Sécurité

C'est un sujet qui m'intéresse beaucoup.

**Ce que je suis :**
- Injections SQL
- XSS (Cross-Site Scripting)
- CSRF (Cross-Site Request Forgery)
- Gestion sécurisée des mots de passe
- Authentification / JWT

**Ressources utilisées :**
- OWASP Top 10 (les 10 vulnérabilités les plus critiques)
- Articles sur Dev.to
- Vidéos de LiveOverflow (pour comprendre les attaques)

### 5. Outils & Workflow

- Git / GitHub (j'apprends encore)
- VS Code (extensions utiles)
- DevTools (inspecteur, debugger)
- Outils de build (Webpack, Vite) - pour plus tard

## Fiches de Veille Détaillées

### Fiche 1 : Lazy Loading des Images

**Date** : Septembre 2025  
**Source** : MDN Web Docs + Article CSS-Tricks

#### Résumé
Le lazy loading permet de charger les images uniquement quand elles deviennent visibles dans le viewport. Ça améliore les performances, surtout sur mobile.

#### Ce que j'ai appris

**Version native HTML :**
```html
<img src="image.jpg" loading="lazy" alt="Description">
```

C'est incroyablement simple ! Supporté par tous les navigateurs modernes.

**Version JavaScript (plus de contrôle) :**
```javascript
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            observer.unobserve(img);
        }
    });
});

document.querySelectorAll('img[data-src]').forEach(img => {
    observer.observe(img);
});
```

#### Application dans mon projet

J'ai ajouté `loading="lazy"` sur toutes les images de la page actualités. Résultat : le temps de chargement initial est passé de **2,5s à 1,2s** (testé avec Lighthouse).

```php
<img src="<?= asset($news['image_path']) ?>" 
     alt="<?= htmlspecialchars($news['title']) ?>"
     loading="lazy">
```

#### Limitations découvertes

- Ne pas l'utiliser sur les images "above the fold" (visibles immédiatement)
- Peut créer un effet de "pop" si pas d'espace réservé

---

### Fiche 2 : Requêtes Préparées vs Requêtes Classiques

**Date** : Août 2025  
**Source** : PHP.net + Article sur la sécurité web

#### Résumé
Les requêtes préparées (prepared statements) sont essentielles pour éviter les injections SQL.

#### Ce que j'ai compris

**Le problème avec les requêtes classiques :**
```php
$email = $_POST['email'];
$query = "SELECT * FROM users WHERE email = '$email'";
```

Un attaquant peut injecter : `' OR '1'='1' --`  
Résultat : la requête retourne tous les utilisateurs !

**La solution : requêtes préparées :**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
```

PDO échappe automatiquement les caractères dangereux.

#### Application dans mon projet

J'ai utilisé des requêtes préparées **partout** dans mon projet. Pas une seule requête directe.

Exemples :
- Connexion utilisateur
- Ajout d'actualités
- Recherche de joueurs
- Insertion de matchs

#### Impact

✅ Sécurité renforcée  
✅ Code plus propre (séparation SQL / données)  
✅ Meilleure performance (requêtes compilées une fois)

---

### Fiche 3 : CSS Grid vs Flexbox

**Date** : Septembre 2025  
**Source** : Vidéo de Kevin Powell + CSS-Tricks

#### Résumé
Grid et Flexbox ne sont pas en compétition, ils sont complémentaires.

#### Différences clés

**Flexbox :**
- Layout **unidimensionnel** (ligne OU colonne)
- Parfait pour les barres de navigation, alignements simples
- Les items peuvent déborder sur plusieurs lignes avec `flex-wrap`

**Grid :**
- Layout **bidimensionnel** (lignes ET colonnes)
- Parfait pour les layouts complexes, les grilles d'articles
- Contrôle précis du positionnement

#### Quand utiliser quoi ?

**Flexbox pour :**
- Menu de navigation
- Alignement d'éléments dans une card
- Espacement entre items

**Grid pour :**
- Layout général de la page
- Grille d'actualités / produits
- Dashboards complexes

#### Application dans mon projet

**Flexbox :**
- Menu de navigation
```css
.navbar__menu {
    display: flex;
    gap: 20px;
}
```

- Cards de matchs
```css
.match-cards {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
```

**Grid :**
- Grille d'actualités
```css
.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}
```

#### Ce que j'ai retenu

> "Flexbox pour les composants, Grid pour les layouts"

---

### Fiche 4 : password_hash() en PHP

**Date** : Juillet 2025  
**Source** : PHP.net + Article sur la sécurité des mots de passe

#### Le problème

Stocker des mots de passe en clair = **TRÈS DANGEREUX**  
Même MD5 ou SHA1 sont dépassés (trop rapides à brute-force)

#### La solution moderne

```php
// Lors de l'inscription
$password = $_POST['password'];
$hash = password_hash($password, PASSWORD_DEFAULT);
// Résultat : $2y$10$N9qo8u...(60 caractères)

// Stockage en BDD
$stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
$stmt->execute([$email, $hash]);

// Lors de la connexion
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password_saisi, $user['password_hash'])) {
    // ✅ Connexion OK
}
```

#### Pourquoi c'est sécurisé ?

- Utilise **bcrypt** (algorithme robuste)
- Génère un **salt** unique automatiquement
- Coût adaptatif (peut être augmenté)
- Impossible de retrouver le mot de passe original

#### Application dans mon projet

Tous les mots de passe du back-office utilisent ce système :
- Création d'utilisateurs
- Connexion
- Réinitialisation de mot de passe

Stockage en BDD :
```sql
password_hash VARCHAR(255) -- bcrypt génère 60 caractères
```

---

### Fiche 5 : Mobile-First Responsive Design

**Date** : Août 2025  
**Source** : Smashing Magazine + vidéo de Traversy Media

#### Concept

Plutôt que de partir du desktop et adapter au mobile, on fait l'inverse :
1. Design pour mobile d'abord
2. Ajouter des améliorations pour tablette
3. Puis pour desktop

#### Avantages

- Force à se concentrer sur l'essentiel
- Meilleure performance sur mobile
- Plus facile d'ajouter des features que d'en retirer

#### En pratique (CSS)

```css
/* Base : mobile (pas de media query) */
.container {
    width: 100%;
    padding: 0 15px;
}

.navbar__menu {
    display: none; /* Menu caché sur mobile */
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
        display: flex; /* Menu visible en desktop */
    }
}
```

#### Application dans mon projet

Tout le CSS du site ES Moulon suit ce principe :
- Styles de base pour mobile (320px)
- Media queries pour tablette (768px+)
- Media queries pour desktop (1024px+)

Résultat : le site est parfaitement utilisable sur smartphone !

---

## Impact Global de la Veille sur Mon Projet

### Améliorations Techniques

Grâce à ma veille, j'ai pu :

✅ **Sécurité**
- Implémenter des requêtes préparées partout
- Utiliser `password_hash()` correctement
- Comprendre les risques XSS et les éviter

✅ **Performance**
- Lazy loading des images
- Optimisation des requêtes SQL
- CSS optimisé (pas de framework lourd)

✅ **Code Quality**
- Code plus propre et commenté
- Conventions de nommage cohérentes
- Séparation des responsabilités

✅ **Responsive**
- Approche mobile-first
- Site parfaitement adapté aux différents écrans
- Menu burger fonctionnel

### Compétences Acquises

- Lecture de documentation technique en anglais
- Capacité à tester et expérimenter rapidement
- Compréhension des concepts avancés (observers, promises, etc.)
- Curiosité et autonomie

## Veille Future

### Ce que je veux approfondir dans les prochains mois :

**Court terme (3-6 mois) :**
- [ ] Apprendre un framework PHP (Laravel ou Symfony)
- [ ] Me perfectionner en JavaScript (async/await, fetch API)
- [ ] Comprendre les APIs REST
- [ ] Git de façon plus avancée (branches, merge, rebase)

**Moyen terme (6-12 mois) :**
- [ ] Apprendre React ou Vue.js
- [ ] Docker et conteneurisation
- [ ] Tests automatisés (PHPUnit, Jest)
- [ ] CI/CD (intégration continue)

**Technologies à surveiller :**
- TypeScript (de plus en plus utilisé)
- TailwindCSS (framework CSS utilitaire)
- Next.js (framework React)
- Headless CMS (Strapi, Contentful)

## Conclusion

La veille technologique n'est pas une option, c'est une **nécessité** dans le développement web. Elle m'a permis de :
- Développer un projet de meilleure qualité
- Résoudre des problèmes complexes
- Rester motivé et curieux
- Me préparer pour le marché du travail

Je compte bien continuer cette démarche tout au long de ma carrière. Le web évolue vite, et c'est ce qui rend ce métier passionnant !

---

*Document de veille technologique - Octobre 2025*
