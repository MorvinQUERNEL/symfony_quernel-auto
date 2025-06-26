/**
 * PAYMENT - JavaScript pour la gestion des paiements Stripe et Apple Pay
 * Quernel Auto
 */

class PaymentManager {
    constructor(stripePublicKey) {
        this.stripe = Stripe(stripePublicKey);
        this.applePaySupported = false;
        this.init();
    }

    init() {
        this.checkApplePayAvailability();
        this.bindEvents();
    }

    // Vérifier si Apple Pay est disponible
    checkApplePayAvailability() {
        if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
            this.applePaySupported = true;
            // Afficher les boutons Apple Pay pour toutes les commandes
            document.querySelectorAll('.apple-pay-button').forEach(button => {
                button.style.display = 'block';
            });
        }
    }

    // Lier les événements
    bindEvents() {
        // Boutons Apple Pay
        document.querySelectorAll('.btn-apple-pay, .apple-pay-button').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const orderId = e.currentTarget.closest('[data-order-id]').dataset.orderId;
                if (orderId) {
                    this.initiateApplePay(orderId);
                }
            });
        });

        // Boutons de détails de commande
        document.querySelectorAll('.btn-details').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const orderId = e.currentTarget.closest('[data-order-id]').dataset.orderId;
                if (orderId) {
                    this.showOrderDetails(orderId);
                }
            });
        });
    }

    // Initialiser Apple Pay pour une commande spécifique
    initializeApplePayForOrder(orderId, amount, createPaymentIntentUrl) {
        const paymentRequest = this.stripe.paymentRequest({
            country: 'FR',
            currency: 'eur',
            total: {
                label: 'Quernel Auto - Commande #' + orderId,
                amount: amount, // en centimes
            },
            requestPayerName: true,
            requestPayerEmail: true,
            requestPayerPhone: true,
            requestShipping: false,
        });

        // Gérer la confirmation du paiement
        paymentRequest.on('paymentmethod', async (event) => {
            try {
                this.showLoading();
                
                // Créer le PaymentIntent
                const response = await fetch(createPaymentIntentUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'order_id=' + orderId
                });

                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }

                // Confirmer le paiement
                const {error: confirmError} = await this.stripe.confirmCardPayment(
                    data.client_secret,
                    {
                        payment_method: event.paymentMethod.id,
                    },
                    {
                        handleActions: false,
                    }
                );

                if (confirmError) {
                    throw new Error(confirmError.message);
                }

                // Paiement réussi - l'URL sera fournie par le template
                this.hideLoading();
                const successUrl = document.body.dataset.paymentSuccessUrl;
                window.location.href = successUrl + '?order_id=' + orderId;

            } catch (error) {
                console.error('Erreur Apple Pay:', error);
                this.hideLoading();
                this.showError('Erreur lors du paiement Apple Pay: ' + error.message);
            }
        });

        // Gérer les erreurs
        paymentRequest.on('cancel', () => {
            console.log('Paiement Apple Pay annulé pour la commande #' + orderId);
            this.hideLoading();
        });

        return paymentRequest;
    }

    // Fonction pour initier Apple Pay
    initiateApplePay(orderId) {
        const orderCard = document.querySelector(`[data-order-id="${orderId}"]`);
        const amount = parseInt(orderCard.dataset.orderAmount);
        const createPaymentIntentUrl = document.body.dataset.createPaymentIntentUrl;
        
        const paymentRequest = this.initializeApplePayForOrder(orderId, amount, createPaymentIntentUrl);
        
        if (paymentRequest) {
            paymentRequest.show();
        } else {
            this.showError('Apple Pay n\'est pas disponible sur votre appareil');
        }
    }

    // Fonction pour afficher le loading
    showLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }
    }

    // Fonction pour masquer le loading
    hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    // Fonction pour afficher les erreurs
    showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.payment-container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        }
    }

    // Afficher les détails de commande
    showOrderDetails(orderId) {
        const modalContent = document.getElementById('orderDetailsContent');
        if (modalContent) {
            modalContent.innerHTML = '<p>Détails de la commande #' + orderId + ' en cours de chargement...</p>';
        }
        
        const modal = document.getElementById('orderDetailsModal');
        if (modal && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const stripePublicKey = document.body.dataset.stripePublicKey;
    if (stripePublicKey) {
        new PaymentManager(stripePublicKey);
    }
}); 