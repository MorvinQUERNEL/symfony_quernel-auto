document.addEventListener('DOMContentLoaded', () => {
    // Navigation burger menu
    const burger = document.querySelector('.navbar-burger');
    const menu = document.querySelector('.navbar-menu');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Toggle menu
    burger.addEventListener('click', () => {
        burger.classList.toggle('is-active');
        menu.classList.toggle('is-active');
        
        // Empêcher le scroll du body quand le menu est ouvert
        document.body.style.overflow = menu.classList.contains('is-active') ? 'hidden' : '';
    });
    
    // Fermer le menu quand on clique sur un lien
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            burger.classList.remove('is-active');
            menu.classList.remove('is-active');
            document.body.style.overflow = '';
        });
    });
    
    // Fermer le menu quand on clique en dehors
    document.addEventListener('click', (e) => {
        if (!burger.contains(e.target) && !menu.contains(e.target)) {
            burger.classList.remove('is-active');
            menu.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    });
    
    // Fermer le menu avec la touche Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('is-active')) {
            burger.classList.remove('is-active');
            menu.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    });
    
    // Gestion du redimensionnement de la fenêtre
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            burger.classList.remove('is-active');
            menu.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    });
}); 