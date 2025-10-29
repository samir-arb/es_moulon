# 🔒 Analyse de Sécurité - Formulaires "Rejoignez-nous"

## ✅ **VERDICT : Vos formulaires sont BIEN SÉCURISÉS !**

Vous avez implémenté **plusieurs couches de sécurité** qui protègent efficacement contre les principales menaces web. Voici l'analyse détaillée :

---

## 🛡️ **Les 5 Protections Mises en Place**

### **1. Protection CSRF (Cross-Site Request Forgery)** ⭐⭐⭐

#### 🎯 Qu'est-ce que c'est ?
Le CSRF est une attaque où un site malveillant force un utilisateur à exécuter une action non voulue sur VOTRE site (où il est connecté).

**Exemple d'attaque sans protection :**
```html
<!-- Site malveillant pirate.com -->
<form action="https://esmoulon.fr/traitement_contact.php" method="POST">
    <input name="email" value="spam@pirate.com">
    <input name="message" value="SPAM!">
</form>
<script>document.forms[0].submit();</script>
```
→ Si la victime visite pirate.com, le formulaire est soumis automatiquement à votre nom !

#### ✅ Votre solution : Token CSRF

**Dans le formulaire :**
```php
<?php
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
```

**Vérification dans `traitement_contact.php` :**
```php
if (!isset($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
    $_SESSION['flash']['error'] = "Session invalide, veuillez réessayer.";
    header("Location: /es_moulon/public/accueil.php");
    exit;
}
```

#### 📝 Comment ça marche ?

1. **Génération du token**
   - `bin2hex(random_bytes(32))` génère une chaîne aléatoire de 64 caractères
   - Exemple : `7a8f3b2c9e1d4f6a8b3c5e7d9f1a3b5c7e9d1f3a5b7c9e1d3f5a7b9c1e3d5f7a9`
   - Ce token est stocké en session (côté serveur)

2. **Envoi du token**
   - Le token est ajouté au formulaire dans un champ caché
   - Quand l'utilisateur soumet le formulaire, le token est envoyé avec les données

3. **Vérification**
   - Le serveur compare le token reçu avec celui en session
   - S'ils correspondent → OK, le formulaire vient bien de votre site
   - S'ils ne correspondent pas → BLOQUÉ, c'est une tentative de CSRF

#### 🚫 Pourquoi l'attaquant ne peut pas bypasser ?
- Le token est **unique par session**
- L'attaquant ne peut pas lire le token (il est sur votre serveur)
- Impossible de deviner (64 caractères aléatoires = 16^64 possibilités)

---

### **2. Protection Anti-Bot (Honeypot)** ⭐⭐⭐

#### 🎯 Qu'est-ce que c'est ?
Les robots automatiques (bots) parcourent le web pour spammer les formulaires. Le honeypot (pot de miel) est un piège pour les attraper.

#### ✅ Votre solution : Champ invisible

**Dans le formulaire :**
```php
<input type="text" name="website" style="display:none">
```

**Vérification dans `traitement_contact.php` :**
```php
if (!empty($_POST['website'])) {
    header("Location: /es_moulon/public/accueil.php");
    exit;
}
```

#### 📝 Comment ça marche ?

1. **Le piège**
   ```html
   <input type="text" name="website" style="display:none">
   ```
   - Champ HTML normal MAIS invisible (`display:none`)
   - Les **humains** ne le voient pas → ne le remplissent pas
   - Les **bots** remplissent automatiquement TOUS les champs → tombent dans le piège

2. **La vérification**
   ```php
   if (!empty($_POST['website'])) {
       // Si le champ est rempli = c'est un bot !
       header("Location: /es_moulon/public/accueil.php");
       exit;
   }
   ```

#### 💡 Avantages
- ✅ **Invisible pour l'utilisateur** (pas de captcha agaçant)
- ✅ **Simple à implémenter**
- ✅ **Efficace contre 90% des bots basiques**
- ✅ **Pas de service externe** (pas besoin de reCAPTCHA)

#### ⚠️ Limites
- Ne bloque pas les bots sophistiqués qui analysent le CSS
- Ne bloque pas les humains malveillants (mais ça + CSRF = très efficace)

---

### **3. Protection Injection SQL** ⭐⭐⭐

#### 🎯 Qu'est-ce que c'est ?
L'injection SQL permet à un attaquant d'exécuter du code SQL malveillant dans votre base de données.

