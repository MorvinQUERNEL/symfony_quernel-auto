/**
 * COMBINED FORM - JavaScript pour le formulaire d'inscription et préférences
 * Quernel Auto
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Validation en temps réel
    document.querySelectorAll('.form-control[required], .form-select[required]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
    
}); 