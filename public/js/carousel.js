/**
 * Carrousel de Véhicules Optimisé - Version 4.1.0
 * Optimisé pour tous les navigateurs modernes et téléphones
 * Support complet iOS Safari, Chrome Mobile, Firefox Mobile, Samsung Internet
 */

class VehiclesCarousel {
    constructor(container) {
        this.container = container;
        this.track = container.querySelector('.vehicles-track');
        this.cards = Array.from(container.querySelectorAll('.vehicle-card'));
        this.prevBtn = container.querySelector('.carousel-nav-prev');
        this.nextBtn = container.querySelector('.carousel-nav-next');
        this.indicators = container.querySelectorAll('.carousel-dot');
        
        // Configuration responsive
        this.breakpoints = {
            mobile: 757,
            desktop: 1024
        };
        
        // État du carrousel
        this.currentIndex = 0;
        this.itemsPerPage = this.getItemsPerPage();
        this.totalPages = Math.ceil(this.cards.length / this.itemsPerPage);
        
        // Support tactile amélioré
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.touchCurrentX = 0;
        this.isDragging = false;
        this.startTransform = 0;
        this.isScrolling = null;
        this.touchThreshold = 50;
        this.velocityThreshold = 0.3;
        
        // Debounce pour le resize
        this.resizeTimeout = null;
        this.rafId = null;
        
        // Support pour différents navigateurs
        this.supportsPassive = this.checkPassiveSupport();
        this.supportsTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        this.isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        
        this.init();
    }
    
    // Vérifier le support des événements passifs
    checkPassiveSupport() {
        let supportsPassive = false;
        try {
            const opts = Object.defineProperty({}, 'passive', {
                get: function() {
                    supportsPassive = true;
                    return false;
                }
            });
            window.addEventListener('testPassive', null, opts);
            window.removeEventListener('testPassive', null, opts);
        } catch (e) {
            // Pas de support
        }
        return supportsPassive;
    }
    
