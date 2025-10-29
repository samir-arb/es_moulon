# Contexte du Projet ES Moulon

## Présentation du Club

L'ES Moulon est un club de football qui avait besoin d'une présence en ligne moderne. Le club compte plusieurs équipes (seniors, école de foot, pôles de formation) et souhaitait un site permettant de :
- Communiquer avec les supporters et les familles
- Gérer facilement le contenu sans compétences techniques
- Présenter les équipes, joueurs et staff
- Afficher les résultats et calendriers des matchs

## Analyse des Besoins

### Besoins fonctionnels

**Pour les visiteurs (front-office) :**
- Consulter les actualités du club
- Voir les prochains matchs et derniers résultats
- Découvrir l'histoire et les valeurs du club
- Consulter les effectifs des différentes équipes
- Trouver les informations pratiques (horaires, adresse, contacts)
- Voir les partenaires du club
- Contacter le club via un formulaire

**Pour les administrateurs (back-office) :**
- Se connecter de manière sécurisée
- Publier et gérer les actualités
- Ajouter/modifier les joueurs et le staff
- Créer et gérer les équipes
- Saisir les matchs et résultats
- Gérer les photos et médias
- Administrer les partenaires
- Gérer les utilisateurs et leurs droits

### Besoins techniques

- Site responsive (mobile, tablette, desktop)
- Navigation intuitive
- Temps de chargement rapide
- Formulaires sécurisés
- Gestion des images optimisée
- Back-office ergonomique
- Système de rôles (admin, éditeur, etc.)

## Contraintes

**Contraintes techniques :**
- Hébergement classique PHP/MySQL (pas de serveur Node.js)
- Compatibilité avec différents navigateurs
- Pas de budget pour des outils payants

**Contraintes de temps :**
- Développement sur environ 2 mois en parallèle de la formation
- Mise en ligne souhaitée avant la fin de la saison

## Choix Techniques

### Langages et Technologies

| Technologie | Justification |
|-------------|---------------|
| **PHP 8+** | Langage côté serveur que je maîtrise, large documentation, hébergement facile |
| **MySQL** | Base de données relationnelle gratuite, parfaite pour ce type de projet |
| **HTML5/CSS3** | Standards web, bonne compatibilité navigateurs |
| **JavaScript** | Pour l'interactivité (menu, formulaires, animations) |
| **PDO** | Abstraction base de données, requêtes préparées pour la sécurité |

### Architecture

J'ai organisé le projet en séparant les responsabilités :

```
ES_Moulon/
├── public/
│   ├── index.php          (point d'entrée, routage simple)
│   └── assets/            (CSS, images, JS)
├── pages/                 (pages du front-office)
├── includes/              (config, fonctions partagées)
├── BO/
│   ├── admin.php          (point d'entrée back-office)
│   └── _backoffice/       (sections admin, authentification)
```

**Pourquoi cette structure ?**
- Séparation claire front/back
- Fichier `config.php` centralisé pour la BDD et les constantes
- Réutilisation du code (header, footer, navbar)
- Sécurité : le back-office vérifie toujours la session

### Fonctionnalités Principales

**Front-office :**
- Page d'accueil dynamique avec dernier résultat et prochain match
- Liste des actualités avec pagination
- Pages de présentation (histoire, valeurs, organigramme)
- Section équipes avec effectifs et staff
- Calendrier et classement
- Formulaire de contact avec envoi d'email

**Back-office :**
- Dashboard avec statistiques (nombre de joueurs, actualités, matchs)
- CRUD complet pour : actualités, joueurs, staff, équipes, matchs, partenaires
- Gestion des médias (upload, classification)
- Gestion des utilisateurs avec système de rôles
- Sécurité : accès restreint selon les permissions

## Difficultés Rencontrées

### 1. Gestion des logos d'équipes
Au début, j'avais prévu de stocker tous les logos comme de simples images. Mais j'ai réalisé qu'une équipe adverse pouvait ne pas avoir de logo. J'ai donc dû ajouter une logique pour afficher un emoji par défaut si pas de logo.

### 2. Routage des pages
Je voulais des URLs propres (type `/Regional1/calendrier` au lieu de `?page=calendrier`). J'ai créé un système de routage simple dans `index.php` avec un tableau qui mappe les URLs aux fichiers PHP correspondants.

### 3. Sécurité du back-office
Il fallait absolument protéger l'accès. J'ai mis en place :
- Vérification de session sur chaque page admin
- Système de rôles avec permissions
- Requêtes préparées partout
- Hashage sécurisé des mots de passe

### 4. Upload et gestion des images
L'upload de fichiers en PHP n'est pas si simple. J'ai dû gérer :
- Vérification du type de fichier (images uniquement)
- Limite de taille
- Renommage des fichiers pour éviter les conflits
- Stockage du chemin en base de données

## Planning de Réalisation

**Semaine 1-2 : Analyse et maquettage**
- Définition des besoins
- Création des maquettes (wireframes basiques)
- Modélisation de la base de données (MCD/MLD)

**Semaine 3-4 : Mise en place technique**
- Création de la base de données
- Structure des dossiers
- Système de configuration et routing
- Header/Footer/Navbar

**Semaine 5-7 : Développement front-office**
- Page d'accueil avec cards dynamiques
- Pages de présentation
- Section actualités
- Section équipes et calendrier
- Formulaire de contact

**Semaine 8-10 : Développement back-office**
- Système d'authentification
- Dashboard
- CRUD actualités, joueurs, staff
- Gestion des matchs
- Upload de médias
- Gestion des utilisateurs

**Semaine 11 : Tests et corrections**
- Tests de toutes les fonctionnalités
- Corrections de bugs
- Optimisation
- Documentation

## Résultats

Le site est fonctionnel et répond aux besoins exprimés. Le club dispose maintenant d'un outil moderne pour communiquer et gérer son contenu facilement.

**Points positifs :**
- ✅ Site responsive et moderne
- ✅ Back-office complet et sécurisé
- ✅ Code organisé et commenté
- ✅ Gestion des rôles fonctionnelle

**Améliorations possibles :**
- Ajouter un système de cache pour les performances
- Créer une API REST pour une future application mobile
- Améliorer l'éditeur d'actualités (ajouter un WYSIWYG)
- Mettre en place des statistiques de visite plus poussées