**Exemple d'attaque sans protection :**
```php
// ❌ CODE DANGEREUX
$email = $_POST['email'];
$query = "INSERT INTO contacts (email) VALUES ('$email')";
$pdo->query($query);
```

Si l'attaquant saisit : `'); DROP TABLE contacts; --`
```sql
-- Requête exécutée :
INSERT INTO contacts (email) VALUES (''); DROP TABLE contacts; --')
```
→ Votre table `contacts` est supprimée ! 💣

#### ✅ Votre solution : Requêtes Préparées PDO

**Dans `traitement_contact.php` :**
```php
$stmt = $pdo->prepare("
    INSERT INTO contacts (first_name, name, email, phone, message, contact_type, sent_at, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'en attente')
");
$stmt->execute([$first_name, $last_name, $email, $phone, $message, $contact_type, $sent_at]);
```

#### 📝 Comment ça marche ?

1. **Séparation code/données**
   ```php
   // La requête avec des placeholders (?)
   $stmt = $pdo->prepare("INSERT INTO contacts (email) VALUES (?)");
   
   // Les données sont envoyées séparément
   $stmt->execute([$email]);
   ```

2. **PDO échappe automatiquement**
   - PDO traite tout ce qui est dans `execute()` comme des **données**, jamais comme du **code SQL**
   - Si l'utilisateur saisit : `'); DROP TABLE contacts; --`
   - PDO l'insère littéralement comme texte : `'); DROP TABLE contacts; --`
   - Aucune commande SQL n'est exécutée !

#### 🔍 Comparaison

**Sans protection (DANGEREUX) :**
```php
$query = "SELECT * FROM users WHERE email = '$email'";
// Si $email = "' OR '1'='1"
// → SELECT * FROM users WHERE email = '' OR '1'='1'
// → Retourne TOUS les utilisateurs !
```

**Avec requêtes préparées (SÉCURISÉ) :**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
// Si $email = "' OR '1'='1"
// → Cherche littéralement un email qui vaut "' OR '1'='1"
// → Aucun utilisateur trouvé (sûr !)
```

---

### **4. Protection XSS (Cross-Site Scripting)** ⭐⭐

#### 🎯 Qu'est-ce que c'est ?
Le XSS permet à un attaquant d'injecter du code JavaScript malveillant qui sera exécuté dans le navigateur des autres utilisateurs.

**Exemple d'attaque :**
Un utilisateur saisit dans le message :
```html
<script>alert('Piraté!');</script>
```

Si vous affichez ce message sans échappement, le JavaScript s'exécute !

#### ✅ Votre solution : htmlspecialchars()

**Dans `traitement_contact.php` :**
```php
$contact_type = htmlspecialchars($_POST['type_form']);
$first_name = htmlspecialchars($_POST['prenom'] ?? $_POST['entreprise'] ?? '');
$last_name = htmlspecialchars($_POST['nom'] ?? $_POST['contact_nom'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$phone = htmlspecialchars($_POST['telephone'] ?? '');
$message = htmlspecialchars($_POST['motivation'] ?? $_POST['message'] ?? '');
```

#### 📝 Comment ça marche ?

**Transformation des caractères dangereux :**
```php
$input = "<script>alert('XSS')</script>";
$safe = htmlspecialchars($input);
// Résultat : "&lt;script&gt;alert('XSS')&lt;/script&gt;"
```

**Table de conversion :**
| Caractère | Devient | Pourquoi |
|-----------|---------|----------|
| `<` | `&lt;` | Empêche les balises HTML |
| `>` | `&gt;` | Empêche les balises HTML |
| `"` | `&quot;` | Empêche les injections dans attributs |
| `'` | `&#039;` | Empêche les injections dans attributs |
| `&` | `&amp;` | Empêche les entités HTML |

**Affichage sécurisé :**
```html
<!-- Sans échappement (DANGEREUX) -->
<p><?= $message ?></p>
<!-- Si $message = "<script>alert('XSS')</script>" -->
<!-- → Le script s'exécute ! -->

<!-- Avec htmlspecialchars (SÉCURISÉ) -->
<p><?= htmlspecialchars($message) ?></p>
<!-- Affiche : &lt;script&gt;alert('XSS')&lt;/script&gt; -->
<!-- → Affiché comme du texte, pas exécuté -->
```

---

### **5. Validation des Données** ⭐⭐

#### ✅ Double validation : Client + Serveur

