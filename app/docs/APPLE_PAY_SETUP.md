# 🍎 Configuration Apple Pay - Quernel Auto

## Vue d'ensemble

Apple Pay a été intégré à votre système de paiement Stripe. Cette fonctionnalité permet aux utilisateurs de payer avec Apple Pay sur les appareils compatibles (iPhone, iPad, Mac).

## ✅ Fonctionnalités implémentées

- ✅ Bouton Apple Pay automatique (apparaît uniquement si disponible)
- ✅ Intégration avec Stripe PaymentIntents
- ✅ Support des métadonnées de commande
- ✅ Gestion des erreurs et annulations
- ✅ Design responsive et accessible
- ✅ Styles CSS personnalisés

## 🔧 Configuration requise

### 1. Compte Stripe
- Compte Stripe actif
- Clés API configurées
- Webhooks configurés

### 2. Domaine HTTPS
- **OBLIGATOIRE** : Apple Pay ne fonctionne qu'en HTTPS
- Certificat SSL valide
- Domaine vérifié dans Stripe

### 3. Apple Developer Account (pour la production)
- Compte Apple Developer payant
- Merchant ID Apple Pay
- Certificat de domaine

## 🚀 Configuration en développement

### 1. Variables d'environnement
Ajoutez dans votre `.env.local` :
```env
# Stripe
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...

# Apple Pay (optionnel en dev)
APPLE_PAY_MERCHANT_ID=merchant.com.quernel.auto
```

### 2. Test en local
Pour tester Apple Pay en local :
1. Utilisez un tunnel HTTPS (ngrok, localtunnel)
2. Configurez le domaine dans Stripe
3. Testez sur un appareil iOS/Safari

## 🌐 Configuration en production

### 1. Apple Developer Account
1. Créez un compte Apple Developer (99$/an)
2. Accédez à "Certificates, Identifiers & Profiles"
3. Créez un "Merchant ID" pour Apple Pay
4. Notez votre Merchant ID

### 2. Vérification de domaine
1. Créez le fichier `.well-known/apple-developer-merchantid-domain-association`
2. Remplacez le contenu par votre vrai fichier de vérification
3. Vérifiez que le fichier est accessible via HTTPS

### 3. Configuration Stripe
1. Dans votre dashboard Stripe :
   - Allez dans "Settings" > "Payment methods"
   - Activez Apple Pay
   - Ajoutez votre domaine
   - Configurez votre Merchant ID

### 4. Variables d'environnement de production
```env
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...
APPLE_PAY_MERCHANT_ID=merchant.com.quernel.auto
```

## 📱 Test et validation

### 1. Test en développement
```bash
# Démarrer un tunnel HTTPS
ngrok http 8000

# Tester sur iPhone/iPad
# Ouvrir Safari et naviguer vers votre site
```

### 2. Vérification
- ✅ Le bouton Apple Pay apparaît sur les appareils compatibles
- ✅ Le bouton est masqué sur les appareils non compatibles
- ✅ Le paiement se déroule correctement
- ✅ Les webhooks Stripe reçoivent les événements

### 3. Appareils de test
- iPhone avec Apple Pay configuré
- iPad avec Apple Pay configuré
- Mac avec Safari et Apple Pay configuré

## 🔍 Dépannage

### Le bouton Apple Pay n'apparaît pas
1. Vérifiez que vous êtes sur HTTPS
2. Vérifiez que vous utilisez Safari ou un navigateur compatible
3. Vérifiez que l'appareil supporte Apple Pay
4. Vérifiez les logs JavaScript dans la console

### Erreur de paiement
1. Vérifiez les logs Stripe
2. Vérifiez les webhooks
3. Vérifiez la configuration du Merchant ID

### Problèmes de domaine
1. Vérifiez que le fichier `.well-known/apple-developer-merchantid-domain-association` est accessible
2. Vérifiez la configuration dans Apple Developer
3. Vérifiez la configuration dans Stripe

## 📊 Analytics et monitoring

### Métriques à surveiller
- Taux d'adoption Apple Pay
- Taux de conversion vs autres méthodes
- Taux d'erreur
- Performance des paiements

### Logs à configurer
```php
// Dans PaymentController.php
$this->logger->info('Apple Pay payment initiated', [
    'order_id' => $order->getId(),
    'user_id' => $user->getId(),
    'amount' => $order->getTotalPrice(),
]);
```

## 🔒 Sécurité

### Bonnes pratiques
- ✅ Toujours utiliser HTTPS
- ✅ Valider les données côté serveur
- ✅ Logger les tentatives de paiement
- ✅ Gérer les erreurs gracieusement
- ✅ Respecter les guidelines Apple Pay

### Validation
- ✅ Vérifier l'authenticite des paiements
- ✅ Valider les montants
- ✅ Vérifier les permissions utilisateur
- ✅ Gérer les doublons

## 📚 Ressources

- [Documentation Apple Pay](https://developer.apple.com/documentation/apple_pay_on_the_web)
- [Documentation Stripe Apple Pay](https://stripe.com/docs/apple-pay)
- [Guidelines Apple Pay](https://developer.apple.com/design/human-interface-guidelines/apple-pay)
- [Test Apple Pay](https://developer.apple.com/apple-pay/sandbox-testing/)

## 🆘 Support

En cas de problème :
1. Vérifiez les logs d'erreur
2. Consultez la documentation Stripe
3. Contactez le support Stripe si nécessaire
4. Vérifiez la configuration Apple Developer

---

**Note** : Apple Pay est une fonctionnalité premium qui nécessite une configuration soigneuse. En développement, vous pouvez utiliser les cartes de test Stripe pour simuler les paiements. 