# 📋 **CHECKLIST DE LIVRAISON — JOUR DE L'EXAMEN**

> **Date de l'examen :** 25/11/2025
> **Objectif :** Vérifier que le site ES Moulon est 100% prêt pour la démonstration au jury

---

## ✅ **1. INFORMATIONS LÉGALES À COMPLÉTER**

### 📄 **Fichier : `pages/mentions_NEW.php`**

- [X]  Remplacer `[Numéro de téléphone du club]` par le vrai numéro (ou supprimer la ligne)
- [X]  Remplacer `[Nom du président du club]` par le nom réel du président
- [X]  Remplacer `[À compléter si disponible]` (SIRET/RNA) → Options :
  - Mettre le vrai numéro si disponible
  - OU écrire : "En cours d'enregistrement"
  - OU supprimer complètement cette ligne

### 🔒 **Fichier : `pages/confidentialite_NEW.php`**

- [X]  Les mêmes infos seront automatiquement cohérentes (fichiers liés)

### 🌐 **Hébergement (Les 2 fichiers ci-dessus)**

- [ ]  Remplacer `[Nom de l'hébergeur]` → Exemples : **OVH**, **O2Switch**, **Hostinger**, **Ionos**
- [ ]  Remplacer `[Adresse complète de l'hébergeur]` → Chercher sur le site officiel de l'hébergeur
- [ ]  Remplacer `[Numéro de l'hébergeur]` → Numéro du support technique
- [ ]  Remplacer `[Email de l'hébergeur]` → Email de contact (ex: `support@ovh.com`)
- [ ]  Remplacer `[URL de l'hébergeur]` → Ex: `https://www.ovh.com`

### 🗂️ **Activation des nouveaux fichiers**

Une fois les placeholders remplis :

```bash
# Sauvegarder les anciennes versions
mv pages/mentions.php pages/mentions_OLD.php
mv pages/confidentialite.php pages/confidentialite_OLD.php

# Activer les nouvelles versions
mv pages/mentions_NEW.php pages/mentions.php
mv pages/confidentialite_NEW.php pages/confidentialite.php
```

---

## 🔐 **2. SÉCURITÉ — VÉRIFICATIONS FINALES**

### ⚙️ **Fichier : `includes/config.php`**

- [ ]  Vérifier `define('DEBUG_MODE', false);` ✅ **DOIT ÊTRE FALSE EN PRODUCTION**
- [ ]  Vérifier `ini_set('display_errors', 0);` ✅ **DOIT ÊTRE 0**
- [ ]  Vérifier les identifiants BDD :
  ```php
  define('DB_HOST', 'localhost'); // OK pour Laragon/XAMPP
  define('DB_NAME', 'es_moulon');
  define('DB_USER', 'root'); // À changer si hébergement distant
  define('DB_PASS', ''); // À changer si hébergement distant
  ```

### 🛡️ **Test de sécurité (5 minutes avant le jury)**

- [ ]  Aller sur `http://localhost/public/_tests_securite/index.html`
- [ ]  Vérifier le score : **95/100** ✅
- [ ]  Tester CSRF : Vérifier que le formulaire refuse les soumissions sans token
- [ ]  Tester RGPD : Vérifier que la bannière cookies apparaît et fonctionne

### 🍪 **Bannière cookies**

- [ ]  Ouvrir le site en navigation privée
- [ ]  Vérifier que la bannière apparaît après 1 seconde
- [ ]  Tester "Accepter" → Le cookie `cookie_consent=accepted` doit être créé
- [ ]  Tester "Refuser" → Le cookie `cookie_consent=refused` doit être créé
- [ ]  Vérifier le lien "🍪 Gérer les cookies" en bas de page → Doit réafficher la bannière

---

## 📊 **3. BASE DE DONNÉES**

### 💾 **Export de sauvegarde**

```bash
# Exporter la BDD AVANT la démonstration
mysqldump -u root es_moulon > es_moulon_backup_25_11_2025.sql
```

- [ ]  Créer un export `.sql` de la base de données
- [ ]  Le placer dans `Z_MA_DOCUMENTATION/`
- [ ]  Tester la restauration (optionnel mais recommandé) :
  ```bash
  mysql -u root -e "CREATE DATABASE es_moulon_test;"
  mysql -u root es_moulon_test < es_moulon_backup_25_11_2025.sql
  ```

### 📋 **Vérifications table `visites`**

