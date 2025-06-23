/**
 * NAVIGATION - QUERNEL AUTO
 * Gestion moderne de la navigation responsive
 * @author Quernel Auto
 * @version 3.0.0
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
                // Handler de clic pour le trigger
                const clickHandler = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // En mode mobile avec menu burger ouvert, ne pas toggle les dropdowns
                    if (window.innerWidth < 768 && this.isMenuOpen) {
                        return;
                    }
                    
                    // Toggle du dropdown
                    this.toggleDropdown(dropdown);
                };
                
                // Ajout de l'événement de clic
                trigger.addEventListener('click', clickHandler);
                
                // Gestion du hover sur desktop uniquement
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
        
        // Fermeture des dropdowns au clic en dehors
        document.addEventListener('click', (e) => {
            const clickedDropdown = e.target.closest('.dropdown');
            
            // En mode mobile, ne pas fermer les dropdowns automatiquement
            if (window.innerWidth < 768) {
                return;
            }
            
            this.dropdowns.forEach(dropdown => {
                if (dropdown !== clickedDropdown) {
                    this.closeDropdown(dropdown);
                }
            });
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
        
        // Ouvrir automatiquement les dropdowns en mode mobile
        if (window.innerWidth < 768) {
            setTimeout(() => {
                // Ouvrir le dropdown Services
                const servicesDropdown = document.querySelector('.navbar-nav .dropdown');
                console.log('Services dropdown trouvé:', servicesDropdown);
                if (servicesDropdown) {
                    this.openDropdown(servicesDropdown);
                }
                
                // Ouvrir le dropdown Profil
                const profileDropdown = document.querySelector('.navbar-actions .dropdown');
                console.log('Profile dropdown trouvé:', profileDropdown);
                if (profileDropdown) {
                    this.openDropdown(profileDropdown);
                }
            }, 500); // Délai plus long pour s'assurer que le menu est complètement ouvert
        }
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
        
        // Fermer tous les dropdowns seulement quand le menu burger se ferme
        if (window.innerWidth < 768) {
            setTimeout(() => {
                this.dropdowns.forEach(dropdown => {
                    this.closeDropdown(dropdown);
                });
            }, 300); // Attendre la fin de l'animation de fermeture
        }
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
        // Fermeture des autres dropdowns seulement en desktop
        if (window.innerWidth >= 768) {
            this.dropdowns.forEach(d => {
                if (d !== dropdown) {
                    this.closeDropdown(d);
                }
            });
        }
        
        dropdown.classList.add('active');
        
        // Animation d'ouverture
        const menu = dropdown.querySelector('.dropdown-menu');
        if (menu) {
            menu.style.display = 'block';
            
            // Animation différente selon le mode
            if (window.innerWidth < 768) {
                // Mode mobile : animation de slide down
                menu.style.opacity = '0';
                menu.style.transform = 'translateY(-10px)';
                menu.style.maxHeight = '0';
                
                requestAnimationFrame(() => {
                    menu.style.transition = 'all 0.3s ease-out';
                    menu.style.opacity = '1';
                    menu.style.transform = 'translateY(0)';
                    menu.style.maxHeight = '500px';
                });
            } else {
                // Mode desktop : animation de fade
                menu.style.opacity = '0';
                menu.style.transform = 'translateY(-10px)';
                
                requestAnimationFrame(() => {
                    menu.style.transition = 'all 0.2s ease-out';
                    menu.style.opacity = '1';
                    menu.style.transform = 'translateY(0)';
                });
            }
        }
    }
    
    /**
     * Fermeture d'un dropdown
     */
    closeDropdown(dropdown) {
        dropdown.classList.remove('active');
        
        const menu = dropdown.querySelector('.dropdown-menu');
        if (menu) {
            // Animation différente selon le mode
            if (window.innerWidth < 768) {
                // Mode mobile : animation de slide up
                menu.style.transition = 'all 0.3s ease-in';
                menu.style.opacity = '0';
                menu.style.transform = 'translateY(-10px)';
                menu.style.maxHeight = '0';
                
                setTimeout(() => {
                    menu.style.display = 'none';
                    menu.style.transition = '';
                    menu.style.maxHeight = '';
                }, 300);
            } else {
                // Mode desktop : animation de fade
                menu.style.opacity = '0';
                menu.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    menu.style.display = 'none';
                    menu.style.transition = '';
                }, 200);
            }
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
        overlay.addEventListener('click', () => {
            this.closeMenu();
        });
        
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
            overlay.classList.remove('active');
            
            // Suppression immédiate de l'overlay
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
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
        if (window.innerWidth >= 768) {
            // En desktop, fermer le menu mobile
            this.closeMenu();
            
            // Fermer tous les dropdowns seulement en desktop
            this.dropdowns.forEach(dropdown => {
                this.closeDropdown(dropdown);
            });
        }
    }
    
    /**
     * Nettoyage
     */
    cleanup() {
        // Suppression des overlays
        const overlays = document.querySelectorAll('.navbar-overlay');
        overlays.forEach(overlay => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        });
        
        // Réactivation du scroll
        document.body.style.overflow = '';
    }
}

// Initialisation de la navigation
document.addEventListener('DOMContentLoaded', () => {
    new Navigation();
}); 