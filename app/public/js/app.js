/**
 * QUERNEL AUTO - JAVASCRIPT PRINCIPAL
 * Initialisation et gestion des composants
 * @author Quernel Auto
 * @version 2.0.0
 */

class App {
    constructor() {
        this.navigation = null;
        this.carousels = [];
        this.backToTopButton = null;
        
        this.init();
    }
    
    init() {
        // Initialisation de la navigation
        this.initNavigation();
        
        // Initialisation du carousel
        this.initCarousel();
        
        this.setupBackToTop();
        this.setupFlashMessages();
        this.setupLazyLoading();
        this.setupScrollEffects();
        this.setupFormValidation();
        this.setupAccessibility();
        
        console.log('Quernel Auto App initialized');
    }
    
    /**
     * Initialisation de la navigation
     */
    initNavigation() {
        try {
            if (typeof Navigation !== 'undefined') {
                this.navigation = new Navigation();
                console.log('Navigation initialized');
            } else {
                console.warn('Navigation class not found');
            }
        } catch (error) {
            console.error('Error initializing navigation:', error);
        }
    }
    
    /**
     * Initialisation du carousel
     */
    initCarousel() {
        try {
            if (typeof Carousel !== 'undefined') {
                const carouselElements = document.querySelectorAll('.carousel');
                carouselElements.forEach((element, index) => {
                    this.carousels[index] = new Carousel(element);
                });
                console.log(`${this.carousels.length} carousel(s) initialized`);
            } else {
                console.warn('Carousel class not found');
            }
        } catch (error) {
            console.error('Error initializing carousel:', error);
        }
    }
    
    /**
     * Configuration du bouton "Retour en haut"
     */
    setupBackToTop() {
        this.backToTopButton = document.querySelector('.back-to-top');
        
        if (!this.backToTopButton) {
            this.createBackToTopButton();
        }
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                this.backToTopButton.classList.add('visible');
            } else {
                this.backToTopButton.classList.remove('visible');
            }
        });
        
        this.backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    /**
     * Création du bouton "Retour en haut"
     */
    createBackToTopButton() {
        this.backToTopButton = document.createElement('button');
        this.backToTopButton.className = 'back-to-top';
        this.backToTopButton.innerHTML = '↑';
        this.backToTopButton.setAttribute('aria-label', 'Retour en haut de page');
        document.body.appendChild(this.backToTopButton);
    }
    
    /**
     * Configuration des messages flash
     */
    setupFlashMessages() {
        const flashMessages = document.querySelectorAll('.flash-message');
        
        flashMessages.forEach(message => {
            // Auto-suppression après 5 secondes
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    message.remove();
                }, 300);
            }, 5000);
            
            // Fermeture manuelle
            const closeBtn = message.querySelector('.flash-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    message.style.opacity = '0';
                    message.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        message.remove();
                    }, 300);
                });
            }
        });
    }
    
    /**
     * Configuration du lazy loading
     */
    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }
    
    /**
     * Configuration des effets de scroll
     */
    setupScrollEffects() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);
        
        const animateElements = document.querySelectorAll('.animate-on-scroll');
        animateElements.forEach(el => observer.observe(el));
    }
    
    /**
     * Configuration de la validation des formulaires
     */
    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
            
            // Validation en temps réel
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
                
                input.addEventListener('input', () => {
                    if (input.classList.contains('is-invalid')) {
                        this.validateField(input);
                    }
                });
            });
        });
    }
    
    /**
     * Validation d'un formulaire
     */
    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * Validation d'un champ
     */
    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        const required = field.hasAttribute('required');
        
        // Suppression des messages d'erreur existants
        this.removeFieldError(field);
        
        // Validation required
        if (required && !value) {
            this.showFieldError(field, 'Ce champ est requis');
            return false;
        }
        
        // Validation email
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(field, 'Format d\'email invalide');
                return false;
            }
        }
        
        // Validation téléphone
        if (field.name === 'phone_number' && value) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
            if (!phoneRegex.test(value)) {
                this.showFieldError(field, 'Format de téléphone invalide');
                return false;
            }
        }
        
        // Validation code postal
        if (field.name === 'postal_code' && value) {
            const postalRegex = /^[0-9]{5}$/;
            if (!postalRegex.test(value)) {
                this.showFieldError(field, 'Code postal invalide');
                return false;
            }
        }
        
        // Validation prix
        if (field.name === 'sale_price' && value) {
            const price = parseFloat(value);
            if (isNaN(price) || price <= 0) {
                this.showFieldError(field, 'Prix invalide');
                return false;
            }
        }
        
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        return true;
    }
    
    /**
     * Affichage d'une erreur de champ
     */
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.color = 'var(--error-color)';
        errorDiv.style.fontSize = 'var(--font-size-sm)';
        errorDiv.style.marginTop = 'var(--spacing-xs)';
        
        field.parentNode.appendChild(errorDiv);
    }
    
    /**
     * Suppression d'une erreur de champ
     */
    removeFieldError(field) {
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    /**
     * Configuration de l'accessibilité
     */
    setupAccessibility() {
        // Gestion du focus visible
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });
        
        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });
        
        // Skip links
        const skipLinks = document.querySelectorAll('.skip-link');
        skipLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    target.focus();
                    target.scrollIntoView();
                }
            });
        });
        
        // ARIA live regions
        const liveRegions = document.querySelectorAll('[aria-live]');
        liveRegions.forEach(region => {
            region.addEventListener('DOMNodeInserted', () => {
                // Force la lecture par les lecteurs d'écran
                region.setAttribute('aria-live', 'polite');
            });
        });
    }
    
    /**
     * Méthode utilitaire pour debounce
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Méthode utilitaire pour throttle
     */
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Initialisation de l'application
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = App;
} 