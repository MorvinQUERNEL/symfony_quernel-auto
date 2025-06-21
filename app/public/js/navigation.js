/**
 * NAVIGATION - QUERNEL AUTO
 * Gestion moderne de la navigation responsive
 * @author Quernel Auto
 * @version 2.0.0
 */

class Navigation {
    constructor() {
        this.navbar = document.querySelector('.navbar');
        this.navbarToggler = document.querySelector('.navbar-toggler');
        this.navbarNav = document.querySelector('.navbar-nav');
        this.navbarActions = document.querySelector('.navbar-actions');
        this.dropdowns = document.querySelectorAll('.dropdown');
        this.isMenuOpen = false;
        this.lastScrollTop = 0;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupScrollEffects();
        this.setupDropdowns();
        this.setupActiveLinks();
        this.setupKeyboardNavigation();
        this.setupTouchGestures();
    }
    
    /**
     * Configuration des écouteurs d'événements
     */
    setupEventListeners() {
        // Toggle du menu burger
        if (this.navbarToggler) {
            this.navbarToggler.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMenu();
            });
        }
        
        // Fermeture du menu au clic sur un lien
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    this.closeMenu();
                }
            });
        });
        
        // Fermeture du menu au clic en dehors
        document.addEventListener('click', (e) => {
            if (!this.navbar.contains(e.target) && this.isMenuOpen) {
                this.closeMenu();
            }
        });
        
        // Gestion de la touche Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMenu();
            }
        });
        
        // Gestion du redimensionnement
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
        
        // Nettoyage lors de la navigation
        window.addEventListener('beforeunload', () => {
            this.cleanupOverlays();
        });
        
        // Nettoyage lors du changement de focus (navigation mobile)
        window.addEventListener('blur', () => {
            if (this.isMenuOpen) {
                this.closeMenu();
            }
        });
    }
    
    /**
     * Effets de scroll sur la navbar
     */
    setupScrollEffects() {
        let scrollTimer;
        
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimer);
            
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const isScrollingDown = scrollTop > this.lastScrollTop;
            
            // Ajout de la classe scrolled
            if (scrollTop > 50) {
                this.navbar.classList.add('scrolled');
            } else {
                this.navbar.classList.remove('scrolled');
            }
            
            // Masquage/Affichage de la navbar au scroll
            if (scrollTop > 100) {
                if (isScrollingDown && scrollTop > 200) {
                    this.navbar.style.transform = 'translateY(-100%)';
                } else {
                    this.navbar.style.transform = 'translateY(0)';
                }
            } else {
                this.navbar.style.transform = 'translateY(0)';
            }
            
            this.lastScrollTop = scrollTop;
            
            // Réinitialisation après arrêt du scroll
            scrollTimer = setTimeout(() => {
                this.navbar.style.transform = 'translateY(0)';
            }, 1000);
        });
    }
    
    /**
     * Gestion des menus déroulants
     */
    setupDropdowns() {
        this.dropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (trigger && menu) {
                // Clic pour ouvrir/fermer
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.toggleDropdown(dropdown);
                });
                
                // Fermeture au clic en dehors
                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target)) {
                        this.closeDropdown(dropdown);
                    }
                });
                
                // Gestion du hover sur desktop
                if (window.innerWidth >= 768) {
                    dropdown.addEventListener('mouseenter', () => {
                        this.openDropdown(dropdown);
                    });
                    
                    dropdown.addEventListener('mouseleave', () => {
                        this.closeDropdown(dropdown);
                    });
                }
            }
        });
    }
    
    /**
     * Configuration des liens actifs
     */
    setupActiveLinks() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath === href) {
                link.setAttribute('aria-current', 'page');
                link.classList.add('active');
            }
        });
    }
    
    /**
     * Navigation au clavier
     */
    setupKeyboardNavigation() {
        const focusableElements = this.navbar.querySelectorAll(
            'a[href], button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
        );
        
        this.navbar.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];
                
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        });
    }
    
    /**
     * Gestes tactiles pour mobile
     */
    setupTouchGestures() {
        if ('ontouchstart' in window) {
            let startX = 0;
            let startY = 0;
            
            this.navbar.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            });
            
            this.navbar.addEventListener('touchmove', (e) => {
                if (!startX || !startY) return;
                
                const diffX = startX - e.touches[0].clientX;
                const diffY = startY - e.touches[0].clientY;
                
                // Swipe horizontal pour fermer le menu
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    if (diffX > 0 && this.isMenuOpen) {
                        this.closeMenu();
                    }
                }
                
                startX = 0;
                startY = 0;
            });
        }
    }
    
    /**
     * Ouverture du menu
     */
    openMenu() {
        if (this.isMenuOpen) return;
        
        this.isMenuOpen = true;
        this.navbarToggler?.classList.add('active');
        this.navbarNav?.classList.add('active');
        this.navbarActions?.classList.add('active');
        
        // Création de l'overlay
        this.createOverlay();
        
        // Désactivation du scroll
        document.body.style.overflow = 'hidden';
        
        // Focus sur le premier élément du menu
        const firstLink = this.navbarNav?.querySelector('.nav-link');
        if (firstLink) {
            setTimeout(() => firstLink.focus(), 100);
        }
        
        // Animation d'entrée
        this.animateMenuIn();
    }
    
    /**
     * Fermeture du menu
     */
    closeMenu() {
        if (!this.isMenuOpen) return;
        
        this.isMenuOpen = false;
        this.navbarToggler?.classList.remove('active');
        this.navbarNav?.classList.remove('active');
        this.navbarActions?.classList.remove('active');
        
        // Suppression de l'overlay
        this.removeOverlay();
        
        // Réactivation du scroll
        document.body.style.overflow = '';
        
        // Animation de sortie
        this.animateMenuOut();
        
        // Nettoyage d'urgence après 1 seconde
        setTimeout(() => {
            this.cleanupOverlays();
        }, 1000);
    }
    
    /**
     * Toggle du menu
     */
    toggleMenu() {
        if (this.isMenuOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    /**
     * Ouverture d'un dropdown
     */
    openDropdown(dropdown) {
        // Fermeture des autres dropdowns
        this.dropdowns.forEach(d => {
            if (d !== dropdown) {
                this.closeDropdown(d);
            }
        });
        
        dropdown.classList.add('active');
        
        // Animation d'ouverture
        const menu = dropdown.querySelector('.dropdown-menu');
        if (menu) {
            menu.style.display = 'block';
            menu.style.opacity = '0';
            menu.style.transform = 'translateY(-10px)';
            
            requestAnimationFrame(() => {
                menu.style.transition = 'all 0.2s ease-out';
                menu.style.opacity = '1';
                menu.style.transform = 'translateY(0)';
            });
        }
    }
    
    /**
     * Fermeture d'un dropdown
     */
    closeDropdown(dropdown) {
        dropdown.classList.remove('active');
        
        const menu = dropdown.querySelector('.dropdown-menu');
        if (menu) {
            menu.style.opacity = '0';
            menu.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                menu.style.display = 'none';
                menu.style.transition = '';
            }, 200);
        }
    }
    
    /**
     * Toggle d'un dropdown
     */
    toggleDropdown(dropdown) {
        if (dropdown.classList.contains('active')) {
            this.closeDropdown(dropdown);
        } else {
            this.openDropdown(dropdown);
        }
    }
    
    /**
     * Création de l'overlay
     */
    createOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'navbar-overlay';
        overlay.addEventListener('click', () => this.closeMenu());
        
        document.body.appendChild(overlay);
        
        requestAnimationFrame(() => {
            overlay.classList.add('active');
        });
    }
    
    /**
     * Suppression de l'overlay
     */
    removeOverlay() {
        const overlay = document.querySelector('.navbar-overlay');
        if (overlay) {
            // Suppression immédiate de la classe active
            overlay.classList.remove('active');
            
            // Suppression de l'élément après l'animation de fade-out
            setTimeout(() => {
                if (overlay && overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 200); // Réduit de 300ms à 200ms
            
            // Fallback : suppression forcée après 1 seconde si l'élément existe encore
            setTimeout(() => {
                const remainingOverlay = document.querySelector('.navbar-overlay');
                if (remainingOverlay && remainingOverlay.parentNode) {
                    remainingOverlay.parentNode.removeChild(remainingOverlay);
                }
            }, 1000);
        }
    }
    
    /**
     * Animation d'entrée du menu
     */
    animateMenuIn() {
        const navItems = this.navbarNav?.querySelectorAll('.nav-item');
        const actionItems = this.navbarActions?.querySelectorAll('*');
        
        if (navItems) {
            navItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(-20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease-out';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }
        
        if (actionItems) {
            actionItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(-20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease-out';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, (navItems?.length || 0) * 50 + index * 50);
            });
        }
    }
    
    /**
     * Animation de sortie du menu
     */
    animateMenuOut() {
        const navItems = this.navbarNav?.querySelectorAll('.nav-item');
        const actionItems = this.navbarActions?.querySelectorAll('*');
        
        if (navItems) {
            navItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.transition = 'all 0.2s ease-in';
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(-20px)';
                }, index * 30);
            });
        }
        
        if (actionItems) {
            actionItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.transition = 'all 0.2s ease-in';
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(-20px)';
                }, (navItems?.length || 0) * 30 + index * 30);
            });
        }
    }
    
    /**
     * Gestion du redimensionnement
     */
    handleResize() {
        if (window.innerWidth >= 768 && this.isMenuOpen) {
            this.closeMenu();
        }
        
        // Réinitialisation des styles
        const navItems = this.navbarNav?.querySelectorAll('.nav-item');
        const actionItems = this.navbarActions?.querySelectorAll('*');
        
        if (navItems) {
            navItems.forEach(item => {
                item.style.opacity = '';
                item.style.transform = '';
                item.style.transition = '';
            });
        }
        
        if (actionItems) {
            actionItems.forEach(item => {
                item.style.opacity = '';
                item.style.transform = '';
                item.style.transition = '';
            });
        }
    }
    
    /**
     * Méthode publique pour fermer le menu
     */
    close() {
        this.closeMenu();
    }
    
    /**
     * Méthode publique pour ouvrir le menu
     */
    open() {
        this.openMenu();
    }
    
    /**
     * Nettoyage d'urgence des overlays
     */
    cleanupOverlays() {
        const overlays = document.querySelectorAll('.navbar-overlay');
        overlays.forEach(overlay => {
            if (overlay && overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        });
        
        // Réactivation du scroll si nécessaire
        if (!this.isMenuOpen) {
            document.body.style.overflow = '';
        }
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    window.navigation = new Navigation();
});

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Navigation;
}

// Rendre la classe Navigation disponible globalement
window.Navigation = Navigation; 