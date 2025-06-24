document.addEventListener('DOMContentLoaded', () => {
    // Navigation burger menu
    const burger = document.querySelector('.navbar-burger');
    const menu = document.querySelector('.navbar-menu');
    const navLinks = document.querySelectorAll('.nav-link');
    const body = document.body;
    
    // Fonction pour ouvrir le menu
    const openMenu = () => {
        burger.classList.add('is-active');
        menu.classList.add('is-active');
        burger.setAttribute('aria-expanded', 'true');
        body.style.overflow = 'hidden';
        
        // Focus sur le premier lien du menu pour l'accessibilité
        const firstLink = menu.querySelector('.nav-link');
        if (firstLink) {
            setTimeout(() => firstLink.focus(), 300);
        }
    };
    
    // Fonction pour fermer le menu
    const closeMenu = () => {
        burger.classList.remove('is-active');
        menu.classList.remove('is-active');
        burger.setAttribute('aria-expanded', 'false');
        body.style.overflow = '';
        
        // Retourner le focus sur le burger
        burger.focus();
    };
    
    // Toggle menu
    burger.addEventListener('click', (e) => {
        e.preventDefault();
        if (menu.classList.contains('is-active')) {
            closeMenu();
        } else {
            openMenu();
        }
    });
    
    // Fermer le menu quand on clique sur un lien
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            closeMenu();
        });
    });
    
    // Fermer le menu quand on clique en dehors
    document.addEventListener('click', (e) => {
        if (menu.classList.contains('is-active') && 
            !burger.contains(e.target) && 
            !menu.contains(e.target)) {
            closeMenu();
        }
    });
    
    // Fermer le menu avec la touche Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('is-active')) {
            closeMenu();
        }
    });
    
    // Gestion du redimensionnement de la fenêtre
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth > 768) {
                closeMenu();
            }
        }, 250);
    });
    
    // Amélioration de l'accessibilité
    burger.setAttribute('aria-label', 'Menu de navigation');
    burger.setAttribute('aria-expanded', 'false');
    burger.setAttribute('aria-controls', 'navbar-menu');
    
    // Navigation au clavier dans le menu
    const focusableElements = menu.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])');
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];
    
    menu.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        }
    });
    
    // Animation fluide pour les liens
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', () => {
            if (window.innerWidth <= 768) {
                link.style.transform = 'translateX(10px)';
            }
        });
        
        link.addEventListener('mouseleave', () => {
            if (window.innerWidth <= 768) {
                link.style.transform = 'translateX(0)';
            }
        });
    });
}); 