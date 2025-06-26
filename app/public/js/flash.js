/**
 * Gestion des messages flash avec animations fluides
 * Quernel Auto - Notifications animées
 */

class FlashMessageManager {
    constructor() {
        this.messages = new Map();
        this.init();
    }

    init() {
        // Initialiser tous les messages flash existants
        document.querySelectorAll('.flash-message').forEach(message => {
            this.setupMessage(message);
        });

        // Observer pour les nouveaux messages ajoutés dynamiquement
        this.observeNewMessages();
    }

    setupMessage(message) {
        const messageId = this.generateId();
        message.dataset.flashId = messageId;

        // Configuration du message
        const config = {
            element: message,
            autoClose: !message.classList.contains('flash-no-auto-dismiss'),
            duration: parseInt(message.dataset.duration) || 5000,
            timeoutId: null
        };

        this.messages.set(messageId, config);

        // Ajouter les écouteurs d'événements
        this.addEventListeners(message, messageId);

        // Démarrer l'auto-fermeture si activée
        if (config.autoClose) {
            this.startAutoClose(messageId);
        }

        // Animation d'entrée
        this.animateIn(message);
    }

    addEventListeners(message, messageId) {
        // Bouton de fermeture
        const closeBtn = message.querySelector('.flash-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeMessage(messageId);
            });
        }

        // Pause de l'auto-fermeture au survol
        message.addEventListener('mouseenter', () => {
            this.pauseAutoClose(messageId);
        });

        message.addEventListener('mouseleave', () => {
            const config = this.messages.get(messageId);
            if (config && config.autoClose) {
                this.startAutoClose(messageId);
            }
        });

        // Fermeture au clic sur le message (mobile)
        if ('ontouchstart' in window) {
            message.addEventListener('click', (e) => {
                if (e.target === message || e.target.closest('.flash-content')) {
                    this.closeMessage(messageId);
                }
            });
        }

        // Accessibilité - fermeture avec Escape
        message.setAttribute('tabindex', '0');
        message.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeMessage(messageId);
            }
        });
    }

    animateIn(message) {
        // Force le reflow pour l'animation
        message.offsetHeight;
        
        // Annoncer le message aux lecteurs d'écran
        this.announceMessage(message);
    }

    closeMessage(messageId) {
        const config = this.messages.get(messageId);
        if (!config) return;

        const message = config.element;
        
        // Annuler l'auto-fermeture
        this.pauseAutoClose(messageId);

        // Ajouter la classe pour l'animation de sortie
        message.classList.add('flash-hiding');

        // Attendre la fin de l'animation avant de supprimer
        message.addEventListener('animationend', () => {
            this.removeMessage(messageId);
        }, { once: true });

        // Fallback si l'animation ne se déclenche pas
        setTimeout(() => {
            this.removeMessage(messageId);
        }, 500);
    }

    removeMessage(messageId) {
        const config = this.messages.get(messageId);
        if (!config) return;

        // Supprimer le message du DOM
        config.element.remove();
        
        // Nettoyer la Map
        this.messages.delete(messageId);

        // Réorganiser les messages restants
        // this.reorganizeMessages();
    }

    startAutoClose(messageId) {
        const config = this.messages.get(messageId);
        if (!config || !config.autoClose) return;

        // Annuler tout timeout existant
        this.pauseAutoClose(messageId);

        // Démarrer le nouveau timeout
        config.timeoutId = setTimeout(() => {
            this.closeMessage(messageId);
        }, config.duration);
    }

    pauseAutoClose(messageId) {
        const config = this.messages.get(messageId);
        if (!config || !config.timeoutId) return;

        clearTimeout(config.timeoutId);
        config.timeoutId = null;
    }

    // ... existing code ...

    announceMessage(message) {
        // Créer une annonce pour les lecteurs d'écran
        const announcement = document.createElement('div');
        announcement.className = 'sr-only';
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', 'polite');
        
        const text = message.querySelector('.flash-content span')?.textContent || '';
        announcement.textContent = text;
        
        document.body.appendChild(announcement);
        
        // Supprimer après l'annonce
        setTimeout(() => {
            announcement.remove();
        }, 1000);
    }

    observeNewMessages() {
        const container = document.querySelector('.flash-messages');
        if (!container) return;

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                    if (node.nodeType === 1 && node.classList.contains('flash-message')) {
                        this.setupMessage(node);
                    }
                });
            });
        });

        observer.observe(container, {
            childList: true,
            subtree: true
        });
    }

    generateId() {
        return `flash-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }

    // Méthode publique pour ajouter un nouveau message
    static addMessage(type, text, options = {}) {
        const container = document.querySelector('.flash-messages');
        if (!container) return;

        const message = document.createElement('div');
        message.className = `flash-message flash-${type}`;
        
        if (options.noAutoClose) {
            message.classList.add('flash-no-auto-dismiss');
        }
        
        if (options.duration) {
            message.dataset.duration = options.duration;
        }

        const iconMap = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };

        message.innerHTML = `
            <div class="flash-content">
                <i class="fas fa-${iconMap[type] || 'info-circle'}"></i>
                <span>${text}</span>
            </div>
            <button class="flash-close" aria-label="Fermer le message">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(message);
        
        // Le gestionnaire s'occupera automatiquement du nouveau message
        return message;
    }
}

// Classe pour les lecteurs d'écran uniquement
const style = document.createElement('style');
style.textContent = `
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        white-space: nowrap;
        border: 0;
    }
`;
document.head.appendChild(style);

// Initialiser le gestionnaire quand le DOM est prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.flashManager = new FlashMessageManager();
    });
} else {
    window.flashManager = new FlashMessageManager();
}

// Exposer la méthode pour ajouter des messages
window.FlashMessage = {
    success: (text, options) => FlashMessageManager.addMessage('success', text, options),
    error: (text, options) => FlashMessageManager.addMessage('error', text, options),
    warning: (text, options) => FlashMessageManager.addMessage('warning', text, options),
    info: (text, options) => FlashMessageManager.addMessage('info', text, options)
}; 