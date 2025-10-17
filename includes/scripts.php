

<script src="<?= asset('js/app.js') ?>" defer></script>

<!-- menu Burger -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const burger = document.getElementById('burger');
    const menu = document.querySelector('.menu');
    const body = document.body;

    if (!burger || !menu) {
        console.error('Burger ou menu introuvable');
        return;
    }

    // ========================================
    // OUVERTURE / FERMETURE DU MENU BURGER
    // ========================================
    burger.addEventListener('click', (e) => {
        e.stopPropagation();
        
        const isActive = burger.classList.toggle('active');
        menu.classList.toggle('active');
        
        // Ajoute une classe au body pour l'overlay
        if (isActive) {
        body.classList.add('menu-open');
        } else {
        body.classList.remove('menu-open');
        // Ferme tous les sous-menus ouverts
        closeAllSubmenus();
        }
    });

    // ========================================
    // GESTION DES SOUS-MENUS SUR MOBILE
    // ========================================
    const submenuParents = document.querySelectorAll('.has-submenu');

    submenuParents.forEach(parent => {
        const link = parent.querySelector(':scope > a');
        const submenu = parent.querySelector('.submenu');

        if (!link || !submenu) return;

        link.addEventListener('click', (e) => {
        // Uniquement sur mobile (≤ 960px)
        if (window.innerWidth <= 960) {
            e.preventDefault();
            e.stopPropagation();

            const isOpen = parent.classList.contains('open');

            if (isOpen) {
            // Ferme le sous-menu actuel
            parent.classList.remove('open');
            submenu.classList.remove('open');
            } else {
            // Ferme les autres sous-menus avant d'ouvrir celui-ci
            closeAllSubmenus();
            
            // Ouvre le sous-menu cliqué
            parent.classList.add('open');
            submenu.classList.add('open');
            }
        }
        // Sur desktop, le lien fonctionne normalement (pas de preventDefault)
        });
    });

    // ========================================
    // FERMETURE DES LIENS SANS SOUS-MENU
    // ========================================
    document.querySelectorAll('.menu > li:not(.has-submenu) > a').forEach(link => {
        link.addEventListener('click', () => {
        if (window.innerWidth <= 960) {
            closeMobileMenu();
        }
        });
    });

    // Fermeture sur clic dans un sous-menu (lien final)
    document.querySelectorAll('.submenu a').forEach(link => {
        link.addEventListener('click', () => {
        if (window.innerWidth <= 960) {
            closeMobileMenu();
        }
        });
    });

    // ========================================
    // FERMETURE EN CLIQUANT EN DEHORS
    // ========================================
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 960) {
        const clickInsideMenu = e.target.closest('.menu');
        const clickOnBurger = e.target.closest('.burger');

        if (!clickInsideMenu && !clickOnBurger && menu.classList.contains('active')) {
            closeMobileMenu();
        }
        }
    });

    // ========================================
    // RÉINITIALISATION AU REDIMENSIONNEMENT
    // ========================================
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
        if (window.innerWidth > 960) {
            // Reset du menu en mode desktop
            burger.classList.remove('active');
            menu.classList.remove('active');
            body.classList.remove('menu-open');
            closeAllSubmenus();
        }
        }, 150);
    });

    // ========================================
    // FONCTIONS UTILITAIRES
    // ========================================
    function closeMobileMenu() {
        burger.classList.remove('active');
        menu.classList.remove('active');
        body.classList.remove('menu-open');
        closeAllSubmenus();
    }

    function closeAllSubmenus() {
        document.querySelectorAll('.has-submenu.open').forEach(item => {
        item.classList.remove('open');
        const submenu = item.querySelector('.submenu');
        if (submenu) {
            submenu.classList.remove('open');
        }
        });
    }
    });
</script>

</body>
</html>

