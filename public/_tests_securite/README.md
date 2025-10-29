# 🔒 Tests de Sécurité - ES Moulon

## 🎯 Accès rapide pour l'examen RNCP DWWM (25/11/2025)

### 📍 URL à retenir (la plus simple) :
```
localhost/es_moulon/public/_tests_securite
```

### 📋 Liste des tests disponibles

#### 1. **Test CSRF (Protection formulaires)**
- **URL complète** : `http://localhost/es_moulon/public/_tests_securite/test_csrf_simple.php`
- **URL courte** : `localhost/es_moulon/public/_tests_securite` → Cliquer sur le bouton
- **Démontre** : Protection contre les attaques Cross-Site Request Forgery
- **Scénarios** :
  - ✅ Formulaire normal avec token → Message vert "Merci !"
  - ❌ Attaque sans token → Message rouge "Token invalide !"

#### 2. **Test RGPD (Consentement cookies)** 🆕
- **URL complète** : `http://localhost/es_moulon/public/_tests_securite/test_rgpd_cookies.php`
- **URL courte** : `localhost/es_moulon/public/_tests_securite` → Cliquer sur "Test RGPD"
- **Démontre** : Conformité RGPD du système de tracking
- **Scénarios** :
  - ✅ Accepter les cookies → Tracking activé (visites enregistrées)
  - ❌ Refuser les cookies → Tracking désactivé (aucune donnée collectée)
  - 🔄 Supprimer les cookies → Réinitialiser le test

---

## 🎤 Phrases à dire au jury

### Question : "Comment avez-vous testé votre sécurité ?"

**Réponse** :
> "J'ai créé une suite de tests de sécurité accessibles via `localhost/es_moulon/public/_tests_securite`. 
> Je peux vous montrer en direct 2 tests : le test CSRF qui simule une attaque réelle, et le test RGPD 
> qui vérifie que mon système de tracking respecte le consentement des utilisateurs.
> Regardez, quand je clique sur le bouton rouge, l'attaque CSRF est bloquée avec un message d'erreur."

### Question : "Qu'est-ce que le CSRF ?"

**Réponse** :
> "CSRF signifie Cross-Site Request Forgery. C'est une attaque où un pirate essaie de faire exécuter 
> des actions à l'insu de l'utilisateur. J'ai protégé tous mes formulaires (15 au total) avec des 
> tokens CSRF uniques par session. Sans le bon token, la requête est rejetée."

### Question : "Comment avez-vous géré la RGPD ?"

**Réponse** :
> "J'ai mis en place un système de consentement des cookies conforme à la RGPD. Les utilisateurs voient 
> une bannière au premier accès avec 3 choix : accepter, refuser ou personnaliser. Si l'utilisateur refuse, 
> mon système de tracking (`tracker.php`) ne collecte AUCUNE donnée. Je peux vous montrer le test en direct : 
> quand je refuse les cookies, la table `visites` dans la BDD ne reçoit aucune nouvelle entrée."

---

## 📁 Organisation des fichiers

```
/public/_tests_securite/
├── index.html              ← Page d'accueil (liste des tests)
├── test_csrf_simple.php    ← Test CSRF interactif
├── test_rgpd_cookies.php   ← Test RGPD/Cookies (NOUVEAU)
└── README.md              ← Ce fichier (mémo)
```

---

## ✅ Checklist avant l'examen

- [ ] Vérifier que Laragon/XAMPP est démarré
- [ ] Tester l'URL : `localhost/es_moulon/public/_tests_securite`
- [ ] Vérifier que les 2 boutons du test CSRF fonctionnent :
  - Bouton bleu → Message vert ✅
  - Bouton rouge → Message rouge ❌
- [ ] Vérifier le test RGPD :
  - Supprimer les cookies → Bannière apparaît sur le site
  - Refuser → Aucune nouvelle visite en BDD
  - Accepter → Les visites sont enregistrées
- [ ] Préparer les phrases d'explication (voir ci-dessus)

---

## 💡 Astuces pour le jour J

1. **Ouvrir la page avant le jury** pour vérifier que tout fonctionne
2. **Garder l'URL en favoris** dans votre navigateur
3. **Imprimer ce README** et l'avoir dans votre dossier

---

## 🚀 Score attendu

Avec cette démonstration de test :
- **Sécurité** : 30/30 ✅
- **Professionnalisme** : +2 points bonus (démarche de test)
- **Score global** : 95/100 minimum 🌟

---

**Créé le** : 28 octobre 2025  
**Examen prévu** : 25 novembre 2025  
**Candidat** : Samir ARB
