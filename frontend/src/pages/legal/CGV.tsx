import { Layout } from '@/components/layout';
import { Card } from '@/components/ui';

export function CGVPage() {
  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen py-12">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl font-black text-gray-900 mb-8">
            Conditions Générales de Vente
          </h1>

          <Card variant="elevated" className="prose prose-gray max-w-none">
            <p className="lead">
              Les présentes conditions générales de vente régissent les relations
              contractuelles entre Quernel Auto et ses clients.
            </p>

            <h2>Article 1 - Objet</h2>
            <p>
              Les présentes CGV ont pour objet de définir les droits et obligations
              des parties dans le cadre de la vente de véhicules d'occasion proposés
              par Quernel Auto.
            </p>

            <h2>Article 2 - Prix</h2>
            <p>
              Les prix de nos véhicules sont indiqués en euros toutes taxes comprises
              (TTC). Quernel Auto se réserve le droit de modifier ses prix à tout
              moment, étant entendu que le prix figurant au catalogue le jour de la
              commande sera le seul applicable à l'acheteur.
            </p>

            <h2>Article 3 - Commande</h2>
            <p>
              Toute commande implique l'acceptation des présentes CGV. La validation
              de la commande vaut acceptation des prix et descriptions des véhicules
              disponibles à la vente.
            </p>

            <h2>Article 4 - Paiement</h2>
            <p>
              Le paiement s'effectue par carte bancaire via notre plateforme
              sécurisée Stripe. Un acompte de 10% est demandé à la commande, le solde
              étant dû à la livraison du véhicule.
            </p>

            <h2>Article 5 - Livraison</h2>
            <p>
              Les véhicules sont livrés à l'adresse indiquée par le client lors de la
              commande. Les délais de livraison sont donnés à titre indicatif et ne
              peuvent donner lieu à aucune pénalité.
            </p>

            <h2>Article 6 - Garantie</h2>
            <p>
              Tous nos véhicules bénéficient de la garantie légale de conformité (2
              ans) et de la garantie contre les vices cachés. Une extension de
              garantie peut être souscrite lors de l'achat.
            </p>

            <h2>Article 7 - Droit de rétractation</h2>
            <p>
              Conformément à l'article L221-28 du Code de la consommation, le droit
              de rétractation ne s'applique pas aux véhicules personnalisés ou
              fabriqués sur mesure.
            </p>

            <h2>Article 8 - Litiges</h2>
            <p>
              En cas de litige, une solution amiable sera recherchée avant toute
              action judiciaire. À défaut d'accord, les tribunaux de Paris seront
              seuls compétents.
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

export default CGVPage;
