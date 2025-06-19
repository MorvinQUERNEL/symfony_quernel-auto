document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si les éléments existent
    const carouselTrack = document.getElementById('carouselTrack');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const dots = document.querySelectorAll('.dot');
    const items = document.querySelectorAll('.carousel-item');
    
    // Si pas d'éléments, arrêter
    if (!carouselTrack || items.length === 0) {
        console.log('Carousel elements not found');
        return;
    }
    
    console.log('Carousel initialized with', items.length, 'items');
    
    let currentIndex = 0;
    
    // Calculer la largeur réelle des éléments
    function getItemWidth() {
        const firstItem = items[0];
        if (firstItem) {
            const itemStyle = window.getComputedStyle(firstItem);
            const itemWidth = firstItem.offsetWidth;
            const itemMargin = parseInt(itemStyle.marginLeft) + parseInt(itemStyle.marginRight);
            const itemGap = 60; // Gap défini dans le CSS
            return itemWidth + itemMargin + itemGap;
        }
        return 410; // Valeur par défaut
    }
    
    let itemWidth = getItemWidth();
    let visibleItems = Math.floor(carouselTrack.offsetWidth / itemWidth);
    let maxIndex = Math.max(0, items.length - visibleItems);
    
    console.log('Item width:', itemWidth, 'Visible items:', visibleItems, 'Max index:', maxIndex);
    
    // Initialisation
    updateCarousel();
    
    // Fonction pour mettre à jour le carrousel
    function updateCarousel() {
        console.log('Updating carousel, current index:', currentIndex);
        
        // Mettre à jour la position du track
        const translateX = -currentIndex * itemWidth;
        carouselTrack.style.transform = `translateX(${translateX}px)`;
        
        // Mettre à jour les classes active
        items.forEach((item, index) => {
            item.classList.remove('active');
            if (index >= currentIndex && index < currentIndex + visibleItems) {
                item.classList.add('active');
            }
        });
        
        // Mettre à jour les dots
        dots.forEach((dot, index) => {
            dot.classList.remove('active');
            if (index === currentIndex) {
                dot.classList.add('active');
            }
        });
        
        // Mettre à jour les boutons
        if (prevBtn) {
            prevBtn.disabled = currentIndex === 0;
            prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
        }
        if (nextBtn) {
            nextBtn.disabled = currentIndex >= maxIndex;
            nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
        }
    }
    
    // Événements des boutons
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Previous button clicked, current index:', currentIndex);
            if (currentIndex > 0) {
                currentIndex--;
                updateCarousel();
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Next button clicked, current index:', currentIndex);
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateCarousel();
            }
        });
    }
    
    // Événements des dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            console.log('Dot clicked:', index);
            currentIndex = Math.max(0, Math.min(index, maxIndex));
            updateCarousel();
        });
    });
    
    // Navigation au clavier
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' && currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        } else if (e.key === 'ArrowRight' && currentIndex < maxIndex) {
            currentIndex++;
            updateCarousel();
        }
    });
    
    // Gestion du redimensionnement
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            itemWidth = getItemWidth();
            visibleItems = Math.floor(carouselTrack.offsetWidth / itemWidth);
            maxIndex = Math.max(0, items.length - visibleItems);
            
            if (currentIndex > maxIndex) {
                currentIndex = maxIndex;
            }
            
            console.log('Resize - Item width:', itemWidth, 'Visible items:', visibleItems, 'Max index:', maxIndex);
            updateCarousel();
        }, 250);
    });
    
    // Debug: Afficher les informations dans la console
    console.log('Carousel setup complete');
    console.log('Track width:', carouselTrack.offsetWidth);
    console.log('Items:', items.length);
    console.log('Item width:', itemWidth);
    console.log('Visible items:', visibleItems);
    console.log('Max index:', maxIndex);
}); 