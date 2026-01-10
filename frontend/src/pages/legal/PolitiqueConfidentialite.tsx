import { Layout } from '@/components/layout';
import { Card } from '@/components/ui';

export function PolitiqueConfidentialitePage() {
  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen py-12">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl font-black text-gray-900 mb-8">
            Politique de Confidentialité
          </h1>

          <Card variant="elevated" className="prose prose-gray max-w-none">
            <p className="lead">
              Quernel Auto s'engage à protéger votre vie privée et à traiter vos
              données personnelles conformément au RGPD.
            </p>

            <h2>1. Collecte des données</h2>
            <p>
              Nous collectons les données que vous nous fournissez directement :
            </p>
            <ul>
              <li>Nom, prénom, adresse email</li>
              <li>Numéro de téléphone</li>
              <li>Adresse postale</li>
              <li>Informations de paiement (traitées par Stripe)</li>
            </ul>

            <h2>2. Utilisation des données</h2>
            <p>Vos données sont utilisées pour :</p>
            <ul>
              <li>Traiter vos commandes et livraisons</li>
              <li>Gérer votre compte client</li>
              <li>Vous envoyer des communications relatives à vos commandes</li>
              <li>Améliorer nos services</li>
            </ul>

            <h2>3. Partage des données</h2>
            <p>
              Vos données peuvent être partagées avec nos prestataires (transport,
              paiement) uniquement dans le cadre de l'exécution de nos services.
              Nous ne vendons jamais vos données à des tiers.
            </p>

            <h2>4. Durée de conservation</h2>
            <p>
              Vos données sont conservées pendant la durée de votre relation
              commerciale avec nous, puis archivées pendant la durée légale de
              prescription applicable.
            </p>

            <h2>5. Vos droits</h2>
            <p>
              Conformément au RGPD, vous disposez des droits suivants :
            </p>
            <ul>
              <li>Droit d'accès à vos données</li>
              <li>Droit de rectification</li>
              <li>Droit à l'effacement ("droit à l'oubli")</li>
              <li>Droit à la limitation du traitement</li>
              <li>Droit à la portabilité</li>
              <li>Droit d'opposition</li>
            </ul>

            <h2>6. Sécurité</h2>
            <p>
              Nous mettons en œuvre des mesures techniques et organisationnelles
              appropriées pour protéger vos données contre tout accès non autorisé,
              modification, divulgation ou destruction.
            </p>

            <h2>7. Cookies</h2>
            <p>
              Notre site utilise des cookies essentiels au fonctionnement du site
              et des cookies analytiques pour améliorer nos services. Vous pouvez
              gérer vos préférences de cookies à tout moment.
            </p>

            <h2>8. Contact</h2>
            <p>
              Pour toute question relative à la protection de vos données :<br />
              Email : rgpd@quernel-auto.fr<br />
              Adresse : Quernel Auto - DPO, 123 Avenue des Champs-Élysées, 75008 Paris
            </p>

            <p className="text-sm text-gray-500 mt-8">
              Dernière mise à jour : Janvier 2024
            </p>
          </Card>
        </div>
      </div>
    </Layout>
  );
}

export default PolitiqueConfidentialitePage;
