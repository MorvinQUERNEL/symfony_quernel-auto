/**
 * NAVIGATION - QUERNEL AUTO
 * Version simplifiée sans dropdowns mobile
 */

class Navigation {
    constructor() {
        this.navbar = document.querySelector('.navbar');
        this.navbarToggler = document.querySelector('.navbar-toggler');
        this.navbarNav = document.querySelector('.navbar-nav');
        this.isMenuOpen = false;
        this.overlay = null;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupDesktopDropdowns();
    }
    
    setupEventListeners() {
        // Toggle du menu burger
        if (this.navbarToggler) {
            this.navbarToggler.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMenu();
            });
        }
        
        // Fermeture du menu au clic sur un lien (sauf dropdown toggle sur desktop)
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Sur mobile, on ferme le menu sauf si c'est un dropdown toggle
                if (window.innerWidth < 768 && !link.classList.contains('dropdown-toggle')) {
                    this.closeMenu();
                }
                // Sur desktop, on empêche le comportement par défaut des dropdown toggles
                if (window.innerWidth >= 768 && link.classList.contains('dropdown-toggle')) {
                    e.preventDefault();
                }
            });
        });
        
        // Fermeture au clic sur l'overlay
        document.addEventListener('click', (e) => {
            if (e.target && e.target.classList.contains('navbar-overlay')) {
                this.closeMenu();
            }
        });
        
        // Fermeture avec Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMenu();
            }
        });
        
        // Gestion du redimensionnement
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768 && this.isMenuOpen) {
                this.closeMenu();
            }
        });
    }
    
    setupDesktopDropdowns() {
        // Sur desktop uniquement, gérer le hover des dropdowns
        if (window.innerWidth >= 768) {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('mouseenter', () => {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.style.display = 'block';
                    }
                });
                
                dropdown.addEventListener('mouseleave', () => {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.style.display = 'none';
                    }
                });
            });
        }
    }
    
    toggleMenu() {
        if (this.isMenuOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    openMenu() {
        this.isMenuOpen = true;
        
        // Ajouter les classes actives
        if (this.navbarToggler) this.navbarToggler.classList.add('active');
        if (this.navbarNav) this.navbarNav.classList.add('active');
        
        // Créer l'overlay
        this.createOverlay();
        
        // Bloquer le scroll du body
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
    }
    
    closeMenu() {
        this.isMenuOpen = false;
        
        // Retirer les classes actives
        if (this.navbarToggler) this.navbarToggler.classList.remove('active');
        if (this.navbarNav) this.navbarNav.classList.remove('active');
        
        // Supprimer l'overlay
        this.removeOverlay();
        
        // Réactiver le scroll du body
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
    }
    
    createOverlay() {
        if (this.overlay) return;
        
        this.overlay = document.createElement('div');
        this.overlay.className = 'navbar-overlay';
        document.body.appendChild(this.overlay);
        
        // Animation d'apparition
        setTimeout(() => {
            if (this.overlay) {
                this.overlay.classList.add('active');
            }
        }, 10);
    }
    
    removeOverlay() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
            
            setTimeout(() => {
                if (this.overlay && this.overlay.parentNode) {
                    this.overlay.parentNode.removeChild(this.overlay);
                    this.overlay = null;
                }
            }, 300);
        }
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    new Navigation();
}); 