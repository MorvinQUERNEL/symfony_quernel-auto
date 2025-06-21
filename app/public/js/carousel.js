/**
 * CARROUSEL - QUERNEL AUTO
 * Carrousel moderne avec animations fluides et effets visuels
 * @author Quernel Auto
 * @version 2.0.0
 */

class Carousel {
    constructor(container, options = {}) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.options = {
            autoplay: options.autoplay || false,
            autoplaySpeed: options.autoplaySpeed || 5000,
            infinite: options.infinite || true,
            slidesToShow: options.slidesToShow || 1,
            slidesToScroll: options.slidesToScroll || 1,
            dots: options.dots !== false,
            arrows: options.arrows !== false,
            responsive: options.responsive || {},
            ...options
        };
        
        this.currentIndex = 0;
        this.isAnimating = false;
        this.autoplayTimer = null;
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.isDragging = false;
        this.dragStartX = 0;
        this.dragOffset = 0;
        
        this.track = null;
        this.items = [];
        this.dots = [];
        this.prevBtn = null;
        this.nextBtn = null;
        this.progressBar = null;
        
        this.init();
    }
    
    init() {
        if (!this.container) {
            console.error('Carousel: Container not found');
            return;
        }
        
        this.setupElements();
        this.setupResponsive();
        this.setupEventListeners();
        this.setupAutoplay();
        this.updateCarousel();
        this.setupIntersectionObserver();
        
        console.log('Carousel initialized with', this.items.length, 'items');
    }
    
    /**
     * Configuration des éléments du carrousel
     */
    setupElements() {
        this.track = this.container.querySelector('.carousel-track');
        this.items = Array.from(this.container.querySelectorAll('.carousel-item'));
        this.dots = Array.from(this.container.querySelectorAll('.carousel-dot'));
        this.prevBtn = this.container.querySelector('.carousel-nav-prev');
        this.nextBtn = this.container.querySelector('.carousel-nav-next');
        this.progressBar = this.container.querySelector('.carousel-progress-bar');
        
        if (!this.track || this.items.length === 0) {
            console.error('Carousel: Required elements not found');
            return;
        }
        
        // Ajout des attributs ARIA pour l'accessibilité
        this.container.setAttribute('role', 'region');
        this.container.setAttribute('aria-label', 'Carrousel de véhicules');
        
        this.items.forEach((item, index) => {
            item.setAttribute('role', 'group');
            item.setAttribute('aria-label', `Slide ${index + 1} sur ${this.items.length}`);
        });
    }
    
    /**
     * Configuration responsive
     */
    setupResponsive() {
        this.updateSlidesToShow();
        
        window.addEventListener('resize', this.debounce(() => {
            this.updateSlidesToShow();
            this.updateCarousel();
        }, 250));
    }
    
    /**
     * Mise à jour du nombre de slides visibles selon la taille d'écran
     */
    updateSlidesToShow() {
        const width = window.innerWidth;
        
        if (width >= 1024) {
            this.options.slidesToShow = 3;
        } else if (width >= 768) {
            this.options.slidesToShow = 2;
        } else {
            this.options.slidesToShow = 1;
        }
        
        this.maxIndex = Math.max(0, this.items.length - this.options.slidesToShow);
    }
    
    /**
     * Configuration des écouteurs d'événements
     */
    setupEventListeners() {
        // Boutons de navigation
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.prev();
            });
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.next();
            });
        }
        
        // Navigation par dots
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                this.goTo(index);
            });
        });
        
        // Navigation au clavier
        this.container.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.prev();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                this.next();
            } else if (e.key === 'Home') {
                e.preventDefault();
                this.goTo(0);
            } else if (e.key === 'End') {
                e.preventDefault();
                this.goTo(this.maxIndex);
            }
        });
        
        // Gestion tactile
        this.setupTouchEvents();
        
        // Gestion de la souris
        this.setupMouseEvents();
        
        // Pause autoplay au hover
        if (this.options.autoplay) {
            this.container.addEventListener('mouseenter', () => this.pauseAutoplay());
            this.container.addEventListener('mouseleave', () => this.resumeAutoplay());
        }
    }
    
    /**
     * Configuration des événements tactiles
     */
    setupTouchEvents() {
        if (!('ontouchstart' in window)) return;
        
        this.container.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
            this.isDragging = false;
        });
        
        this.container.addEventListener('touchmove', (e) => {
            if (!this.touchStartX) return;
            
            const touchX = e.touches[0].clientX;
            const touchY = e.touches[0].clientY;
            const diffX = this.touchStartX - touchX;
            const diffY = this.touchStartY - touchY;
            
            if (!this.isDragging && Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                this.isDragging = true;
                this.dragStartX = touchX;
                this.dragOffset = 0;
            }
            
            if (this.isDragging) {
                e.preventDefault();
                this.dragOffset = diffX;
                this.updateDragPosition();
            }
        });
        
        this.container.addEventListener('touchend', () => {
            if (this.isDragging) {
                this.handleDragEnd();
            }
            this.touchStartX = 0;
            this.touchStartY = 0;
            this.isDragging = false;
        });
    }
    
    /**
     * Configuration des événements de souris
     */
    setupMouseEvents() {
        let isMouseDown = false;
        let startX = 0;
        let startOffset = 0;
        
        this.container.addEventListener('mousedown', (e) => {
            isMouseDown = true;
            startX = e.clientX;
            startOffset = 0;
            this.container.style.cursor = 'grabbing';
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isMouseDown) return;
            
            const diffX = startX - e.clientX;
            startOffset = diffX;
            this.updateDragPosition();
        });
        
        document.addEventListener('mouseup', () => {
            if (isMouseDown) {
                this.handleDragEnd();
                isMouseDown = false;
                this.container.style.cursor = '';
            }
        });
    }
    
    /**
     * Mise à jour de la position pendant le drag
     */
    updateDragPosition() {
        if (!this.track) return;
        
        const itemWidth = this.getItemWidth();
        const currentOffset = this.currentIndex * itemWidth;
        const dragOffset = this.dragOffset || 0;
        
        this.track.style.transform = `translateX(${-(currentOffset - dragOffset)}px)`;
    }
    
    /**
     * Gestion de la fin du drag
     */
    handleDragEnd() {
        const itemWidth = this.getItemWidth();
        const threshold = itemWidth * 0.3;
        
        if (Math.abs(this.dragOffset) > threshold) {
            if (this.dragOffset > 0) {
                this.next();
            } else {
                this.prev();
            }
        } else {
            this.updateCarousel();
        }
        
        this.dragOffset = 0;
    }
    
    /**
     * Configuration de l'autoplay
     */
    setupAutoplay() {
        if (!this.options.autoplay) return;
        
        this.startAutoplay();
    }
    
    /**
     * Démarrage de l'autoplay
     */
    startAutoplay() {
        if (this.autoplayTimer) return;
        
        this.autoplayTimer = setInterval(() => {
            if (!this.isAnimating) {
                this.next();
            }
        }, this.options.autoplaySpeed);
    }
    
    /**
     * Pause de l'autoplay
     */
    pauseAutoplay() {
        if (this.autoplayTimer) {
            clearInterval(this.autoplayTimer);
            this.autoplayTimer = null;
        }
    }
    
    /**
     * Reprise de l'autoplay
     */
    resumeAutoplay() {
        if (this.options.autoplay && !this.autoplayTimer) {
            this.startAutoplay();
        }
    }
    
    /**
     * Configuration de l'Intersection Observer
     */
    setupIntersectionObserver() {
        if (!('IntersectionObserver' in window)) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.resumeAutoplay();
                } else {
                    this.pauseAutoplay();
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(this.container);
    }
    
    /**
     * Mise à jour du carrousel
     */
    updateCarousel() {
        if (!this.track || this.isAnimating) return;
        
        this.isAnimating = true;
        
        const itemWidth = this.getItemWidth();
        const translateX = -this.currentIndex * itemWidth;
        
        // Animation fluide
        this.track.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        this.track.style.transform = `translateX(${translateX}px)`;
        
        // Mise à jour des classes active
        this.updateActiveItems();
        
        // Mise à jour des dots
        this.updateDots();
        
        // Mise à jour des boutons
        this.updateButtons();
        
        // Mise à jour de la barre de progression
        this.updateProgressBar();
        
        // Fin de l'animation
        setTimeout(() => {
            this.isAnimating = false;
        }, 600);
    }
    
    /**
     * Mise à jour des items actifs
     */
    updateActiveItems() {
        this.items.forEach((item, index) => {
            item.classList.remove('active');
            
            if (index >= this.currentIndex && index < this.currentIndex + this.options.slidesToShow) {
                item.classList.add('active');
            }
        });
    }
    
    /**
     * Mise à jour des dots
     */
    updateDots() {
        this.dots.forEach((dot, index) => {
            dot.classList.remove('active');
            dot.setAttribute('aria-current', 'false');
            
            if (index === this.currentIndex) {
                dot.classList.add('active');
                dot.setAttribute('aria-current', 'true');
            }
        });
    }
    
    /**
     * Mise à jour des boutons
     */
    updateButtons() {
        if (this.prevBtn) {
            const isDisabled = this.currentIndex === 0 && !this.options.infinite;
            this.prevBtn.disabled = isDisabled;
            this.prevBtn.setAttribute('aria-disabled', isDisabled);
        }
        
        if (this.nextBtn) {
            const isDisabled = this.currentIndex >= this.maxIndex && !this.options.infinite;
            this.nextBtn.disabled = isDisabled;
            this.nextBtn.setAttribute('aria-disabled', isDisabled);
        }
    }
    
    /**
     * Mise à jour de la barre de progression
     */
    updateProgressBar() {
        if (!this.progressBar) return;
        
        const progress = (this.currentIndex / this.maxIndex) * 100;
        this.progressBar.style.width = `${progress}%`;
    }
    
    /**
     * Calcul de la largeur d'un item
     */
    getItemWidth() {
        if (this.items.length === 0) return 0;
        
        const firstItem = this.items[0];
        const itemStyle = window.getComputedStyle(firstItem);
        const itemWidth = firstItem.offsetWidth;
        const itemMargin = parseInt(itemStyle.marginLeft) + parseInt(itemStyle.marginRight);
        const gap = 24; // Gap défini dans le CSS
        
        return itemWidth + itemMargin + gap;
    }
    
    /**
     * Navigation vers le slide précédent
     */
    prev() {
        if (this.isAnimating) return;
        
        if (this.currentIndex > 0) {
            this.currentIndex--;
        } else if (this.options.infinite) {
            this.currentIndex = this.maxIndex;
        } else {
            return;
        }
        
        this.updateCarousel();
    }
    
    /**
     * Navigation vers le slide suivant
     */
    next() {
        if (this.isAnimating) return;
        
        if (this.currentIndex < this.maxIndex) {
            this.currentIndex++;
        } else if (this.options.infinite) {
            this.currentIndex = 0;
        } else {
            return;
        }
        
        this.updateCarousel();
    }
    
    /**
     * Navigation vers un slide spécifique
     */
    goTo(index) {
        if (this.isAnimating) return;
        
        const targetIndex = Math.max(0, Math.min(index, this.maxIndex));
        
        if (targetIndex === this.currentIndex) return;
        
        this.currentIndex = targetIndex;
        this.updateCarousel();
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
     * Destruction du carrousel
     */
    destroy() {
        this.pauseAutoplay();
        
        if (this.track) {
            this.track.style.transition = '';
            this.track.style.transform = '';
        }
        
        this.items.forEach(item => {
            item.classList.remove('active');
        });
        
        this.dots.forEach(dot => {
            dot.classList.remove('active');
        });
        
        // Suppression des écouteurs d'événements
        this.container.removeEventListener('keydown', this.handleKeydown);
        this.container.removeEventListener('mouseenter', this.pauseAutoplay);
        this.container.removeEventListener('mouseleave', this.resumeAutoplay);
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    const carousels = document.querySelectorAll('.carousel-container');
    
    carousels.forEach((container, index) => {
        const carousel = new Carousel(container, {
            autoplay: true,
            autoplaySpeed: 5000,
            infinite: true,
            dots: true,
            arrows: true
        });
        
        // Stockage de l'instance pour accès externe
        container.carouselInstance = carousel;
    });
});

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Carousel;
}

// Rendre la classe Carousel disponible globalement
window.Carousel = Carousel; 