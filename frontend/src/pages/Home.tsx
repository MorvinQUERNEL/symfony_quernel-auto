import { Link } from 'react-router-dom';
import { ArrowRight, Shield, Truck, Clock, Star, ChevronRight } from 'lucide-react';
import { Button, Card } from '@/components/ui';
import { Layout } from '@/components/layout';

export function HomePage() {
  const features = [
    {
      icon: <Shield className="w-6 h-6" />,
      title: 'Garantie Qualité',
      description: 'Tous nos véhicules sont rigoureusement inspectés et certifiés avant la vente.',
    },
    {
      icon: <Truck className="w-6 h-6" />,
      title: 'Livraison Sécurisée',
      description: 'Transport professionnel et assurance complète pour votre tranquillité.',
    },
    {
      icon: <Clock className="w-6 h-6" />,
      title: 'Processus Rapide',
      description: 'De la sélection à la livraison en moins de 3 semaines.',
    },
  ];

  const stats = [
    { value: '500+', label: 'Véhicules vendus' },
    { value: '98%', label: 'Clients satisfaits' },
    { value: '10+', label: "Années d'expérience" },
    { value: '24/7', label: 'Support client' },
  ];

  return (
    <Layout>
      {/* Hero Section */}
      <section className="relative min-h-[90vh] flex items-center overflow-hidden">
        {/* Background */}
        <div className="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
          {/* Grid pattern overlay */}
          <div
            className="absolute inset-0 opacity-20"
            style={{
              backgroundImage: `linear-gradient(rgba(255,107,0,0.1) 1px, transparent 1px),
                               linear-gradient(90deg, rgba(255,107,0,0.1) 1px, transparent 1px)`,
              backgroundSize: '50px 50px',
            }}
          />
          {/* Gradient orbs */}
          <div className="absolute top-1/4 right-1/4 w-96 h-96 bg-[#FF6B00]/20 rounded-full blur-[100px]" />
          <div className="absolute bottom-1/4 left-1/4 w-96 h-96 bg-[#FFAA00]/10 rounded-full blur-[100px]" />
        </div>

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            {/* Content */}
            <div className="text-center lg:text-left">
              {/* Badge */}
              <div className="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full border border-white/20 mb-8">
                <span className="w-2 h-2 bg-[#FF6B00] rounded-full animate-pulse" />
                <span className="text-sm text-white/80 font-medium">
                  Import & Export de Véhicules
                </span>
              </div>

              {/* Title */}
              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight">
                Votre véhicule
                <br />
                <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#FF6B00] to-[#FFAA00]">
                  sur mesure
                </span>
              </h1>

              <p className="mt-6 text-lg text-gray-300 max-w-xl mx-auto lg:mx-0">
                Quernel Auto vous accompagne dans l'acquisition de votre véhicule idéal.
                Import, export, nous gérons tout pour vous.
              </p>

              {/* CTAs */}
              <div className="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <Link to="/vehicules">
                  <Button size="lg" rightIcon={<ArrowRight className="w-5 h-5" />}>
                    Voir les véhicules
                  </Button>
                </Link>
                <Link to="/processus">
                  <Button variant="outline" size="lg" className="border-white/30 text-white hover:bg-white hover:text-gray-900">
                    Notre processus
                  </Button>
                </Link>
              </div>

              {/* Stats */}
              <div className="mt-16 grid grid-cols-2 sm:grid-cols-4 gap-8">
                {stats.map((stat) => (
                  <div key={stat.label} className="text-center lg:text-left">
                    <div className="text-3xl font-black text-white">{stat.value}</div>
                    <div className="text-sm text-gray-400 mt-1">{stat.label}</div>
                  </div>
                ))}
              </div>
            </div>

            {/* Hero Image / Car showcase */}
            <div className="relative hidden lg:block">
              {/* Decorative ring */}
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="w-[500px] h-[500px] border border-[#FF6B00]/20 rounded-full animate-[spin_60s_linear_infinite]" />
                <div className="absolute w-[400px] h-[400px] border border-[#FF6B00]/30 rounded-full animate-[spin_40s_linear_infinite_reverse]" />
              </div>

              {/* Placeholder for car image */}
              <div className="relative aspect-[4/3] flex items-center justify-center">
                <div className="w-full max-w-md p-8 text-center">
                  <div className="w-32 h-32 mx-auto rounded-2xl bg-gradient-to-br from-[#FF6B00] to-[#FFAA00] flex items-center justify-center mb-6 shadow-2xl shadow-[#FF6B00]/30">
                    <span className="text-6xl font-black text-white">Q</span>
                  </div>
                  <p className="text-gray-400 text-sm">
                    Image du véhicule à la une
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Scroll indicator */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-white/50">
          <span className="text-xs uppercase tracking-widest">Découvrir</span>
          <div className="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center p-2">
            <div className="w-1.5 h-3 bg-white/50 rounded-full animate-bounce" />
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-24 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Section header */}
          <div className="text-center max-w-2xl mx-auto mb-16">
            <span className="text-[#FF6B00] font-semibold uppercase tracking-wider text-sm">
              Pourquoi nous choisir
            </span>
            <h2 className="mt-4 text-3xl sm:text-4xl font-black text-gray-900">
              Un service d'excellence
            </h2>
            <p className="mt-4 text-gray-600">
              Nous mettons notre expertise à votre service pour vous garantir
              une expérience d'achat unique et sans stress.
            </p>
          </div>

          {/* Features grid */}
          <div className="grid md:grid-cols-3 gap-8">
            {features.map((feature, index) => (
              <Card
                key={feature.title}
                variant="outlined"
                hover
                className="text-center group"
                style={{ animationDelay: `${index * 100}ms` }}
              >
                {/* Icon */}
                <div className="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-[#FF6B00]/10 to-[#FFAA00]/10 flex items-center justify-center text-[#FF6B00] mb-6 group-hover:scale-110 transition-transform duration-300">
                  {feature.icon}
                </div>

                <h3 className="text-xl font-bold text-gray-900 mb-3">
                  {feature.title}
                </h3>
                <p className="text-gray-600">
                  {feature.description}
                </p>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-24 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-gray-900 to-gray-800 p-12 lg:p-16">
            {/* Background decoration */}
            <div className="absolute top-0 right-0 w-64 h-64 bg-[#FF6B00]/20 rounded-full blur-[80px]" />
            <div className="absolute bottom-0 left-0 w-64 h-64 bg-[#FFAA00]/10 rounded-full blur-[80px]" />

            <div className="relative grid lg:grid-cols-2 gap-12 items-center">
              <div>
                <h2 className="text-3xl sm:text-4xl font-black text-white">
                  Prêt à trouver votre
                  <span className="text-[#FF6B00]"> véhicule idéal</span> ?
                </h2>
                <p className="mt-4 text-gray-300">
                  Parcourez notre catalogue ou contactez-nous pour une recherche personnalisée.
                  Notre équipe est à votre écoute.
                </p>
              </div>

              <div className="flex flex-col sm:flex-row gap-4 lg:justify-end">
                <Link to="/vehicules">
                  <Button size="lg" rightIcon={<ChevronRight className="w-5 h-5" />}>
                    Voir le catalogue
                  </Button>
                </Link>
                <Link to="/contact">
                  <Button variant="secondary" size="lg">
                    Nous contacter
                  </Button>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Testimonials Section */}
      <section className="py-24 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <span className="text-[#FF6B00] font-semibold uppercase tracking-wider text-sm">
              Témoignages
            </span>
            <h2 className="mt-4 text-3xl sm:text-4xl font-black text-gray-900">
              Ce que disent nos clients
            </h2>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              {
                name: 'Pierre D.',
                role: 'Client depuis 2022',
                content: "Service impeccable ! Mon véhicule est arrivé exactement comme décrit. L'équipe a été très réactive tout au long du processus.",
              },
              {
                name: 'Marie L.',
                role: 'Cliente depuis 2023',
                content: "J'ai fait importer ma voiture d'Allemagne grâce à Quernel Auto. Tout s'est passé parfaitement, je recommande vivement.",
              },
              {
                name: 'Thomas B.',
                role: 'Client depuis 2021',
                content: 'Professionnalisme et transparence. Les prix sont compétitifs et le suivi est excellent. Ma troisième commande avec eux !',
              },
            ].map((testimonial, index) => (
              <Card key={index} variant="elevated" className="relative">
                {/* Stars */}
                <div className="flex gap-1 mb-4">
                  {[...Array(5)].map((_, i) => (
                    <Star key={i} className="w-5 h-5 fill-[#FF6B00] text-[#FF6B00]" />
                  ))}
                </div>

                <p className="text-gray-600 italic mb-6">
                  "{testimonial.content}"
                </p>

                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-gradient-to-br from-[#FF6B00] to-[#FFAA00] flex items-center justify-center">
                    <span className="text-white font-bold">
                      {testimonial.name[0]}
                    </span>
                  </div>
                  <div>
                    <div className="font-semibold text-gray-900">
                      {testimonial.name}
                    </div>
                    <div className="text-sm text-gray-500">
                      {testimonial.role}
                    </div>
                  </div>
                </div>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </Layout>
  );
}

export default HomePage;
