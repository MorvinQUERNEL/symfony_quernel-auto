import { useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import {
  ArrowLeft,
  ChevronLeft,
  ChevronRight,
  Calendar,
  Gauge,
  Fuel,
  Settings,
  Palette,
  Phone,
  MessageSquare,
  ShoppingCart,
  Share2,
  Heart,
} from 'lucide-react';
import { Layout } from '@/components/layout';
import { Button, Card, StatusBadge, Spinner } from '@/components/ui';
import { vehiculesApi } from '@/api';
import { useAuthStore } from '@/stores';
import { clsx } from 'clsx';
import { getImageUrl, getYearFromDate, formatPrice } from '@/types';

export function VehiculeDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const { isAuthenticated } = useAuthStore();

  const { data: vehicule, isLoading, error } = useQuery({
    queryKey: ['vehicule', id],
    queryFn: () => vehiculesApi.getById(Number(id)),
    enabled: !!id,
  });

  if (isLoading) {
    return (
      <Layout>
        <div className="flex items-center justify-center min-h-[60vh]">
          <Spinner size="xl" />
        </div>
      </Layout>
    );
  }

  if (error || !vehicule) {
    return (
      <Layout>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">
            Véhicule non trouvé
          </h1>
          <p className="text-gray-600 mb-8">
            Ce véhicule n'existe pas ou n'est plus disponible.
          </p>
          <Link to="/vehicules">
            <Button leftIcon={<ArrowLeft className="w-5 h-5" />}>
              Retour aux véhicules
            </Button>
          </Link>
        </div>
      </Layout>
    );
  }

  const images = vehicule.pictures.length > 0
    ? vehicule.pictures.map((p) => getImageUrl(p))
    : ['/placeholder-car.jpg'];

  const nextImage = () => {
    setCurrentImageIndex((prev) => (prev + 1) % images.length);
  };

  const prevImage = () => {
    setCurrentImageIndex((prev) => (prev - 1 + images.length) % images.length);
  };

  const specs = [
    { icon: <Calendar className="w-5 h-5" />, label: 'Année', value: getYearFromDate(vehicule.year) },
    { icon: <Gauge className="w-5 h-5" />, label: 'Kilométrage', value: `${vehicule.mileage.toLocaleString('fr-FR')} km` },
    { icon: <Fuel className="w-5 h-5" />, label: 'Carburant', value: vehicule.fuelType },
    { icon: <Settings className="w-5 h-5" />, label: 'Transmission', value: vehicule.trasmission },
    ...(vehicule.color ? [{ icon: <Palette className="w-5 h-5" />, label: 'Couleur', value: vehicule.color }] : []),
  ];

  const handleOrder = () => {
    if (isAuthenticated) {
      navigate(`/orders/new?vehicule=${vehicule.id}`);
    } else {
      navigate('/login', { state: { from: { pathname: `/vehicules/${vehicule.id}` } } });
    }
  };

  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen">
        {/* Back button */}
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <Link
            to="/vehicules"
            className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors"
          >
            <ArrowLeft className="w-5 h-5" />
            <span>Retour aux véhicules</span>
          </Link>
        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
          <div className="grid lg:grid-cols-2 gap-8">
            {/* Image gallery */}
            <div className="space-y-4">
              {/* Main image */}
              <div className="relative aspect-[4/3] rounded-2xl overflow-hidden bg-white shadow-lg">
                <img
                  src={images[currentImageIndex]}
                  alt={`${vehicule.brand} ${vehicule.model}`}
                  className="w-full h-full object-cover"
                />

                {/* Navigation arrows */}
                {images.length > 1 && (
                  <>
                    <button
                      onClick={prevImage}
                      className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm shadow-lg flex items-center justify-center text-gray-700 hover:bg-white transition-colors"
                    >
                      <ChevronLeft className="w-6 h-6" />
                    </button>
                    <button
                      onClick={nextImage}
                      className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm shadow-lg flex items-center justify-center text-gray-700 hover:bg-white transition-colors"
                    >
                      <ChevronRight className="w-6 h-6" />
                    </button>
                  </>
                )}

                {/* Image counter */}
                {images.length > 1 && (
                  <div className="absolute bottom-4 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-black/60 backdrop-blur-sm text-white text-sm">
                    {currentImageIndex + 1} / {images.length}
                  </div>
                )}

                {/* Status badge */}
                <div className="absolute top-4 left-4">
                  <StatusBadge status={vehicule.status} size="lg" />
                </div>

                {/* Action buttons */}
                <div className="absolute top-4 right-4 flex gap-2">
                  <button className="w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm shadow-lg flex items-center justify-center text-gray-700 hover:text-red-500 transition-colors">
                    <Heart className="w-5 h-5" />
                  </button>
                  <button className="w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm shadow-lg flex items-center justify-center text-gray-700 hover:text-[#FF6B00] transition-colors">
                    <Share2 className="w-5 h-5" />
                  </button>
                </div>
              </div>

              {/* Thumbnails */}
              {images.length > 1 && (
                <div className="flex gap-3 overflow-x-auto pb-2">
                  {images.map((image, index) => (
                    <button
                      key={index}
                      onClick={() => setCurrentImageIndex(index)}
                      className={clsx(
                        'flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all',
                        currentImageIndex === index
                          ? 'border-[#FF6B00] shadow-lg'
                          : 'border-transparent opacity-60 hover:opacity-100'
                      )}
                    >
                      <img
                        src={image}
                        alt=""
                        className="w-full h-full object-cover"
                      />
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Details */}
            <div className="space-y-6">
              {/* Title and price */}
              <div>
                <div className="flex items-start justify-between gap-4 mb-2">
                  <h1 className="text-3xl font-black text-gray-900">
                    {vehicule.brand} {vehicule.model}
                  </h1>
                </div>
                <p className="text-gray-500">{getYearFromDate(vehicule.year)}</p>

                <div className="mt-4 flex items-baseline gap-2">
                  <span className="text-4xl font-black text-[#FF6B00]">
                    {formatPrice(vehicule.salePrice)}
                  </span>
                  <span className="text-xl text-gray-500">€</span>
                </div>
              </div>

              {/* Specs grid */}
              <Card variant="outlined" padding="none">
                <div className="divide-y divide-gray-100">
                  {specs.map((spec) => (
                    <div
                      key={spec.label}
                      className="flex items-center justify-between px-5 py-4"
                    >
                      <div className="flex items-center gap-3 text-gray-600">
                        <span className="text-[#FF6B00]">{spec.icon}</span>
                        {spec.label}
                      </div>
                      <span className="font-semibold text-gray-900">
                        {spec.value}
                      </span>
                    </div>
                  ))}
                </div>
              </Card>

              {/* Description */}
              {vehicule.description && (
                <Card variant="outlined">
                  <h3 className="font-bold text-gray-900 mb-3">Description</h3>
                  <p className="text-gray-600 whitespace-pre-line">
                    {vehicule.description}
                  </p>
                </Card>
              )}

              {/* Action buttons */}
              {vehicule.status === 'available' && (
                <div className="space-y-3">
                  <Button
                    size="lg"
                    fullWidth
                    onClick={handleOrder}
                    leftIcon={<ShoppingCart className="w-5 h-5" />}
                  >
                    Commander ce véhicule
                  </Button>

                  <div className="grid grid-cols-2 gap-3">
                    <Link to="/contact">
                      <Button variant="outline" fullWidth leftIcon={<Phone className="w-5 h-5" />}>
                        Nous appeler
                      </Button>
                    </Link>
                    <Link to={isAuthenticated ? '/messages' : '/login'}>
                      <Button variant="secondary" fullWidth leftIcon={<MessageSquare className="w-5 h-5" />}>
                        Envoyer un message
                      </Button>
                    </Link>
                  </div>
                </div>
              )}

              {vehicule.status !== 'available' && (
                <Card variant="outlined" className="text-center py-6 bg-gray-50">
                  <p className="text-gray-600">
                    Ce véhicule n'est plus disponible à la vente.
                  </p>
                  <Link to="/vehicules" className="mt-4 inline-block">
                    <Button variant="outline">
                      Voir d'autres véhicules
                    </Button>
                  </Link>
                </Card>
              )}
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
}

export default VehiculeDetailPage;
