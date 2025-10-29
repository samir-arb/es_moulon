/**
 * 🍪 Système de Consentement RGPD
 * Gère l'acceptation/refus des cookies et du tracking
 */

(function() {
    'use strict';

    // Vérifier si le consentement a déjà été donné
    const consent = getCookie('cookie_consent');
    
    if (!consent) {
        // Afficher la bannière après 1 seconde
        setTimeout(showCookieBanner, 1000);
    }

    // Afficher la bannière
    function showCookieBanner() {
        const banner = document.getElementById('cookie-consent');
        const overlay = document.getElementById('cookie-consent-overlay');
        
        if (banner) {
            banner.classList.add('active');
            if (overlay) overlay.classList.add('active');
        }
    }

    // Masquer la bannière
    function hideCookieBanner() {
        const banner = document.getElementById('cookie-consent');
        const overlay = document.getElementById('cookie-consent-overlay');
        
        if (banner) {
            banner.style.animation = 'slideDown 0.5s ease-out';
            setTimeout(() => {
                banner.classList.remove('active');
                if (overlay) overlay.classList.add('active');
            }, 500);
        }
    }

    // Accepter les cookies
    window.acceptCookies = function() {
        setCookie('cookie_consent', 'accepted', 365);
        setCookie('tracking_consent', 'yes', 365);
        hideCookieBanner();
        
        // Message de confirmation (optionnel)
        showNotification('✅ Préférences enregistrées. Merci !', 'success');
    };

    // Refuser les cookies
    window.refuseCookies = function() {
        setCookie('cookie_consent', 'refused', 365);
        setCookie('tracking_consent', 'no', 365);
        hideCookieBanner();
        
        // Message de confirmation
        showNotification('❌ Tracking désactivé. Vos données ne seront pas collectées.', 'info');
    };

    // Paramètres personnalisés (à développer si besoin)
    window.openCookieSettings = function() {
        alert('Fonctionnalité à venir : Vous pourrez bientôt personnaliser vos préférences de cookies.');
    };

    // Rouvrir la bannière (depuis le footer par exemple)
    window.reopenCookieBanner = function() {
        // Supprimer les cookies de consentement
        deleteCookie('cookie_consent');
        deleteCookie('tracking_consent');
        showCookieBanner();
    };

    // ===================================
    // Fonctions utilitaires cookies
    // ===================================

    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
    }

    function getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function deleteCookie(name) {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
    }

    // ===================================
    // Notification temporaire
    // ===================================
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : '#3b82f6'};
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10000;
            font-weight: 600;
            animation: slideInRight 0.3s ease-out;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.5s';
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }

    // Animation CSS (si pas déjà définie)
    if (!document.querySelector('#cookie-consent-animations')) {
        const style = document.createElement('style');
        style.id = 'cookie-consent-animations';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideDown {
                from { transform: translateY(0); opacity: 1; }
                to { transform: translateY(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }

})();
