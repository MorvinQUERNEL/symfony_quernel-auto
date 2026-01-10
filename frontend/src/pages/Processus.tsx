import { Link } from 'react-router-dom';
import { Search, FileCheck, CreditCard, Truck, CheckCircle, ArrowRight } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Button, Card } from '@/components/ui';

const steps = [
  {
    icon: <Search className="w-8 h-8" />,
    title: 'Recherche du véhicule',
    description:
      'Parcourez notre catalogue ou décrivez-nous le véhicule de vos rêves. Nous recherchons pour vous les meilleures offres sur le marché européen.',
  },
  {
    icon: <FileCheck className="w-8 h-8" />,
    title: 'Vérification complète',
    description:
      'Chaque véhicule est minutieusement inspecté : historique, kilométrage, état mécanique et carrosserie. Aucun défaut caché.',
  },
  {
    icon: <CreditCard className="w-8 h-8" />,
    title: 'Paiement sécurisé',
    description:
      'Réglez en toute confiance via notre plateforme sécurisée. Paiement échelonné possible selon conditions.',
  },
  {
    icon: <Truck className="w-8 h-8" />,
    title: 'Livraison à domicile',
    description:
      "Votre véhicule est transporté par des professionnels et livré directement chez vous, partout en France. Assurance complète incluse.",
  },
  {
    icon: <CheckCircle className="w-8 h-8" />,
    title: 'Garantie incluse',
    description:
      'Profitez de notre garantie constructeur ou de notre garantie maison. Votre satisfaction est notre priorité.',
  },
];

const advantages = [
  { value: '3 semaines', label: 'Délai moyen de livraison' },
  { value: '12 mois', label: 'Garantie minimum' },
  { value: '100%', label: 'Transparence sur les prix' },
  { value: '24/7', label: 'Support client disponible' },
];

export function ProcessusPage() {
  return (
    <Layout>
      {/* Hero */}
      <section className="relative py-20 bg-gradient-to-br from-gray-900 to-gray-800 overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div
            className="absolute inset-0"
            style={{
              backgroundImage: `linear-gradient(rgba(255,107,0,0.2) 1px, transparent 1px),
                               linear-gradient(90deg, rgba(255,107,0,0.2) 1px, transparent 1px)`,
              backgroundSize: '40px 40px',
            }}
          />
        </div>
        <div className="absolute top-0 right-1/4 w-96 h-96 bg-[#FF6B00]/10 rounded-full blur-[100px]" />

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 className="text-4xl sm:text-5xl font-black text-white mb-4">
            Notre <span className="text-[#FF6B00]">Processus</span>
          </h1>
          <p className="text-gray-300 max-w-2xl mx-auto">
            Un accompagnement personnalisé de A à Z pour l'acquisition de votre véhicule.
          </p>
        </div>
      </section>

      {/* Steps */}
      <section className="py-24 bg-white">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <span className="text-[#FF6B00] font-semibold uppercase tracking-wider text-sm">
              Comment ça marche
            </span>
            <h2 className="mt-4 text-3xl sm:text-4xl font-black text-gray-900">
              5 étapes simples
            </h2>
          </div>

          <div className="relative">
            {/* Connection line */}
            <div className="absolute left-8 top-0 bottom-0 w-0.5 bg-gradient-to-b from-[#FF6B00] to-[#FFAA00] hidden md:block" />

            <div className="space-y-12">
              {steps.map((step, index) => (
                <div key={step.title} className="relative flex gap-8">
                  {/* Step number */}
                  <div className="relative z-10 flex-shrink-0">
                    <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#FF6B00] to-[#FF8533] flex items-center justify-center text-white shadow-lg shadow-[#FF6B00]/30">
                      {step.icon}
                    </div>
                    <div className="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-gray-900 text-white text-xs font-bold flex items-center justify-center">
                      {index + 1}
                    </div>
                  </div>

                  {/* Content */}
                  <Card variant="outlined" hover className="flex-1">
                    <h3 className="text-xl font-bold text-gray-900 mb-2">
                      {step.title}
                    </h3>
                    <p className="text-gray-600">{step.description}</p>
                  </Card>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Advantages */}
      <section className="py-24 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <span className="text-[#FF6B00] font-semibold uppercase tracking-wider text-sm">
              Nos engagements
            </span>
            <h2 className="mt-4 text-3xl sm:text-4xl font-black text-gray-900">
              Pourquoi nous faire confiance
            </h2>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {advantages.map((adv) => (
              <Card key={adv.label} variant="elevated" className="text-center">
                <div className="text-4xl font-black text-[#FF6B00] mb-2">
                  {adv.value}
                </div>
                <p className="text-gray-600">{adv.label}</p>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-24 bg-white">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <Card variant="elevated" className="relative overflow-hidden">
            <div className="absolute top-0 right-0 w-64 h-64 bg-[#FF6B00]/10 rounded-full blur-[80px]" />
            <div className="relative text-center py-8">
              <h2 className="text-2xl sm:text-3xl font-black text-gray-900 mb-4">
                Prêt à commencer ?
              </h2>
              <p className="text-gray-600 max-w-xl mx-auto mb-8">
                Parcourez notre sélection de véhicules ou contactez-nous pour une recherche personnalisée.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <Link to="/vehicules">
                  <Button size="lg" rightIcon={<ArrowRight className="w-5 h-5" />}>
                    Voir les véhicules
                  </Button>
                </Link>
                <Link to="/contact">
                  <Button variant="outline" size="lg">
                    Nous contacter
                  </Button>
                </Link>
              </div>
            </div>
          </Card>
        </div>
      </section>
    </Layout>
  );
}

export default ProcessusPage;
