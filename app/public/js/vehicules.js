/**
 * VEHICULES - JavaScript pour la gestion des véhicules
 * Quernel Auto
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');

    if (searchForm && searchInput) {
        // Gestion de la soumission du formulaire (clic sur la loupe ou touche Entrée)
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm) {
                const url = new URL(window.location);
                url.searchParams.set('search', searchTerm);
                window.location.href = url.toString();
            }
        });

        // Recherche quand on appuie sur Entrée dans le champ
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Gestion des confirmations de suppression
    const deleteButtons = document.querySelectorAll('form[onsubmit*="confirm"] button[type="submit"]');
    deleteButtons.forEach(button => {
        const form = button.closest('form');
        if (form) {
            // Supprimer l'attribut onsubmit du formulaire
            form.removeAttribute('onsubmit');
            
            // Ajouter l'événement avec JavaScript
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ? Cette action est irréversible.')) {
                    form.submit();
                }
            });
        }
    });
}); 