- [ ]  Aller sur `http://localhost/public/debug_cookies.php`
- [ ]  Vérifier qu'il y a des visites enregistrées (au moins 10-20 pour la démo)
- [ ]  Si besoin, naviguer sur plusieurs pages du site pour générer des données

---

## 🖼️ **4. MÉDIAS ET CONTENU**

### 📸 **Images optimisées**

- [ ]  Vérifier que les images importantes sont en **WebP** (meilleure performance)
- [ ]  Vérifier qu'il n'y a pas d'images cassées (404) sur les pages principales
- [ ]  Pages à vérifier :
  - Accueil (`pages/accueil.php`)
  - Actualités (`pages/actualites.php`)
  - Calendrier (`pages/Regional1/calendrier.php`)
  - Effectif (`pages/Regional1/effectif_et_staff.php`)

### 📝 **Contenu rédactionnel**

- [ ]  Vérifier qu'il y a au moins **3-5 actualités** dans la BDD
- [ ]  Vérifier qu'il y a des **matchs** dans le calendrier (table `matches`)
- [ ]  Vérifier qu'il y a des **joueurs** et **staff** (tables `joueurs`, `staff`)

---

## 🎨 **5. FRONT-OFFICE — TEST COMPLET**

### 🏠 **Navigation principale**

- [ ]  **Accueil** → Affiche bien les dernières actus + calendrier + stats
- [ ]  **Actualités** → Liste des actus + détail d'un article
- [ ]  **Le Club** → Histoire, infos pratiques, organigramme, arbitres, bénévoles
- [ ]  **Nos Équipes** → École de foot, pré-formation, formation, seniors
- [ ]  **Régional 1** → Calendrier, classement, effectif/staff
- [ ]  **Rejoignez-nous** → Formulaires (arbitre, bénévole, partenaire, contact)
- [ ]  **Partenaires** → Liste des partenaires

### 🔗 **Footer**

- [ ]  Vérifier les 3 liens légaux en bas :
  - "Tous droits réservés" → `pages/droits.php` (✅ déjà OK)
  - "Mentions légales" → `pages/mentions.php` (à activer après remplissage)
  - "Politique de confidentialité" → `pages/confidentialite.php` (à activer après remplissage)

---

## 🔧 **6. BACK-OFFICE — TEST COMPLET**

### 🔑 **Connexion admin**

- [ ]  Aller sur `http://localhost/BO/admin.php`
- [ ]  Se connecter avec les identifiants admin
- [ ]  Vérifier que le dashboard affiche :
  - Total visites (doit correspondre à la table `visites`)
  - Total actualités
  - Total joueurs
  - Total matchs

### 📊 **Test du bouton "Rafraîchir les statistiques"**

- [ ]  Cliquer sur le bouton "🔄 Rafraîchir"
- [ ]  Vérifier qu'un message vert apparaît : "✅ Statistiques mises à jour !"
- [ ]  Vérifier que les chiffres sont à jour

### ✍️ **Test CRUD (Créer, Lire, Modifier, Supprimer)**

- [ ]  **Actualités** → Ajouter, modifier, supprimer une actu
- [ ]  **Joueurs** → Ajouter, modifier, supprimer un joueur
- [ ]  **Matchs** → Ajouter un match via `ajouter_match.php`
- [ ]  Vérifier que les **tokens CSRF** sont présents (regarder dans le code source : `<input type="hidden" name="csrf_token">`)

---

## 🚀 **7. PERFORMANCE ET SEO (Optionnel mais valorisant)**

### ⚡ **Test de vitesse**

- [ ]  Ouvrir le site avec les **DevTools** (F12)
- [ ]  Aller dans l'onglet **Network** → Recharger la page
- [ ]  Vérifier que le temps de chargement est **< 2 secondes**
- [ ]  Si trop lent :
  - Activer `gzip` dans `.htaccess`
  - Optimiser les images (WebP)
  - Minifier CSS/JS (optionnel)

### 🔍 **SEO Basique**

- [ ]  Vérifier que chaque page a un `<title>` unique (variable `$page_title`)
- [ ]  Vérifier que les images importantes ont un attribut `alt`
- [ ]  Vérifier qu'il y a des balises `<meta>` (description, keywords)

---

## 📖 **8. DOCUMENTATION — VÉRIFICATION FINALE**

### 📂 **Dossier `Z_MA_DOCUMENTATION/`**

