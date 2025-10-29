<?php
require_once __DIR__ . '/../includes/config.php';

?>

<link rel="stylesheet" href="<?= asset('_front.css/legal-pages.css') ?>">

<main class="page-content">
  <section class="page-header">
    <div class="container">
      <h1>Politique de confidentialité</h1>
      <p class="subtitle">Protection de vos données personnelles — Conforme RGPD</p>
    </div>
  </section>

  <section class="content-section">
    <div class="container legal-content">

  <section>
    <h2>ARTICLE 1 — Préambule</h2>
    <p>
      Le site <strong>esmoulon.fr</strong> (ci-après « le Site ») édité par l'<strong>ES Moulon</strong> 
      s'engage à protéger les données personnelles de ses utilisateurs, conformément à la réglementation en vigueur :
    </p>
    <ul>
      <li>Règlement général sur la protection des données (RGPD — Règlement UE 2016/679) ;</li>
      <li>Loi n° 78-17 du 6 janvier 1978 dite « Informatique et Libertés » (modifiée) ;</li>
      <li>Article 82 de la loi Informatique et Libertés concernant les cookies et traceurs.</li>
    </ul>
    <p>
      La présente politique décrit la manière dont nous collectons, utilisons et protégeons 
      vos informations personnelles lorsque vous utilisez notre Site.
    </p>
  </section>

  <section>
    <h2>ARTICLE 2 — Principes de protection des données</h2>
    <p>Conformément à l'article 5 du RGPD, vos données personnelles sont :</p>
    <ul>
      <li><strong>Traitées de manière licite, loyale et transparente</strong> ;</li>
      <li><strong>Collectées uniquement pour des finalités déterminées</strong> ;</li>
      <li><strong>Adéquates, pertinentes et limitées</strong> au nécessaire (principe de minimisation) ;</li>
      <li><strong>Conservées pendant une durée limitée</strong>, sauf obligation légale ;</li>
      <li><strong>Protégées par des mesures techniques et organisationnelles appropriées</strong>.</li>
    </ul>
  </section>

  <section>
    <h2>ARTICLE 3 — Données collectées et finalités</h2>
    
    <h3>3.1 Données des formulaires de contact</h3>
    <p><strong>Données collectées :</strong> Nom, prénom, e-mail, téléphone (optionnel), message.</p>
    <p><strong>Collecte :</strong> Via les formulaires de contact, candidature arbitre, bénévole ou partenaire.</p>
    <p><strong>Conservation :</strong> 3 ans après le dernier contact (sauf obligation légale différente).</p>
    <p><strong>Finalités :</strong> Répondre aux sollicitations, gérer les candidatures, organiser la vie du club.</p>
    <p><strong>Base légale :</strong> Consentement explicite de l'utilisateur lors de l'envoi du formulaire.</p>
    <p><strong>Transmission :</strong> Les données ne sont pas transmises à des tiers hors obligations légales.</p>
    
    <h3>3.2 Données de navigation et statistiques (Tracking)</h3>
    <p><strong>Données collectées :</strong> Adresse IP, User-Agent (navigateur et système d'exploitation), pages visitées, 
    URL de provenance (referer), date et heure de la visite.</p>
    <p><strong>Collecte :</strong> Uniquement si vous <strong>acceptez les cookies</strong> via la bannière de consentement.</p>
    <p><strong>Conservation :</strong> Les données de navigation sont conservées pendant toute la durée d'exploitation du site 
    en phase de développement. En production, une durée de <strong>12 à 24 mois</strong> sera appliquée conformément aux recommandations de la CNIL.</p>
    <p><strong>Finalités :</strong> Analyser le trafic du site, améliorer l'expérience utilisateur, comprendre les pages les plus consultées.</p>
    <p><strong>Base légale :</strong> Consentement explicite via la bannière cookies (Article 82 de la loi Informatique et Libertés).</p>
    <p><strong>Droit de refus :</strong> Vous pouvez refuser le tracking en cliquant sur "Refuser" dans la bannière cookies. 
    Dans ce cas, <strong>aucune donnée de navigation ne sera collectée</strong>. Vous pouvez modifier votre choix à tout moment 
    via le lien "🍪 Gérer les cookies" en bas de page.</p>
    <p><strong>Hébergement :</strong> Les données sont stockées dans une base de données MySQL hébergée en France.</p>

    <h3>3.3 Informations sur l'hébergeur</h3>
    <p>
      <strong>Environnement actuel :</strong> Phase de développement — Hébergement local (Laragon)<br>
      <strong>Localisation :</strong> France<br>
      <strong>Base de données :</strong> MySQL (stockage local sécurisé)
    </p>
    <p>
      <strong>⚠️ Note :</strong> Lors de la mise en production, le site sera hébergé chez un prestataire professionnel 
      français conforme RGPD (OVH, O2Switch, ou équivalent). Cette page sera mise à jour avec les coordonnées complètes.
    </p>
    <p>
      Les données sont et seront hébergées en France, soumises à la législation française (RGPD et loi Informatique et Libertés).
    </p>
  </section>

  <section>
    <h2>ARTICLE 4 — Responsable de traitement & DPO</h2>
    <h3>A. Responsable</h3>
    <p>
      <strong>ES Moulon</strong> (association loi 1901)<br>
      <strong>Adresse :</strong> Rue de la Sente aux Loups, 18000 Bourges<br>
      <strong>SIRET :</strong> 395 367 451 00014<br>
      <strong>Téléphone :</strong> 02 48 65 28 15<br>
      <strong>E-mail :</strong> <a href="mailto:contact@esmoulon.fr">contact@esmoulon.fr</a><br>
      <strong>Représentant légal :</strong> BARBOSA David (Président)
    </p>

    <h3>B. Engagements</h3>
    <ul>
      <li>Protection des données par des mesures techniques et organisationnelles appropriées ;</li>
      <li>Chiffrement des communications (certificat SSL/TLS) ;</li>
      <li>Traitement sécurisé des mots de passe (hachage bcrypt) ;</li>
      <li>Protection contre les attaques CSRF (tokens de sécurité) ;</li>
      <li>Information en cas de modification ou suppression de données ;</li>
      <li>Notification en cas d'incident majeur (violation de données).</li>
    </ul>

    <h3>C. Délégué à la protection des données (DPO)</h3>
    <p>Le cas échéant, vous pouvez contacter le DPO via <a href="mailto:contact@esmoulon.fr">contact@esmoulon.fr</a> 
    ou par courrier à l'adresse ci-dessus.</p>
  </section>

  <section>
    <h2>ARTICLE 5 — Droits des utilisateurs</h2>
    <p>Conformément aux articles 15 à 22 du RGPD, vous disposez des droits suivants :</p>
    <ul>
      <li><strong>Droit d'accès :</strong> obtenir la confirmation que vos données sont traitées et en obtenir une copie ;</li>
      <li><strong>Droit de rectification :</strong> corriger vos données inexactes ou incomplètes ;</li>
      <li><strong>Droit d'effacement (« droit à l'oubli ») :</strong> demander la suppression de vos données ;</li>
      <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré et lisible par machine ;</li>
      <li><strong>Droit de limitation du traitement :</strong> suspendre le traitement de vos données ;</li>
      <li><strong>Droit d'opposition :</strong> vous opposer au traitement de vos données pour des motifs légitimes ;</li>
      <li><strong>Droit de définir le sort de vos données après votre décès</strong> (article 40-1 de la loi Informatique et Libertés) ;</li>
      <li><strong>Droit d'introduire une réclamation :</strong> saisir la CNIL en cas de non-respect de vos droits 
        (<a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>).</li>
    </ul>
    <p>
      <strong>Exercer vos droits :</strong> Contactez-nous par e-mail à 
      <a href="mailto:contact@esmoulon.fr">contact@esmoulon.fr</a> ou par courrier postal. 
      Nous vous répondrons dans un délai maximum de <strong>30 jours</strong>.
    </p>
  </section>

  <section>
    <h2>ARTICLE 6 — Cookies et traceurs</h2>
    <p>
      Le Site utilise un système de tracking interne (sans cookies tiers) pour analyser le trafic. 
      Ce système collecte des données de navigation <strong>uniquement si vous acceptez les cookies</strong> 
      via la bannière de consentement affichée lors de votre première visite.
    </p>
    <h3>Gestion de vos préférences cookies</h3>
    <p>Vous pouvez à tout moment :</p>
    <ul>
      <li>Modifier votre choix via le lien <strong>"🍪 Gérer les cookies"</strong> en bas de page ;</li>
      <li>Configurer votre navigateur pour refuser tous les cookies :</li>
    </ul>
    <ul style="list-style-type: circle; margin-left: 2em;">
      <li><strong>Chrome :</strong> <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">support.google.com/chrome/answer/95647</a></li>
      <li><strong>Firefox :</strong> <a href="https://support.mozilla.org/fr/kb/activer-desactiver-cookies" target="_blank" rel="noopener">support.mozilla.org/fr/kb/activer-desactiver-cookies</a></li>
      <li><strong>Safari :</strong> <a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" rel="noopener">support.apple.com/fr-fr/guide/safari/sfri11471</a></li>
      <li><strong>Edge :</strong> <a href="https://support.microsoft.com/fr-fr/microsoft-edge/supprimer-les-cookies-dans-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener">support.microsoft.com</a></li>
    </ul>
    <p><strong>⚠️ Attention :</strong> Si vous refusez tous les cookies, certaines fonctionnalités du Site peuvent être limitées.</p>
  </section>

  <section>
    <h2>ARTICLE 7 — Modifications de la politique</h2>
    <p>
      La présente politique de confidentialité peut être modifiée à tout moment pour refléter 
      les évolutions légales ou les mises à jour du Site. Toute modification sera publiée sur cette page.
    </p>
    <p><strong>Dernière mise à jour :</strong> <?= date("d/m/Y") ?></p>
  </section>

    </div>
  </section>
</main>


