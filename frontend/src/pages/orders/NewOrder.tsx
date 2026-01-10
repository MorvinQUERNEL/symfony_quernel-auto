import { useSearchParams, useNavigate, Link } from 'react-router-dom';
import { useQuery, useMutation } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { ArrowLeft, ShoppingCart, Calendar, Gauge, Fuel, MapPin } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Card, Button, Spinner, Badge, Input } from '@/components/ui';
import { vehiculesApi, ordersApi } from '@/api';
import { useAuthStore } from '@/stores';
import { getImageUrl, getYearFromDate, formatPrice } from '@/types';

const orderSchema = z.object({
  deliveryAddress: z.string().min(5, 'L\'adresse est requise (min 5 caractères)'),
  deliveryCity: z.string().min(2, 'La ville est requise'),
  deliveryPostalCode: z.string().min(4, 'Le code postal est requis'),
  deliveryCountry: z.string().min(2, 'Le pays est requis'),
  notes: z.string().optional(),
});

type OrderFormData = z.infer<typeof orderSchema>;

export function NewOrderPage() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const vehiculeId = searchParams.get('vehicule');
  const { user } = useAuthStore();

  const { data: vehicule, isLoading } = useQuery({
    queryKey: ['vehicule', vehiculeId],
    queryFn: () => vehiculesApi.getById(Number(vehiculeId)),
    enabled: !!vehiculeId,
  });

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<OrderFormData>({
    resolver: zodResolver(orderSchema),
    defaultValues: {
      deliveryAddress: user?.address || '',
      deliveryCity: user?.city || '',
      deliveryPostalCode: user?.postalCode?.toString() || '',
      deliveryCountry: user?.country || 'France',
      notes: '',
    },
  });

  const createOrderMutation = useMutation({
    mutationFn: (data: OrderFormData) =>
      ordersApi.create({
        vehiculeId: Number(vehiculeId),
        deliveryAddress: data.deliveryAddress,
        deliveryCity: data.deliveryCity,
        deliveryPostalCode: data.deliveryPostalCode,
        deliveryCountry: data.deliveryCountry,
        notes: data.notes || undefined,
      }),
    onSuccess: (order) => {
      navigate(`/orders/${order.id}`);
    },
  });

  if (!vehiculeId) {
    return (
      <Layout>
        <div className="max-w-3xl mx-auto px-4 py-20 text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">
            Aucun véhicule sélectionné
          </h1>
          <Link to="/vehicules">
            <Button>Voir les véhicules</Button>
          </Link>
        </div>
      </Layout>
    );
  }

  if (isLoading) {
    return (
      <Layout>
        <div className="flex items-center justify-center min-h-[60vh]">
          <Spinner size="xl" />
        </div>
      </Layout>
    );
  }

  if (!vehicule) {
    return (
      <Layout>
        <div className="max-w-3xl mx-auto px-4 py-20 text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">
            Véhicule non trouvé
          </h1>
          <Link to="/vehicules">
            <Button leftIcon={<ArrowLeft className="w-5 h-5" />}>
              Retour aux véhicules
            </Button>
          </Link>
        </div>
      </Layout>
    );
  }

  if (vehicule.status !== 'available') {
    return (
      <Layout>
        <div className="max-w-3xl mx-auto px-4 py-20 text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">
            Véhicule non disponible
          </h1>
          <p className="text-gray-600 mb-8">
            Ce véhicule n'est plus disponible à la vente.
          </p>
          <Link to="/vehicules">
            <Button>Voir les autres véhicules</Button>
          </Link>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen py-12">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Back button */}
          <Link
            to={`/vehicules/${vehiculeId}`}
            className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6"
          >
            <ArrowLeft className="w-5 h-5" />
            Retour au véhicule
          </Link>

          <h1 className="text-3xl font-black text-gray-900 mb-8">
            Commander ce véhicule
          </h1>

          <form onSubmit={handleSubmit((data) => createOrderMutation.mutate(data))}>
            <div className="grid lg:grid-cols-3 gap-8">
              {/* Main content */}
              <div className="lg:col-span-2 space-y-6">
                {/* Vehicle summary */}
                <Card variant="elevated">
                  <div className="flex gap-6">
                    <div className="w-48 h-32 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                      <img
                        src={getImageUrl(vehicule.pictures[0])}
                        alt={`${vehicule.brand} ${vehicule.model}`}
                        className="w-full h-full object-cover"
                      />
                    </div>
                    <div className="flex-1">
                      <Badge variant="success" dot pulse className="mb-2">
                        Disponible
                      </Badge>
                      <h2 className="text-xl font-bold text-gray-900">
                        {vehicule.brand} {vehicule.model}
                      </h2>
                      <div className="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
                        <span className="flex items-center gap-1">
                          <Calendar className="w-4 h-4" />
                          {getYearFromDate(vehicule.year)}
                        </span>
                        <span className="flex items-center gap-1">
                          <Gauge className="w-4 h-4" />
                          {vehicule.mileage.toLocaleString('fr-FR')} km
                        </span>
                        <span className="flex items-center gap-1">
                          <Fuel className="w-4 h-4" />
                          {vehicule.fuelType}
                        </span>
                      </div>
                    </div>
                  </div>
                </Card>

                {/* Buyer info */}
                <Card variant="elevated">
                  <h3 className="font-bold text-gray-900 mb-4">Vos informations</h3>
                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <p className="text-gray-500">Nom</p>
                      <p className="font-medium">{user?.firstname} {user?.lastname}</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Email</p>
                      <p className="font-medium">{user?.email}</p>
                    </div>
                    {user?.phoneNumber && (
                      <div>
                        <p className="text-gray-500">Téléphone</p>
                        <p className="font-medium">{user.phoneNumber}</p>
                      </div>
                    )}
                  </div>
                </Card>

                {/* Delivery address */}
                <Card variant="elevated">
                  <h3 className="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <MapPin className="w-5 h-5 text-[#FF6B00]" />
                    Adresse de livraison
                  </h3>
                  <div className="space-y-4">
                    <Input
                      label="Adresse"
                      placeholder="123 Rue de la Paix"
                      error={errors.deliveryAddress?.message}
                      {...register('deliveryAddress')}
                    />
                    <div className="grid grid-cols-2 gap-4">
                      <Input
                        label="Ville"
                        placeholder="Paris"
                        error={errors.deliveryCity?.message}
                        {...register('deliveryCity')}
                      />
                      <Input
                        label="Code postal"
                        placeholder="75001"
                        error={errors.deliveryPostalCode?.message}
                        {...register('deliveryPostalCode')}
                      />
                    </div>
                    <Input
                      label="Pays"
                      placeholder="France"
                      error={errors.deliveryCountry?.message}
                      {...register('deliveryCountry')}
                    />
                  </div>
                </Card>

                {/* Notes */}
                <Card variant="elevated">
                  <h3 className="font-bold text-gray-900 mb-4">Notes (optionnel)</h3>
                  <textarea
                    {...register('notes')}
                    rows={4}
                    placeholder="Ajoutez des informations supplémentaires si nécessaire..."
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-[#FF6B00] focus:ring-4 focus:ring-[#FF6B00]/10 outline-none transition-all resize-none"
                  />
                </Card>
              </div>

              {/* Sidebar */}
              <div className="space-y-6">
                {/* Price summary */}
                <Card variant="elevated">
                  <h3 className="font-bold text-gray-900 mb-4">Récapitulatif</h3>
                  <div className="space-y-3 pb-4 border-b border-gray-100">
                    <div className="flex justify-between text-gray-600">
                      <span>Prix du véhicule</span>
                      <span>{formatPrice(vehicule.salePrice)} €</span>
                    </div>
                  </div>
                  <div className="flex justify-between items-baseline pt-4">
                    <span className="text-gray-900 font-semibold">Total</span>
                    <span className="text-2xl font-black text-[#FF6B00]">
                      {formatPrice(vehicule.salePrice)} €
                    </span>
                  </div>
                </Card>

                {/* Submit */}
                <Button
                  type="submit"
                  fullWidth
                  size="lg"
                  isLoading={createOrderMutation.isPending}
                  leftIcon={<ShoppingCart className="w-5 h-5" />}
                >
                  Confirmer la commande
                </Button>

                {createOrderMutation.error && (
                  <Card variant="outlined" className="bg-red-50 border-red-200">
                    <p className="text-sm text-red-600 text-center">
                      {(createOrderMutation.error as { message?: string })?.message ||
                        'Une erreur est survenue'}
                    </p>
                  </Card>
                )}

                <p className="text-xs text-gray-500 text-center">
                  En confirmant, vous acceptez nos{' '}
                  <Link to="/cgv" className="text-[#FF6B00] hover:underline">
                    conditions générales
                  </Link>.
                </p>
              </div>
            </div>
          </form>
        </div>
      </div>
    </Layout>
  );
}

export default NewOrderPage;
