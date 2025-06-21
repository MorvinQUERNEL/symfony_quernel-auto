/**
 * Script pour la page Processus
 * Ajoute des animations au scroll et améliore l'expérience utilisateur
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Animation des étapes au scroll
    const observerOptions = {
        threshold: 0.3,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const stepObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                
                // Ajouter une classe pour les animations CSS
                entry.target.classList.add('step-visible');
            }
        });
    }, observerOptions);
    
    // Observer tous les éléments d'étape
    const stepItems = document.querySelectorAll('.step-item');
    stepItems.forEach(item => {
        stepObserver.observe(item);
    });
    
    // Animation des numéros d'étape
    const numberObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const number = entry.target;
                number.style.transform = 'scale(1.1)';
                number.style.background = 'linear-gradient(135deg, #FF6B00 0%, #ff8533 100%)';
                
                setTimeout(() => {
                    number.style.transform = 'scale(1)';
                }, 200);
            }
        });
    }, { threshold: 0.5 });
    
    // Observer tous les numéros d'étape
    const stepNumbers = document.querySelectorAll('.step-number');
    stepNumbers.forEach(number => {
        numberObserver.observe(number);
    });
    
    // Animation des images au hover
    const stepImages = document.querySelectorAll('.step-img');
    stepImages.forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Animation des boutons CTA
    const ctaButtons = document.querySelectorAll('.cta-actions .btn');
    ctaButtons.forEach((button, index) => {
        button.style.animationDelay = `${index * 0.1}s`;
        button.classList.add('btn-animate');
    });
    
    // Smooth scroll pour les ancres (si présentes)
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Animation du titre principal
    const mainTitle = document.querySelector('.processus-title');
    if (mainTitle) {
        mainTitle.style.opacity = '0';
        mainTitle.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            mainTitle.style.transition = 'all 0.8s ease-out';
            mainTitle.style.opacity = '1';
            mainTitle.style.transform = 'translateY(0)';
        }, 100);
    }
    
    // Animation du sous-titre
    const subtitle = document.querySelector('.processus-subtitle');
    if (subtitle) {
        subtitle.style.opacity = '0';
        subtitle.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            subtitle.style.transition = 'all 0.8s ease-out 0.2s';
            subtitle.style.opacity = '1';
            subtitle.style.transform = 'translateY(0)';
        }, 300);
    }
    
    // Effet de parallaxe léger sur l'en-tête
    const header = document.querySelector('.processus-header');
    if (header) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            header.style.transform = `translateY(${rate}px)`;
        });
    }
    
    // Animation des features au hover
    const featureItems = document.querySelectorAll('.step-features li');
    featureItems.forEach((item, index) => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(10px)';
            this.style.color = '#FF6B00';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
            this.style.color = '#495057';
        });
    });
    
    // Amélioration de l'accessibilité
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    if (prefersReducedMotion) {
        // Désactiver les animations pour les utilisateurs qui préfèrent moins de mouvement
        document.body.classList.add('reduced-motion');
    }
    
    // Gestion des erreurs d'images
    const images = document.querySelectorAll('.step-img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.style.display = 'none';
            const stepImage = this.closest('.step-image');
            if (stepImage) {
                stepImage.innerHTML = '<div class="image-placeholder"><i class="fas fa-image"></i><p>Image non disponible</p></div>';
            }
        });
    });
});

// Styles CSS additionnels pour les animations
const additionalStyles = `
    .step-visible {
        animation: none !important;
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
    
    .btn-animate {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    .image-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 300px;
        background: #f8f9fa;
        color: #6c757d;
        border-radius: 8px;
    }
    
    .image-placeholder i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .reduced-motion * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
`;

// Injecter les styles additionnels
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet); 