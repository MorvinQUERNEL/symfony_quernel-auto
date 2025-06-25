document.addEventListener('DOMContentLoaded', () => {
    const mainImage = document.getElementById('main-image');
    const gallery = document.getElementById('thumbnail-gallery');

    if (!mainImage || !gallery) {
        return;
    }

    gallery.addEventListener('click', (event) => {
        // S'assurer que l'on a cliqué sur un thumbnail-item ou son contenu
        const thumbnail = event.target.closest('.thumbnail-item');
        if (!thumbnail) {
            return;
        }
        
        // Récupérer l'URL de la nouvelle image
        const newImageUrl = thumbnail.dataset.imageUrl;
        if (!newImageUrl) {
            return;
        }

        // Précharger la nouvelle image pour une transition fluide
        const tempImage = new Image();
        tempImage.src = newImageUrl;
        tempImage.onload = () => {
            // Appliquer l'effet de fondu
            mainImage.style.opacity = '0';
            
            setTimeout(() => {
                // Changer la source et réapparaître
                mainImage.src = newImageUrl;
                mainImage.style.opacity = '1';
            }, 200); // Doit correspondre à la durée de la transition CSS
        };

        // Mettre à jour la classe 'active'
        const currentActive = gallery.querySelector('.thumbnail-item.active');
        if (currentActive) {
            currentActive.classList.remove('active');
        }
        thumbnail.classList.add('active');
    });

    // Ajouter une transition CSS à l'image principale
    mainImage.style.transition = 'opacity 0.2s ease-in-out';
}); 