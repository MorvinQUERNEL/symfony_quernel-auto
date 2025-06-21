# Configuration des Webhooks Stripe

Ce document explique comment configurer et utiliser les webhooks Stripe dans l'application Quernel Auto.

## Qu'est-ce qu'un webhook ?

Un webhook est un mécanisme qui permet à Stripe d'envoyer des notifications en temps réel à votre application lorsqu'un événement se produit (paiement réussi, échec, etc.).

## Configuration

### 1. Configuration dans Stripe Dashboard

1. Connectez-vous à votre [Dashboard Stripe](https://dashboard.stripe.com/)
2. Allez dans **Développeurs > Webhooks**
3. Cliquez sur **"Ajouter un endpoint"**
4. Configurez l'endpoint :
   - **URL** : `https://votre-domaine.com/webhook/stripe`
   - **Événements à écouter** :
     - `checkout.session.completed`
     - `checkout.session.expired`
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `payment_intent.canceled`
     - `invoice.payment_succeeded`
     - `invoice.payment_failed`

### 2. Configuration dans l'application

1. Copiez la **clé secrète du webhook** depuis le dashboard Stripe
2. Mettez à jour le fichier `config/services.yaml` :

```yaml
parameters:
    stripe_webhook_secret: 'whsec_votre_cle_secrete_ici'
```

### 3. Test de la configuration

Utilisez la commande Symfony pour tester la configuration :

```bash
php bin/console stripe:webhook:setup
```

## Événements gérés

### checkout.session.completed
- **Déclencheur** : Session de paiement terminée avec succès
- **Action** : Marque la commande comme payée et crée un enregistrement de paiement

### checkout.session.expired
- **Déclencheur** : Session de paiement expirée
- **Action** : Marque la commande comme expirée

### payment_intent.succeeded
- **Déclencheur** : Paiement réussi
- **Action** : Log de l'événement

### payment_intent.payment_failed
- **Déclencheur** : Échec de paiement
- **Action** : Log de l'événement avec les détails de l'erreur

### payment_intent.canceled
- **Déclencheur** : Paiement annulé
- **Action** : Log de l'événement

## Sécurité

### Vérification de signature
Les webhooks utilisent la vérification de signature Stripe pour s'assurer que les événements proviennent bien de Stripe :

```php
$event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
```

### Gestion des erreurs
- Logs détaillés de tous les événements
- Gestion des erreurs avec rollback en cas de problème
- Réponses HTTP appropriées (200, 400, 500)

## Développement local

### Utilisation de Stripe CLI

Pour tester les webhooks en local, utilisez Stripe CLI :

1. Installez Stripe CLI
2. Connectez-vous : `stripe login`
3. Écoutez les événements : `stripe listen --forward-to localhost:8080/webhook/stripe`

### Configuration ngrok

Pour exposer votre serveur local :

1. Installez ngrok
2. Lancez : `ngrok http 8080`
3. Utilisez l'URL ngrok dans votre webhook Stripe

## Monitoring

### Logs
Tous les événements webhook sont loggés dans `var/log/dev.log` (ou `prod.log` en production).

### Commandes utiles

```bash
# Voir les logs des webhooks
tail -f var/log/dev.log | grep webhook

# Tester un webhook
php bin/console stripe:webhook:setup

# Vérifier la configuration
php bin/console debug:router | grep webhook
```

## Dépannage

### Erreurs courantes

1. **Signature invalide** : Vérifiez que la clé secrète est correcte
2. **Payload invalide** : Vérifiez que l'URL du webhook est accessible
3. **Erreur 500** : Vérifiez les logs pour plus de détails

### Vérifications

- [ ] URL du webhook accessible publiquement
- [ ] Clé secrète correctement configurée
- [ ] Événements sélectionnés dans Stripe
- [ ] Logs sans erreur
- [ ] Base de données accessible

## Production

### Recommandations

1. Utilisez HTTPS pour l'URL du webhook
2. Configurez des alertes sur les échecs de webhook
3. Surveillez les logs régulièrement
4. Testez les webhooks après chaque déploiement
5. Utilisez des clés secrètes différentes pour dev/prod

### Monitoring

- Surveillez les logs d'erreur
- Configurez des alertes Stripe
- Vérifiez régulièrement le statut des webhooks dans le dashboard 