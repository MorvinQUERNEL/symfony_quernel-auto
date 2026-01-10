import { Layout } from '@/components/layout';
import { Card } from '@/components/ui';

export function MentionsLegalesPage() {
  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen py-12">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl font-black text-gray-900 mb-8">Mentions Légales</h1>

          <Card variant="elevated" className="prose prose-gray max-w-none">
            <h2>Éditeur du site</h2>
            <p>
              <strong>Quernel Auto</strong><br />
              SAS au capital de 10 000 €<br />
              123 Avenue des Champs-Élysées<br />
              75008 Paris, France
            </p>
            <p>
              SIRET : XXX XXX XXX XXXXX<br />
              RCS Paris B XXX XXX XXX<br />
              TVA intracommunautaire : FR XX XXX XXX XXX
            </p>

            <h2>Directeur de la publication</h2>
            <p>M. / Mme [Nom du dirigeant], en qualité de Président</p>

            <h2>Hébergement</h2>
            <p>
              Le site est hébergé par :<br />
              [Nom de l'hébergeur]<br />
              [Adresse de l'hébergeur]
            </p>

            <h2>Contact</h2>
            <p>
              Téléphone : +33 1 23 45 67 89<br />
              Email : contact@quernel-auto.fr
            </p>

            <h2>Propriété intellectuelle</h2>
            <p>
              L'ensemble du contenu de ce site (textes, images, vidéos, logos, etc.)
              est la propriété exclusive de Quernel Auto ou de ses partenaires.
              Toute reproduction, distribution, modification ou utilisation de ces
              contenus sans autorisation préalable est strictement interdite.
            </p>

            <h2>Données personnelles</h2>
            <p>
              Conformément à la loi "Informatique et Libertés" du 6 janvier 1978
              modifiée et au Règlement Général sur la Protection des Données (RGPD),
              vous disposez d'un droit d'accès, de rectification, de suppression et
              d'opposition aux données vous concernant.
            </p>
            <p>
              Pour exercer ces droits, contactez-nous à : rgpd@quernel-auto.fr
            </p>

            <h2>Cookies</h2>
            <p>
              Ce site utilise des cookies pour améliorer votre expérience de
              navigation. Pour en savoir plus, consultez notre politique de
              confidentialité.
            </p>
          </Card>
        </div>
      </div>
    </Layout>
  );
}

export default MentionsLegalesPage;
