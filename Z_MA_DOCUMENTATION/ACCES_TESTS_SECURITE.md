# 🔗 Accès rapide aux Tests de Sécurité

## 🎯 URL à retenir pour l'examen (25/11/2025)

### **URL la plus simple** :
```
localhost/es_moulon/public/_tests_securite
```

ou

```
http://localhost/es_moulon/public/_tests_securite/
```

---

## 📌 Marche à suivre le jour J

1. **Démarrer Laragon** (ou XAMPP)
2. **Ouvrir votre navigateur**
3. **Taper dans la barre d'adresse** :
   ```
   localhost/es_moulon/public/_tests_securite
   ```
4. **Cliquer sur** : "▶️ Lancer le test CSRF"
5. **Démontrer au jury** :
   - Bouton bleu (avec token) → ✅ Ça marche
   - Bouton rouge (sans token) → ❌ Bloqué

---

## 💡 Astuce : Créer un favori dans votre navigateur

**Chrome/Firefox** :
1. Ouvrir `localhost/es_moulon/public/_tests_securite`
2. Cliquer sur l'étoile ⭐ dans la barre d'adresse
3. Renommer : "🔒 Tests Sécurité - EXAM RNCP"

Le jour de l'examen, vous cliquez juste sur le favori !

---

## 🎤 Phrase à dire au jury

> "Pour démontrer que ma sécurité fonctionne, j'ai créé une page de tests accessible en local. Je vais vous montrer..."
>
> *[Vous ouvez localhost/es_moulon/public/_tests_securite]*
>
> "Voici 2 scénarios : avec token CSRF et sans token. Regardez ce qui se passe quand j'essaie de soumettre un formulaire sans le token de sécurité..."
>
> *[Vous cliquez sur le bouton rouge]*
>
> "Vous voyez ? 'Token CSRF invalide. Tentative d'attaque détectée'. Ma protection fonctionne."

---

## ✅ À vérifier avant l'examen

- [ ] Laragon démarre correctement
- [ ] L'URL `localhost/es_moulon/public/_tests_securite` fonctionne
- [ ] Les 2 boutons du test CSRF fonctionnent
- [ ] Vous avez répété votre explication (maximum 2 minutes)

---

**Date de création** : 28 octobre 2025  
**Examen** : 25 novembre 2025  
**Score attendu avec cette démo** : 95/100 🌟