    init() {
        if (this.cards.length === 0) return;
        
        // Optimisation pour iOS
        if (this.isIOS) {
            this.track.style.webkitTransform = 'translateZ(0)';
            this.track.style.webkitBackfaceVisibility = 'hidden';
        }
        
        this.setupEventListeners();
        this.updateView();
        this.updateNavigation();
        
        // Mise à jour responsive avec debounce
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 150), false);
        
        // Optimisation pour orientation change sur mobile
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleResize();
            }, 100);
        }, false);
        
        console.log('🚗 Carrousel optimisé initialisé:', {
            véhicules: this.cards.length,
            plateforme: this.isIOS ? 'iOS' : (this.isSafari ? 'Safari' : 'Autre'),
            supportTactile: this.supportsTouch,
            supportPassif: this.supportsPassive
        });
    }
    
    getItemsPerPage() {
        const width = window.innerWidth;
        if (width >= this.breakpoints.desktop) return 3; // Desktop: 3 cartes
        if (width >= this.breakpoints.mobile) return 2;  // Tablette: 2 cartes
        return 1; // Mobile: 1 carte
    }
    
    setupEventListeners() {
        // Navigation par boutons
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.prev();
            }, false);
        }
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.next();
            }, false);
        }
        
        // Indicateurs
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', (e) => {
                e.preventDefault();
                this.goToPage(index);
            }, false);
        });
        
        // Support tactile optimisé
        if (this.supportsTouch) {
            const passiveOpts = this.supportsPassive ? { passive: false } : false;
            const passiveOptsTrue = this.supportsPassive ? { passive: true } : false;
            
            this.track.addEventListener('touchstart', (e) => this.handleTouchStart(e), passiveOptsTrue);
            this.track.addEventListener('touchmove', (e) => this.handleTouchMove(e), passiveOpts);
            this.track.addEventListener('touchend', (e) => this.handleTouchEnd(e), passiveOptsTrue);
            this.track.addEventListener('touchcancel', (e) => this.handleTouchEnd(e), passiveOptsTrue);
        }
        
        // Support souris pour desktop
        if (!this.supportsTouch || window.innerWidth >= this.breakpoints.desktop) {
            this.track.addEventListener('mousedown', (e) => this.handleMouseStart(e), false);
            this.track.addEventListener('mousemove', (e) => this.handleMouseMove(e), false);
            this.track.addEventListener('mouseup', (e) => this.handleMouseEnd(e), false);
            this.track.addEventListener('mouseleave', (e) => this.handleMouseEnd(e), false);
        }
        
        // Support clavier
        this.container.addEventListener('keydown', (e) => this.handleKeyboard(e), false);
        this.container.setAttribute('tabindex', '0');
        
        // Empêcher le drag des images
        this.cards.forEach(card => {
            const img = card.querySelector('img');
            if (img) {
                img.addEventListener('dragstart', (e) => e.preventDefault(), false);
                img.style.webkitUserSelect = 'none';
                img.style.userSelect = 'none';
            }
        });
    }
    
    // Gestion tactile optimisée
    handleTouchStart(e) {
        if (e.touches.length !== 1) return;
        
        const touch = e.touches[0];
        this.touchStartX = touch.clientX;
        this.touchStartY = touch.clientY;
        this.touchCurrentX = touch.clientX;
        this.isDragging = true;
        this.isScrolling = null;
        this.startTransform = this.getCurrentTransform();
        this.startTime = Date.now();
        
        // Arrêter les transitions
        this.track.style.transition = 'none';
        this.track.style.webkitTransition = 'none';
    }
    
    handleTouchMove(e) {
        if (!this.isDragging || e.touches.length !== 1) return;
        
        const touch = e.touches[0];
        this.touchCurrentX = touch.clientX;
        const deltaX = this.touchCurrentX - this.touchStartX;
        const deltaY = touch.clientY - this.touchStartY;
        
        // Déterminer la direction du geste
        if (this.isScrolling === null) {
            this.isScrolling = Math.abs(deltaY) > Math.abs(deltaX);
        }
        
        // Si c'est un scroll vertical, ne pas interférer
        if (this.isScrolling) {
            this.isDragging = false;
            this.resetTransition();
            return;
        }
        
        // Empêcher le scroll horizontal
        e.preventDefault();
        
        // Appliquer le déplacement avec résistance aux bords
        const displacement = this.calculateDisplacement(deltaX);
        this.setTransform(this.startTransform + displacement);
    }
    
    handleTouchEnd(e) {
        if (!this.isDragging) return;
        
        this.isDragging = false;
        this.resetTransition();
        
        const deltaX = this.touchCurrentX - this.touchStartX;
        const deltaTime = Date.now() - this.startTime;
        const velocity = Math.abs(deltaX) / deltaTime;
        
        // Déterminer l'action basée sur la distance et la vélocité
        if (Math.abs(deltaX) > this.touchThreshold || velocity > this.velocityThreshold) {
            if (deltaX > 0) {
                this.prev();
            } else {
                this.next();
            }
        } else {
            this.updateView();
        }
    }
    
    // Support souris pour desktop
    handleMouseStart(e) {
        if (this.supportsTouch) return;
        
        e.preventDefault();
        this.touchStartX = e.clientX;
        this.touchStartY = e.clientY;
        this.touchCurrentX = e.clientX;
        this.isDragging = true;
        this.startTransform = this.getCurrentTransform();
        this.startTime = Date.now();
        
        this.track.style.transition = 'none';
        this.track.style.webkitTransition = 'none';
        this.track.style.cursor = 'grabbing';
    }
    
    handleMouseMove(e) {
        if (!this.isDragging) return;
        
        e.preventDefault();
        this.touchCurrentX = e.clientX;
        const deltaX = this.touchCurrentX - this.touchStartX;
        
        const displacement = this.calculateDisplacement(deltaX);
        this.setTransform(this.startTransform + displacement);
    }
    
    handleMouseEnd(e) {
        if (!this.isDragging) return;
        
        this.isDragging = false;
        this.resetTransition();
        this.track.style.cursor = 'grab';
        
        const deltaX = this.touchCurrentX - this.touchStartX;
        const deltaTime = Date.now() - this.startTime;
        const velocity = Math.abs(deltaX) / deltaTime;
        
        if (Math.abs(deltaX) > this.touchThreshold || velocity > this.velocityThreshold) {
            if (deltaX > 0) {
                this.prev();
            } else {
                this.next();
            }
        } else {
            this.updateView();
        }
    }
    
    // Calcul du déplacement avec résistance
    calculateDisplacement(deltaX) {
        const maxTransform = this.getMaxTransform();
        const minTransform = 0;
        const currentTransform = this.startTransform + deltaX;
        
        // Résistance aux bords
        if (currentTransform > minTransform) {
            return minTransform - this.startTransform + (currentTransform - minTransform) * 0.2;
        } else if (currentTransform < maxTransform) {
            return maxTransform - this.startTransform + (currentTransform - maxTransform) * 0.2;
        }
        
        return deltaX;
    }
    
    // Réinitialiser les transitions
    resetTransition() {
        this.track.style.transition = 'transform 0.4s ease-out';
        this.track.style.webkitTransition = '-webkit-transform 0.4s ease-out';
    }
    
    // Définir la transformation de manière cross-browser
    setTransform(value) {
        this.track.style.transform = `translateX(${value}px)`;
        this.track.style.webkitTransform = `translateX(${value}px)`;
        this.track.style.msTransform = `translateX(${value}px)`;
    }
    
    handleKeyboard(e) {
        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                this.prev();
                break;
            case 'ArrowRight':
                e.preventDefault();
                this.next();
                break;
            case 'Home':
                e.preventDefault();
                this.goToPage(0);
                break;
            case 'End':
                e.preventDefault();
                this.goToPage(this.totalPages - 1);
                break;
        }
    }
    
    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.updateView();
            this.updateNavigation();
        }
    }
    
    next() {
        if (this.currentIndex < this.totalPages - 1) {
            this.currentIndex++;
            this.updateView();
            this.updateNavigation();
        }
    }
    
    goToPage(pageIndex) {
        if (pageIndex >= 0 && pageIndex < this.totalPages) {
            this.currentIndex = pageIndex;
            this.updateView();
            this.updateNavigation();
        }
    }
    
    updateView() {
        // Utiliser requestAnimationFrame pour des performances optimales
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }
        
        this.rafId = requestAnimationFrame(() => {
            const itemWidth = this.getItemWidth();
            const translateX = -this.currentIndex * itemWidth * this.itemsPerPage;
            
            this.setTransform(translateX);
            this.updateActiveCards();
        });
    }
    
    updateActiveCards() {
        this.cards.forEach((card, index) => {
            const isVisible = this.isCardVisible(index);
            card.classList.toggle('active', isVisible);
        });
    }
    
    isCardVisible(cardIndex) {
        const startIndex = this.currentIndex * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        return cardIndex >= startIndex && cardIndex < endIndex;
    }
    
    updateNavigation() {
        // Boutons
        if (this.prevBtn) {
            this.prevBtn.disabled = this.currentIndex === 0;
            this.prevBtn.setAttribute('aria-disabled', this.currentIndex === 0);
        }
        if (this.nextBtn) {
            this.nextBtn.disabled = this.currentIndex === this.totalPages - 1;
            this.nextBtn.setAttribute('aria-disabled', this.currentIndex === this.totalPages - 1);
        }
        
        // Indicateurs
        this.indicators.forEach((indicator, index) => {
            const isActive = index === this.currentIndex;
            indicator.classList.toggle('active', isActive);
            indicator.setAttribute('aria-selected', isActive);
        });

        this.updateProgressBar();
    }
    
    updateProgressBar() {
        const progressBar = this.container.querySelector('.carousel-progress-bar');
        if (!progressBar) return;
    
        const progress = this.totalPages > 1 ? ((this.currentIndex) / (this.totalPages - 1)) * 100 : 100;
        progressBar.style.width = `${progress}%`;
    }
    
    getItemWidth() {
        if (this.cards.length === 0) return 0;
        
        const card = this.cards[0];
        const cardStyle = getComputedStyle(card);
        const cardWidth = card.offsetWidth;
        const marginLeft = parseFloat(cardStyle.marginLeft) || 0;
        const marginRight = parseFloat(cardStyle.marginRight) || 0;
        
        return cardWidth + marginLeft + marginRight;
    }
    
    getCurrentTransform() {
        const style = getComputedStyle(this.track);
        const matrix = style.transform || style.webkitTransform || style.msTransform;
        
        if (matrix === 'none' || !matrix) return 0;
        
        const values = matrix.match(/matrix.*\((.+)\)/);
        if (!values) return 0;
        
        const matrixValues = values[1].split(', ');
        return parseFloat(matrixValues[4]) || 0;
    }
    
    getMaxTransform() {
        if (this.cards.length === 0) return 0;
        
        const containerWidth = this.track.parentElement.offsetWidth;
        const totalWidth = this.cards.length * this.getItemWidth();
        
        return Math.min(0, containerWidth - totalWidth);
    }
    
    handleResize() {
        const newItemsPerPage = this.getItemsPerPage();
        
        if (newItemsPerPage !== this.itemsPerPage) {
            this.itemsPerPage = newItemsPerPage;
            this.totalPages = Math.ceil(this.cards.length / this.itemsPerPage);
            
            // Ajuster l'index si nécessaire
            if (this.currentIndex >= this.totalPages) {
                this.currentIndex = Math.max(0, this.totalPages - 1);
            }
            
            this.updateView();
            this.updateNavigation();
        }
    }
    
    // Utilitaire debounce
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
}

// Auto-initialisation
document.addEventListener('DOMContentLoaded', function() {
    const carouselContainer = document.querySelector('.vehicles-carousel');
    
    if (carouselContainer) {
        try {
            window.vehiclesCarousel = new VehiclesCarousel(carouselContainer);
        } catch (error) {
            console.error('Erreur lors de l\'initialisation du carrousel:', error);
            // Fallback basique si le carrousel ne peut pas s'initialiser
            const track = carouselContainer.querySelector('.vehicles-track');
            if (track) {
                track.style.overflow = 'auto';
                track.style.scrollBehavior = 'smooth';
            }
        }
    }
});