- [ ]  `README.md` → Introduction du projet
- [ ]  `AUDIT_CONFORMITE_RNCP_DWWM.md` → Conformité au référentiel
- [ ]  `RESUME_EXECUTIF_AUDIT.md` → Résumé pour le jury
- [ ]  `ACCES_TESTS_SECURITE.md` → Guide d'accès aux tests
- [ ]  `GUIDE_RAPIDE_WEBP.md` → Optimisation images
- [ ]  **Nouveaux fichiers :**
  - [ ]  `CHECKLIST_LIVRAISON.md` (ce fichier)
  - [ ]  `3_Realisation_Technique/securite.md` → Sections CSRF + RGPD

### 📝 **Sections clés à maîtriser pour le jury**

- [ ]  **CSRF** → Expliquer comment les tokens protègent les formulaires (Section 10 de `securite.md`)
- [ ]  **RGPD** → Expliquer la bannière cookies et le tracking conditionnel (Section 11)
- [ ]  **Limitations** → Parler de la conservation illimitée des données (Section 12) et proposer des solutions

---

## 🎯 **9. ARGUMENTAIRE POUR LE JURY**

### 💡 **Points forts à mettre en avant**

1. **Sécurité 95/100** → Protection CSRF sur 15 formulaires + honeypot anti-bot
2. **Conformité RGPD** → Bannière cookies + tracking conditionnel + politique de confidentialité
3. **Architecture propre** → PDO 100%, sessions sécurisées, séparation back/front
4. **Optimisation** → Images WebP, cache dashboard, requêtes optimisées
5. **Documentation complète** → 8 fichiers MD + tests de sécurité accessibles

### ⚠️ **Limitations assumées (Montrer votre esprit critique)**

1. **Conservation des données illimitée** → "En production, je mettrais en place un CRON job pour supprimer les visites > 12 mois"
2. **Pas de CDN** → "Pour un gros trafic, j'utiliserais Cloudflare"
3. **Pas de tests unitaires** → "Si j'avais plus de temps, j'ajouterais PHPUnit"

### 📢 **Phrase d'accroche pour le jury**

> "Mon projet ES Moulon est un site vitrine pour un club de football amateur. J'ai mis l'accent sur **3 piliers** :
> 1️⃣ La **sécurité** (CSRF, protection des formulaires, sessions)
> 2️⃣ La **conformité RGPD** (bannière cookies, consentement, droits utilisateurs)
> 3️⃣ L'**expérience utilisateur** (navigation fluide, back-office ergonomique, optimisation WebP)
>
> J'ai également documenté les **limitations identifiées** et proposé des solutions d'amélioration pour montrer ma capacité à **prendre du recul sur mon travail**."

---

## ✅ **10. CHECKLIST 1 HEURE AVANT LE JURY**

- [ ]  ☕ **Prendre un café** (ou un thé, on juge pas)
- [ ]  🔄 **Redémarrer Apache + MySQL** (pour éviter les bugs de cache)
- [ ]  🧹 **Vider le cache navigateur** (Ctrl + Shift + Del)
- [ ]  🧪 **Tester 3 parcours utilisateurs :**
  1. Visiteur → Accepte cookies → Navigue sur 5 pages → Vérifie que les visites sont enregistrées
  2. Visiteur → Refuse cookies → Navigue → Vérifie qu'AUCUNE visite n'est enregistrée
  3. Admin → Se connecte → Ajoute une actu → Vérifie qu'elle s'affiche bien sur le front
- [ ]  📊 **Ouvrir les 4 pages de tests** (pour les montrer au jury) :
  - `http://localhost/public/_tests_securite/index.html`
  - `http://localhost/public/_tests_securite/test_csrf_simple.php`
  - `http://localhost/public/_tests_securite/test_rgpd_cookies.php`
  - `http://localhost/public/debug_cookies.php`
- [ ]  🎤 **Répéter l'argumentaire** (5 minutes max, clair et concis)
- [ ]  😎 **Respirer un coup** → **Vous allez cartonner ! 💪**

---

## 📞 **CONTACT EN CAS DE PROBLÈME**

Si vous avez un doute ou un bug de dernière minute :

1. Relire la section correspondante dans `Z_MA_DOCUMENTATION/3_Realisation_Technique/securite.md`
2. Vérifier les logs PHP : `C:\laragon\bin\apache\httpd-2.4.59-win64-VS17\logs\error.log`
3. Tester en navigation privée (pour éviter les problèmes de cache)

---

## 🎓 **BON COURAGE POUR L'EXAMEN !**

> **"Le succès, c'est d'aller d'échec en échec sans perdre son enthousiasme."**
> — Winston Churchill

✅ **Vous avez bossé dur, votre projet est solide, VOUS ÊTES PRÊT ! 🚀**
