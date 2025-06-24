/**
 * COMBINED FORM - JavaScript pour le formulaire d'inscription et préférences
 * Quernel Auto
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Animation lors du focus sur les champs
    document.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

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