**Côté Client (HTML5) :**
```html
<input type="text" name="prenom" placeholder="Prénom *" required>
<input type="email" name="email" placeholder="Email *" required>
<input type="tel" name="telephone" placeholder="Téléphone *" required>
```

**Attributs de sécurité :**
- `required` : Le champ ne peut pas être vide
- `type="email"` : Vérifie le format email (contient @ et .)
- `type="tel"` : Format téléphone

**Côté Serveur (PHP) :**
```php
if (empty($email) || empty($first_name)) {
    $_SESSION['flash']['error'] = "Merci de remplir tous les champs obligatoires.";
    header("Location: ...");
    exit;
}
```

#### 📝 Pourquoi les deux ?

1. **Validation HTML (UX)** :
   - Retour immédiat à l'utilisateur
   - Évite un aller-retour serveur inutile
   - **MAIS** : Peut être désactivé dans les DevTools !

2. **Validation PHP (Sécurité)** :
   - Ne peut pas être contournée
   - S'exécute côté serveur
   - **C'est la vraie sécurité !**

**Exemple de bypass HTML :**
```javascript
// Un attaquant peut faire ça dans la console :
document.querySelector('input[name="email"]').removeAttribute('required');
document.forms[0].submit();
```
→ Sans validation PHP, ça passe !

---

## 🎯 **Protections Additionnelles**

### **6. Gestion Sécurisée des Sessions**

```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

**Ce que ça fait :**
- Vérifie si une session est déjà active
- Évite l'erreur "session already started"
- Sécurise le contexte d'exécution

### **7. Pattern PRG (Post-Redirect-Get)**

```php
$_SESSION['flash']['success'] = "✅ Merci ! Votre message a bien été envoyé.";
header("Location: /es_moulon/public/Rejoignez_nous/nous_contactez#confirmation");
exit;
```

**Ce que ça fait :**
- Après soumission POST → Redirection GET
- **Empêche la re-soumission** si l'utilisateur appuie sur F5
- Évite les doublons en base de données

### **8. Messages Flash Sécurisés**

```php
<?php if (isset($_SESSION['flash']['success'])): ?>
    <div id="confirmation">
        <?= $_SESSION['flash']['success'] ?>
    </div>
    <?php unset($_SESSION['flash']['success']); ?>
<?php endif; ?>
```

**Ce que ça fait :**
- Message affiché une seule fois
- Supprimé automatiquement après affichage
- Pas de persistence en session

---

## 📊 **Tableau Récapitulatif**

| 🎯 Menace | ✅ Protection | 📈 Niveau | 📝 Implémentation |
|-----------|---------------|-----------|-------------------|
| **CSRF** | Token unique | ⭐⭐⭐ Excellent | `bin2hex(random_bytes(32))` |
| **Bots/Spam** | Honeypot | ⭐⭐⭐ Excellent | Champ invisible `website` |
| **Injection SQL** | Requêtes préparées | ⭐⭐⭐ Excellent | PDO `prepare()` + `execute()` |
| **XSS** | htmlspecialchars() | ⭐⭐ Bon | Sur toutes les données affichées |
| **Données invalides** | Validation double | ⭐⭐ Bon | HTML5 `required` + PHP |
| **Re-soumission** | Pattern PRG | ⭐⭐ Bon | Redirection après POST |
| **Rate limiting** | ❌ Absent | ⚠️ À ajouter | Limiter X envois/heure |
| **Brute force** | ❌ Absent | ⚠️ À ajouter | Blocage après X tentatives |

---

## 💡 **Améliorations Possibles (Bonus)**

### 1. Validation Email Stricte
```php
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    $_SESSION['flash']['error'] = "Email invalide.";
    exit;
}
```

### 2. Limite de Longueur
```php
if (strlen($message) > 5000) {
    $_SESSION['flash']['error'] = "Message trop long (max 5000 caractères).";
    exit;
}
```

### 3. Rate Limiting (Anti-Spam Avancé)
```php
// Limiter à 3 messages par heure par IP
$ip = $_SERVER['REMOTE_ADDR'];
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM contacts 
    WHERE ip_address = ? AND sent_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
