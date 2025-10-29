# Manuel Utilisateur - Back-Office ES Moulon

Ce guide explique comment utiliser l'interface d'administration du site ES Moulon. Il est destiné aux personnes qui vont gérer le contenu du site (actualités, joueurs, matchs, etc.).

## Accès au Back-Office

### Connexion

1. Accéder à l'URL : `http://votre-site.fr/admin.php`
2. Saisir votre email et mot de passe
3. Cliquer sur "Se connecter"

![Page de connexion](screenshots/login.png) *(capture à faire)*

**⚠️ Important :**

- Ne partagez jamais votre mot de passe
- Si vous oubliez votre mot de passe, cliquez sur "Mot de passe oublié ?"
- Pensez à vous déconnecter après utilisation (surtout sur un ordinateur partagé)

### Interface Principale

Une fois connecté, vous arrivez sur le **Dashboard** (tableau de bord) qui affiche :

- Les statistiques générales (nombre d'actualités, de joueurs, de matchs)
- Les dernières actualités publiées
- Les prochains matchs

Sur la gauche, vous avez le **menu latéral** avec toutes les sections disponibles selon vos droits.

## 📰 Gestion des Actualités

### Voir la Liste des Actualités

1. Cliquer sur **"Actualités"** dans le menu de gauche
2. Vous voyez toutes les actualités (publiées ou brouillons)
3. Colonnes affichées :
   - Titre
   - Auteur
   - Date de création
   - Statut (publié / brouillon)
   - Actions (modifier, supprimer)

### Ajouter une Nouvelle Actualité

1. Cliquer sur **"+ Nouvelle actualité"**
2. Remplir le formulaire :
   - **Titre** : Le titre de l'article (obligatoire)
   - **Résumé** : Court texte qui s'affiche dans la liste
   - **Contenu** : Le texte complet de l'article
   - **Image principale** : Photo illustrant l'article
   - **Publier** : Cocher pour rendre l'article visible, décocher pour garder en brouillon
3. Cliquer sur **"Ajouter"**

**💡 Conseils :**

- Choisissez un titre accrocheur et clair
- Le résumé doit donner envie de lire la suite (2-3 phrases max)
- L'image doit être au format paysage (idéalement 1200x600 pixels)
- Vous pouvez préparer un article en brouillon et le publier plus tard

### Modifier une Actualité

1. Dans la liste, cliquer sur l'icône **"Modifier"** (crayon)
2. Modifier les champs souhaités
3. Cliquer sur **"Enregistrer les modifications"**

### Supprimer une Actualité

1. Dans la liste, cliquer sur l'icône **"Supprimer"** (poubelle)
2. Confirmer la suppression

**⚠️ Attention : La suppression est définitive !**

## ⚽ Gestion des Joueurs

### Voir la Liste des Joueurs

1. Cliquer sur **"Joueurs"** dans le menu
2. Vous voyez tous les joueurs du club
3. Vous pouvez filtrer par équipe

### Ajouter un Joueur

1. Cliquer sur **"+ Nouveau joueur"**
2. Remplir les informations :
   - **Prénom** et **Nom** (obligatoires)
   - **Date de naissance**
   - **Position** : Gardien, Défenseur, Milieu, Attaquant
   - **Numéro de maillot**
   - **Équipe** : Sélectionner l'équipe du joueur
   - **Photo** : Photo du joueur (optionnel)
   - **Biographie** : Quelques infos sur le joueur (parcours, qualités...)
3. Cliquer sur **"Ajouter"**

**💡 Conseil :** Pour les photos, utilisez des images de bonne qualité en format portrait (300x400 pixels minimum).

### Modifier un Joueur

1. Cliquer sur **"Modifier"** à côté du joueur
2. Modifier les informations
3. Vous pouvez changer la photo en uploadant une nouvelle
4. Cliquer sur **"Enregistrer"**

### Supprimer un Joueur

1. Cliquer sur **"Supprimer"**
2. Confirmer

**Note :** Si un joueur quitte le club, vous pouvez le supprimer ou le déplacer dans une équipe "Anciens joueurs" si vous en avez créé une.

## 👥 Gestion du Staff

Le fonctionnement est identique à la gestion des joueurs.

### Ajouter un Membre du Staff

1. Cliquer sur **"Staff"** puis **"+ Nouveau membre"**
2. Remplir :
   - Prénom / Nom
   - **Rôle** : Entraîneur, Éducateur, Préparateur physique, etc.
   - Équipe associée
   - Photo
   - Biographie
   - Email et téléphone (optionnels)
3. Cliquer sur **"Ajouter"**

## 🏆 Gestion des Équipes

### Créer une Nouvelle Équipe

1. Cliquer sur **"Équipes"** puis **"+ Nouvelle équipe"**
2. Remplir :
   - **Nom** : Ex: "Régional 1 Seniors"
   - **Niveau** : R1, R2, U19, U17, etc.
   - **Catégorie** : Seniors, Jeunes, École de foot
   - **Équipe du club** : Cocher si c'est une équipe du club (laisser décoché pour les adversaires)
   - **Logo** : Logo de l'équipe (optionnel)
3. Cliquer sur **"Créer"**

**💡 Utilité :** Vous devez créer les équipes adverses pour pouvoir saisir les matchs.

## 📅 Gestion du Calendrier

### Ajouter un Match

1. Cliquer sur **"Calendrier"** puis **"+ Nouveau match"**
2. Remplir :
   - **Date et heure** du match
   - **Équipe domicile** : Sélectionner dans la liste
   - **Équipe extérieur** : Sélectionner dans la liste
   - **Type de match** : Championnat, Coupe, Amical
   - **Lieu** : Stade, adresse
   - **Scores** : Laisser vide si le match n'est pas encore joué
3. Cliquer sur **"Ajouter"**

**💡 Astuce :** Au moins une des deux équipes doit être une équipe du club.

### Saisir le Résultat d'un Match

1. Dans la liste des matchs, trouver le match joué
2. Cliquer sur **"Modifier"**
3. Renseigner les scores
4. Cliquer sur **"Enregistrer"**

Le résultat s'affichera automatiquement sur la page d'accueil (dans la section "Dernier résultat").

## 🖼️ Gestion des Médias

### Voir la Médiathèque

1. Cliquer sur **"Médias"**
2. Vous voyez toutes les photos/logos uploadés
3. Vous pouvez filtrer par type ou rechercher

### Uploader un Nouveau Fichier

1. Cliquer sur **"+ Uploader un fichier"**
2. Choisir le fichier sur votre ordinateur
3. Optionnel : Ajouter un titre et un texte alternatif (pour l'accessibilité)
4. Cliquer sur **"Uploader"**

**⚠️ Restrictions :**

- Formats autorisés : JPEG, PNG, GIF, WebP
- Taille maximale : 5 MB par fichier
- Noms de fichiers sans caractères spéciaux

**💡 Conseil :** Compressez vos images avant de les uploader (utilisez un outil comme TinyPNG ou Squoosh).

### Supprimer un Média

1. Cliquer sur **"Supprimer"** sous le fichier
2. Confirmer

**⚠️ Attention :** Si ce média est utilisé quelque part (actualité, joueur, etc.), l'image ne s'affichera plus.

## 🤝 Gestion des Partenaires

### Ajouter un Partenaire

1. Cliquer sur **"Partenaires"** puis **"+ Nouveau partenaire"**
2. Remplir :
   - **Nom** du partenaire
   - **Description** (quelques mots sur le partenaire)
   - **Site web** (URL complète avec https://)
   - **Logo** : Upload du logo
   - **Ordre d'affichage** : Numéro pour contrôler l'ordre (1, 2, 3...)
   - **Actif** : Cocher pour afficher sur le site
3. Cliquer sur **"Ajouter"**

**💡 Conseil :** Pour les logos, utilisez des fichiers PNG avec fond transparent si possible.

## 📧 Gestion des Messages de Contact

### Consulter les Messages

1. Cliquer sur **"Contacts"**
2. Vous voyez tous les messages reçus via le formulaire de contact
3. Colonnes :
   - Nom et email de l'expéditeur
   - Sujet
   - Date d'envoi
   - Statut (lu / non lu)

### Lire un Message

1. Cliquer sur le message
2. Le contenu complet s'affiche
3. Le message est automatiquement marqué comme "lu"

### Répondre à un Message

Le système n'envoie pas de réponse directement. Pour répondre :

1. Noter l'adresse email de l'expéditeur
2. Répondre via votre logiciel email habituel (Gmail, Outlook, etc.)

### Supprimer un Message

1. Cliquer sur **"Supprimer"**
2. Confirmer

## 👤 Gestion des Utilisateurs (Admins uniquement)

**⚠️ Cette section est réservée aux administrateurs (ROLE_ADMIN).**

### Voir les Utilisateurs

1. Cliquer sur **"Utilisateurs"**
2. Liste de tous les comptes du back-office

### Ajouter un Utilisateur

1. Cliquer sur **"+ Nouvel utilisateur"**
2. Remplir :
   - Email (servira d'identifiant)
   - Prénom / Nom
   - **Rôle** :
     - **Admin** : Tous les droits (⚠️ à attribuer avec précaution)
     - **Éditeur** : Peut gérer le contenu mais pas les utilisateurs
     - **Visiteur** : Accès en lecture seule
   - Mot de passe temporaire
3. Cliquer sur **"Créer"**

**💡 L'utilisateur recevra un email avec ses identifiants.**

### Modifier un Utilisateur

1. Cliquer sur **"Modifier"**
2. Vous pouvez changer le rôle, désactiver le compte, réinitialiser le mot de passe
3. Enregistrer

### Désactiver un Utilisateur

Plutôt que de supprimer, vous pouvez désactiver :

1. Cliquer sur **"Désactiver"**
2. L'utilisateur ne pourra plus se connecter mais son compte existe toujours

## ⚙️ Bonnes Pratiques

### Sécurité

✅ **À FAIRE :**

- Se déconnecter après chaque session
- Utiliser un mot de passe fort (lettres, chiffres, caractères spéciaux)
- Ne jamais partager ses identifiants
- Vérifier qu'on est bien sur le bon site avant de se connecter

❌ **À NE PAS FAIRE :**

- Noter son mot de passe sur un post-it
- Utiliser un mot de passe simple (nom, date de naissance)
- Rester connecté sur un ordinateur partagé

### Contenu

✅ **À FAIRE :**

- Relire les actualités avant de publier
- Utiliser des images de bonne qualité
- Vérifier les scores des matchs avant de les saisir
- Garder la même structure pour les actualités (titre accrocheur, résumé, contenu)

❌ **À NE PAS FAIRE :**

- Publier des informations non vérifiées
- Utiliser des images trouvées sur Internet sans droit
- Oublier de remplir les champs importants (résumé, etc.)

### Organisation

💡 **Conseils :**

- Publier régulièrement des actualités (au moins 1 par semaine)
- Mettre à jour les résultats le jour même des matchs
- Faire le tri dans les médias de temps en temps (supprimer les anciens)
- Préparer les articles à l'avance en mode brouillon

## ❓ Questions Fréquentes (FAQ)

### Comment changer mon mot de passe ?

1. Cliquer sur votre nom en haut à droite
2. "Mon compte" > "Changer mon mot de passe"
3. Saisir l'ancien puis le nouveau mot de passe
4. Enregistrer

### J'ai oublié mon mot de passe, que faire ?

1. Sur la page de connexion, cliquer sur "Mot de passe oublié ?"
2. Saisir votre email
3. Vous recevrez un lien pour réinitialiser

### Je ne peux pas uploader une image, pourquoi ?

Vérifiez :

- La taille du fichier (max 5 MB)
- Le format (JPEG, PNG, GIF, WebP uniquement)
- Votre connexion internet

### Une actualité n'apparaît pas sur le site

Vérifiez que :

- Elle est bien **publiée** (case cochée)
- Vous avez vidé le cache de votre navigateur (Ctrl+F5)

### Comment supprimer plusieurs éléments en même temps ?

Actuellement, il faut supprimer un par un. Une fonction de suppression en masse sera ajoutée prochainement.

## 📞 Besoin d'Aide ?

Si vous rencontrez un problème non résolu par ce manuel :

- **Email** : support@esmoulon.fr
- **Téléphone** : [Numéro à définir]

Indiquez dans votre message :

- Votre nom et rôle
- La section concernée (actualités, joueurs, etc.)
- Le message d'erreur si applicable
- Une capture d'écran si possible

---

*Manuel utilisateur - Dernière mise à jour : 28 octobre 2025*
