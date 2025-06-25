/**
 * Nouveau Carrousel de Véhicules - Version 4.0.0
 * Carrousel moderne avec support tactile et navigation responsive
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
        
        // Support tactile
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.isDragging = false;
        this.startTransform = 0;
        
        this.init();
    }
    
    init() {
        if (this.cards.length === 0) return;
        
        this.setupEventListeners();
        this.updateView();
        this.updateNavigation();
        
        // Mise à jour responsive
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        console.log('🚗 Carrousel de véhicules initialisé avec', this.cards.length, 'véhicules');
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
            this.prevBtn.addEventListener('click', () => this.prev());
        }
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.next());
        }
        
        // Indicateurs
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => this.goToPage(index));
        });
        
        // Support tactile
        this.track.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: false });
        this.track.addEventListener('touchmove', (e) => this.handleTouchMove(e), { passive: false });
        this.track.addEventListener('touchend', (e) => this.handleTouchEnd(e));
        
        // Support clavier
        this.container.addEventListener('keydown', (e) => this.handleKeyboard(e));
        
        // Rendre le container focusable pour le clavier
        this.container.setAttribute('tabindex', '0');
    }
    
    handleTouchStart(e) {
        this.touchStartX = e.touches[0].clientX;
        this.touchStartY = e.touches[0].clientY;
        this.isDragging = true;
        this.startTransform = this.getCurrentTransform();
        
        // Arrêter les transitions pendant le drag
        this.track.style.transition = 'none';
    }
    
    handleTouchMove(e) {
        if (!this.isDragging) return;
        
        const touchX = e.touches[0].clientX;
        const touchY = e.touches[0].clientY;
        const deltaX = touchX - this.touchStartX;
        const deltaY = touchY - this.touchStartY;
        
        // Détecter si c'est un swipe horizontal ou un scroll vertical
        if (Math.abs(deltaY) > Math.abs(deltaX)) {
            // C'est un scroll vertical, on laisse faire
            this.isDragging = false;
            this.track.style.transition = '';
            return;
        }
        
        // C'est un swipe horizontal, on empêche le scroll
        e.preventDefault();
        
        // Appliquer le déplacement en temps réel
        const newTransform = this.startTransform + deltaX;
        this.track.style.transform = `translateX(${newTransform}px)`;
    }
    
    handleTouchEnd(e) {
        if (!this.isDragging) return;
        
        this.isDragging = false;
        this.track.style.transition = '';
        
        const touchEndX = e.changedTouches[0].clientX;
        const deltaX = touchEndX - this.touchStartX;
        const threshold = 50; // Seuil de déclenchement
        
        if (Math.abs(deltaX) > threshold) {
            if (deltaX > 0) {
                this.prev();
            } else {
                this.next();
            }
        } else {
            // Retour à la position originale
            this.updateView();
        }
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
        const itemWidth = this.getItemWidth();
        const translateX = -this.currentIndex * itemWidth * this.itemsPerPage;
        
        this.track.style.transform = `translateX(${translateX}px)`;
        
        // Mettre à jour les classes actives
        this.updateActiveCards();
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
        }
        if (this.nextBtn) {
            this.nextBtn.disabled = this.currentIndex === this.totalPages - 1;
        }
        
        // Indicateurs
        this.indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === this.currentIndex);
        });
    }
    
    getItemWidth() {
        if (this.cards.length === 0) return 0;
        const cardStyle = getComputedStyle(this.cards[0]);
        const cardWidth = this.cards[0].offsetWidth;
        const marginLeft = parseFloat(cardStyle.marginLeft) || 0;
        const marginRight = parseFloat(cardStyle.marginRight) || 0;
        return cardWidth + marginLeft + marginRight;
    }
    
    getCurrentTransform() {
        const style = getComputedStyle(this.track);
        const matrix = style.transform;
        if (matrix === 'none') return 0;
        
        const values = matrix.match(/matrix.*\((.+)\)/)[1].split(', ');
        return parseFloat(values[4]) || 0;
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
    
    // Méthodes publiques pour contrôle externe
    destroy() {
        window.removeEventListener('resize', this.handleResize);
        // Nettoyer les autres event listeners si nécessaire
    }
    
    reset() {
        this.currentIndex = 0;
        this.updateView();
        this.updateNavigation();
    }
    
    getState() {
        return {
            currentIndex: this.currentIndex,
            totalPages: this.totalPages,
            itemsPerPage: this.itemsPerPage,
            totalCards: this.cards.length
        };
    }
}

// Auto-initialisation
document.addEventListener('DOMContentLoaded', function() {
    const carouselContainer = document.querySelector('.vehicles-carousel');
    
    if (carouselContainer) {
        window.vehiclesCarousel = new VehiclesCarousel(carouselContainer);
    }
});

// Export pour utilisation modulaire si nécessaire
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VehiclesCarousel;
} 