");
$stmt->execute([$ip]);
if ($stmt->fetchColumn() >= 3) {
    $_SESSION['flash']['error'] = "Trop de tentatives. Réessayez dans 1 heure.";
    exit;
}
```

### 4. Logs de Sécurité
```php
if (!empty($_POST['website'])) {
    error_log("🤖 Bot détecté : IP=" . $_SERVER['REMOTE_ADDR'] . " à " . date('Y-m-d H:i:s'));
    exit;
}
```

### 5. Sanitization Avancée
```php
// Pour les noms (seulement lettres, espaces, tirets)
$first_name = preg_replace('/[^a-zA-ZÀ-ÿ\s\-]/u', '', $_POST['prenom']);

// Pour les téléphones (seulement chiffres et espaces)
$phone = preg_replace('/[^0-9\s\+\-\(\)]/u', '', $_POST['telephone']);
```

---

## 🎓 **Ce que vous pouvez dire à l'examen DWWM**

### **Question du jury : "Comment avez-vous sécurisé vos formulaires ?"**

**Votre réponse :**

> "J'ai implémenté **5 couches de sécurité** principales :
> 
> **1. Protection CSRF** : J'ai créé un token unique par session avec `bin2hex(random_bytes(32))`. Ce token est vérifié côté serveur pour s'assurer que le formulaire vient bien de mon site et pas d'un site malveillant.
> 
> **2. Honeypot Anti-Bot** : J'ai ajouté un champ invisible que les bots remplissent automatiquement. Si ce champ est rempli, je bloque la soumission. C'est simple mais très efficace contre le spam automatique.
> 
> **3. Requêtes Préparées PDO** : Toutes mes requêtes SQL utilisent des requêtes préparées avec des placeholders. PDO sépare le code SQL des données, ce qui empêche totalement les injections SQL.
> 
> **4. Échappement HTML** : J'utilise `htmlspecialchars()` sur toutes les données utilisateur avant de les afficher. Ça convertit les caractères dangereux comme `<script>` en entités HTML inoffensives.
> 
> **5. Double Validation** : J'ai une validation côté client avec HTML5 (`required`, `type="email"`) pour l'expérience utilisateur, mais surtout une validation côté serveur en PHP car le HTML peut être contourné.
> 
> J'ai également mis en place le **pattern Post-Redirect-Get** pour éviter les re-soumissions accidentelles, et des **messages flash** qui s'affichent une seule fois."

### **Question : "Qu'est-ce que le CSRF et comment vous en protégez-vous ?"**

> "Le CSRF, c'est quand un site malveillant force un utilisateur à exécuter une action sur mon site. Par exemple, si l'utilisateur est connecté sur mon site et visite un site pirate, ce dernier pourrait soumettre un formulaire à ma place.
> 
> Pour m'en protéger, je génère un **token CSRF aléatoire** stocké en session. Ce token est ajouté dans un champ caché du formulaire. Quand le formulaire est soumis, je vérifie que le token reçu correspond à celui en session. Un attaquant ne peut pas deviner ce token car il fait 64 caractères aléatoires et change à chaque session."

### **Question : "Pourquoi les requêtes préparées sont plus sûres ?"**

> "Les requêtes préparées **séparent le code SQL des données**. Sans elles, si je concatène directement les données utilisateur dans la requête, un attaquant peut injecter du SQL malveillant.
> 
> Avec PDO `prepare()` et `execute()`, les données sont envoyées séparément et traitées comme des valeurs, jamais comme du code. Même si l'utilisateur saisit `'; DROP TABLE users; --`, PDO va littéralement chercher un email qui s'appelle comme ça, sans exécuter de commande."

---

## ✅ **Note Finale : 9/10**

### **Points Forts :**
- ✅ Protection CSRF implémentée (rare chez les débutants !)
- ✅ Honeypot simple et efficace
- ✅ Requêtes préparées PDO partout
- ✅ Échappement HTML systématique
- ✅ Pattern PRG pour éviter les doublons
- ✅ Code propre et bien organisé

### **Points à Améliorer (pour aller plus loin) :**
- ⚠️ Rate limiting (limiter le nombre d'envois)
- ⚠️ Validation email stricte avec `filter_var()`
- ⚠️ Logs de sécurité pour tracer les tentatives suspectes
- ⚠️ Limite de longueur sur les champs texte

**Conclusion :** Vos formulaires sont **très bien sécurisés** pour un niveau DWWM. Vous avez compris et appliqué les concepts essentiels de sécurité web. C'est un **excellent point** pour votre dossier professionnel ! 🎉

---

*Document créé pour le dossier RNCP DWWM - Octobre 2025*
