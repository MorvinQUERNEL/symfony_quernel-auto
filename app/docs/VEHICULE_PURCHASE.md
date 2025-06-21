# Système d'Achat de Véhicules

Ce document explique le système d'achat de véhicules via Stripe dans l'application Quernel Auto.

## Fonctionnalités

### ✅ **Fonctionnalités implémentées :**

1. **Bouton d'achat** sur la page de détail du véhicule
2. **Page d'achat dédiée** avec toutes les informations du véhicule
3. **Intégration Stripe** pour le paiement sécurisé
4. **Gestion des commandes** automatique
5. **Pages de succès et d'annulation**
6. **Webhooks** pour traiter les paiements
7. **Mise à jour du statut** des véhicules (disponible → vendu)

## Flux d'achat

### 1. **Sélection du véhicule**
- L'utilisateur consulte la liste des véhicules
- Il clique sur un véhicule pour voir les détails
- Si le véhicule est disponible, un bouton "Acheter ce véhicule" apparaît

### 2. **Page d'achat**
- URL : `/vehicules/{id}/buy`
- Affichage des détails du véhicule
- Prix en euros (formatage automatique)
- Bouton d'achat avec intégration Stripe

### 3. **Processus de paiement**
- Création d'une session Stripe Checkout
- Redirection vers la page de paiement Stripe
- Paiement sécurisé par carte bancaire
- Retour vers l'application après paiement

### 4. **Traitement du paiement**
- **Succès** : Redirection vers `/vehicules/purchase/success`
- **Annulation** : Redirection vers `/vehicules/purchase/cancel`
- **Webhook** : Traitement automatique en arrière-plan

## Routes disponibles

### Routes d'achat
- `GET /vehicules/{id}/buy` - Page d'achat
- `POST /vehicules/{id}/create-purchase-session` - Création session Stripe
- `GET /vehicules/purchase/success` - Page de succès
- `GET /vehicules/purchase/cancel` - Page d'annulation

### Routes webhook
- `POST /webhook/stripe` - Traitement des événements Stripe

## Entités impliquées

### Vehicules
- **Statut** : `available` → `sold`
- **Prix** : `salePrice` (en centimes)
- **Images** : Affichage dans la page d'achat

### Orders (Commandes)
- **Création automatique** lors de l'achat
- **Statut** : `pending` → `paid`
- **Prix total** : Copié depuis le véhicule
- **Adresse de livraison** : Depuis le profil utilisateur

### Payement
- **Création automatique** après paiement réussi
- **Statut** : `completed`
- **Montant** : Copié depuis la commande

## Sécurité

### Vérifications
- ✅ Utilisateur connecté requis
- ✅ Statut du véhicule vérifié (disponible uniquement)
- ✅ Vérification de signature Stripe
- ✅ Gestion des erreurs complète

### Logs
- Tous les événements sont loggés
- Erreurs détaillées pour le debugging
- Traçabilité complète des transactions

## Interface utilisateur

### Page de détail du véhicule
- Bouton d'achat visible si disponible
- Prix affiché en euros
- Statut du véhicule (disponible/vendu)

### Page d'achat
- Design moderne et responsive
- Informations complètes du véhicule
- Bouton d'achat avec loading
- Intégration Stripe transparente

### Pages de résultat
- **Succès** : Confirmation avec prochaines étapes
- **Annulation** : Possibilité de réessayer

## Configuration

### Variables d'environnement
```yaml
# config/services.yaml
parameters:
    stripe_public_key: 'pk_test_...'
    stripe_secret_key: 'sk_test_...'
    stripe_webhook_secret: 'whsec_...'
```

### Webhooks Stripe
Événements à configurer :
- `checkout.session.completed`
- `checkout.session.expired`
- `payment_intent.succeeded`
- `payment_intent.payment_failed`

## Tests

### Test manuel
1. Connectez-vous à l'application
2. Allez sur la liste des véhicules
3. Cliquez sur un véhicule disponible
4. Cliquez sur "Acheter ce véhicule"
5. Testez le processus de paiement

### Test avec Stripe CLI
```bash
# Écouter les webhooks en local
stripe listen --forward-to localhost:8080/webhook/stripe

# Tester un événement
stripe trigger checkout.session.completed
```

## Dépannage

### Problèmes courants

1. **Bouton d'achat invisible**
   - Vérifiez que l'utilisateur est connecté
   - Vérifiez le statut du véhicule (`available`)

2. **Erreur de paiement**
   - Vérifiez les clés Stripe
   - Consultez les logs d'erreur
   - Vérifiez la configuration webhook

3. **Véhicule non marqué comme vendu**
   - Vérifiez les webhooks Stripe
   - Consultez les logs webhook
   - Vérifiez la base de données

### Logs utiles
```bash
# Logs d'achat
tail -f var/log/dev.log | grep -i "achat\|purchase"

# Logs webhook
tail -f var/log/dev.log | grep -i "webhook"

# Logs Stripe
tail -f var/log/dev.log | grep -i "stripe"
```

## Améliorations futures

### Fonctionnalités à ajouter
- [ ] Email de confirmation d'achat
- [ ] Facture PDF automatique
- [ ] Historique des achats utilisateur
- [ ] Système de réservation
- [ ] Paiement en plusieurs fois
- [ ] Garantie étendue

### Optimisations
- [ ] Cache des images véhicules
- [ ] Optimisation des requêtes base de données
- [ ] Monitoring des performances
- [ ] Tests automatisés

## Support

Pour toute question ou problème :
- **Email** : support@quernel-auto.fr
- **Téléphone** : 01 23 45 67 89
- **Documentation** : Voir les autres fichiers dans `/docs/` 