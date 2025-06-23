/**
 * NAVIGATION - QUERNEL AUTO
 * Menu burger responsive simple et efficace
 * @author Quernel Auto
 * @version 6.0.1
 */

class Navigation {
    constructor() {
        this.navbar = document.querySelector('.navbar');
        this.navbarToggler = document.querySelector('.navbar-toggler');
        this.navbarNav = document.querySelector('.navbar-nav');
        this.navbarActions = document.querySelector('.navbar-actions');
        this.isMenuOpen = false;
        this.overlay = null;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupScrollEffects();
        this.setupActiveLinks();
        this.setupDropdowns();
        
        // Nettoyage initial au cas où
        this.removeAllOverlays();
    }
    
    /**
     * Configuration des écouteurs d'événements
     */
    setupEventListeners() {
        // Toggle du menu burger
        if (this.navbarToggler) {
            this.navbarToggler.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleMenu();
            });
        }
        
        // Fermeture du menu au clic sur un lien (sauf dropdowns)
        const navLinks = document.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768 && this.isMenuOpen) {
                    this.closeMenu();
                }
            });
        });
        
        // Gestion de la touche Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMenu();
            }
        });
        
        // Fermeture au clic sur l'overlay
        document.addEventListener('click', (e) => {
            if (e.target && e.target.classList && e.target.classList.contains('navbar-overlay')) {
                e.preventDefault();
                e.stopPropagation();
                this.closeMenu();
            }
        });
        
        // Gestion du redimensionnement
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth >= 768 && this.isMenuOpen) {
                    this.closeMenu();
                }
            }, 250);
        });
    }
    
    /**
     * Effets de scroll sur la navbar
     */
    setupScrollEffects() {
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Ajout de la classe scrolled
            if (scrollTop > 50) {
                this.navbar.classList.add('scrolled');
            } else {
                this.navbar.classList.remove('scrolled');
            }
        }, { passive: true });
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
     * Configuration des dropdowns
     */
    setupDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown');
        
        dropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (trigger && menu) {
                // Sur mobile uniquement
                if (window.innerWidth < 768) {
                    trigger.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.toggleDropdown(dropdown);
                    });
                }
            }
        });
    }
    
    /**
     * Toggle d'un dropdown
     */
    toggleDropdown(dropdown) {
        const menu = dropdown.querySelector('.dropdown-menu');
        const isOpen = menu.style.display === 'block';
        
        // Fermer tous les autres dropdowns
        const allDropdowns = document.querySelectorAll('.dropdown');
        allDropdowns.forEach(d => {
            if (d !== dropdown) {
                this.closeDropdown(d);
            }
        });
        
        if (isOpen) {
            this.closeDropdown(dropdown);
        } else {
            this.openDropdown(dropdown);
        }
    }
    
    /**
     * Ouverture d'un dropdown
     */
    openDropdown(dropdown) {
        const menu = dropdown.querySelector('.dropdown-menu');
        if (menu) {
            menu.style.display = 'block';
            menu.style.opacity = '1';
            menu.style.visibility = 'visible';
            menu.style.transform = 'translateY(0)';
            menu.style.maxHeight = '200px';
            
            // Décalage du menu si c'est le dropdown du profil
            const parent = dropdown.parentElement;
            if (parent && parent.classList.contains('navbar-actions')) {
                const dropdownHeight = menu.scrollHeight;
                if (this.navbarNav) {
                    this.navbarNav.style.transform = `translateY(${dropdownHeight + 20}px)`;
                    this.navbarNav.style.transition = 'transform 0.3s ease';
                }
                if (this.navbarActions) {
                    this.navbarActions.style.transform = `translateY(${dropdownHeight + 20}px)`;
                    this.navbarActions.style.transition = 'transform 0.3s ease';
                }
            }
        }
    }
    
    /**
     * Fermeture d'un dropdown
     */
    closeDropdown(dropdown) {
        const menu = dropdown.querySelector('.dropdown-menu');
        if (menu) {
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            menu.style.transform = 'translateY(-10px)';
            menu.style.maxHeight = '0';
            
            setTimeout(() => {
                menu.style.display = 'none';
            }, 300);
        }
    }
    
    /**
     * Ouverture du menu burger
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
        document.documentElement.style.overflow = 'hidden';
    }
    
    /**
     * Fermeture du menu burger
     */
    closeMenu() {
        if (!this.isMenuOpen) return;
        
        this.isMenuOpen = false;
        
        // Retirer les classes active
        this.navbarToggler?.classList.remove('active');
        this.navbarNav?.classList.remove('active');
        this.navbarActions?.classList.remove('active');
        
        // Suppression de l'overlay
        this.removeOverlay();
        
        // Forcer la suppression de tous les overlays après un délai
        setTimeout(() => {
            this.removeAllOverlays();
        }, 350);
        
        // Réactivation du scroll
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        
        // Fermer tous les dropdowns
        this.closeAllDropdowns();
        
        // Réinitialiser les positions
        this.resetMenuPositions();
    }
    
    /**
     * Toggle du menu burger
     */
    toggleMenu() {
        if (this.isMenuOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    /**
     * Création de l'overlay
     */
    createOverlay() {
        // Supprimer tout overlay existant d'abord
        this.removeAllOverlays();
        
        this.overlay = document.createElement('div');
        this.overlay.className = 'navbar-overlay';
        document.body.appendChild(this.overlay);
        
        // Forcer le reflow
        this.overlay.offsetHeight;
        
        requestAnimationFrame(() => {
            if (this.overlay) {
                this.overlay.classList.add('active');
            }
        });
    }
    
    /**
     * Suppression de l'overlay
     */
    removeOverlay() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
            
            const overlayRef = this.overlay;
            setTimeout(() => {
                if (overlayRef && overlayRef.parentNode) {
                    overlayRef.parentNode.removeChild(overlayRef);
                }
            }, 300);
            
            this.overlay = null;
        }
    }
    
    /**
     * Suppression de tous les overlays (sécurité)
     */
    removeAllOverlays() {
        const overlays = document.querySelectorAll('.navbar-overlay');
        overlays.forEach(overlay => {
            if (overlay && overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        });
        this.overlay = null;
    }
    
    /**
     * Fermeture de tous les dropdowns
     */
    closeAllDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            this.closeDropdown(dropdown);
        });
    }
    
    /**
     * Réinitialisation des positions du menu
     */
    resetMenuPositions() {
        if (this.navbarNav) {
            this.navbarNav.style.transform = '';
            this.navbarNav.style.transition = '';
        }
        if (this.navbarActions) {
            this.navbarActions.style.transform = '';
            this.navbarActions.style.transition = '';
        }
    }
    
    /**
     * Nettoyage complet
     */
    cleanup() {
        // Fermer le menu si ouvert
        if (this.isMenuOpen) {
            this.closeMenu();
        }
        
        // Suppression forcée de tous les overlays
        this.removeAllOverlays();
        
        // Réactivation du scroll
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        
        // Réinitialisation de l'état
        this.isMenuOpen = false;
        this.navbarToggler?.classList.remove('active');
        this.navbarNav?.classList.remove('active');
        this.navbarActions?.classList.remove('active');
        
        // Fermer tous les dropdowns
        this.closeAllDropdowns();
        
        // Réinitialiser les positions
        this.resetMenuPositions();
    }
}

// Initialisation de la navigation
document.addEventListener('DOMContentLoaded', () => {
    const navigation = new Navigation();
    
    // Nettoyage lors de la fermeture de la page
    window.addEventListener('beforeunload', () => {
        navigation.cleanup();
    });
    
    // Nettoyage sur le changement de page (SPA)
    window.addEventListener('pagehide', () => {
        navigation.cleanup();
    });
}); 