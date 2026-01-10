import { useParams, Link } from 'react-router-dom';
import { useQuery, useMutation } from '@tanstack/react-query';
import { ArrowLeft, CreditCard, XCircle, Calendar, Gauge, Fuel } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Card, Button, StatusBadge, Spinner } from '@/components/ui';
import { ordersApi, paymentApi } from '@/api';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { getImageUrl, getYearFromDate, formatPrice } from '@/types';

export function OrderDetailPage() {
  const { id } = useParams<{ id: string }>();

  const { data: order, isLoading, error, refetch } = useQuery({
    queryKey: ['order', id],
    queryFn: () => ordersApi.getById(Number(id)),
    enabled: !!id,
  });

  const checkoutMutation = useMutation({
    mutationFn: () => paymentApi.createCheckoutSession(Number(id)),
    onSuccess: (data) => {
      window.location.href = data.url;
    },
  });

  const cancelMutation = useMutation({
    mutationFn: () => ordersApi.cancel(Number(id)),
    onSuccess: () => {
      refetch();
    },
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

  if (error || !order) {
    return (
      <Layout>
        <div className="max-w-3xl mx-auto px-4 py-20 text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Commande non trouvée</h1>
          <Link to="/orders">
            <Button leftIcon={<ArrowLeft className="w-5 h-5" />}>
              Retour aux commandes
            </Button>
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
            to="/orders"
            className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6"
          >
            <ArrowLeft className="w-5 h-5" />
            Retour aux commandes
          </Link>

          {/* Header */}
          <div className="flex items-center justify-between mb-8">
            <div>
              <p className="text-sm text-gray-500">Commande #{order.id}</p>
              <h1 className="text-3xl font-black text-gray-900">
                {order.vehicules?.[0]?.brand} {order.vehicules?.[0]?.model}
              </h1>
            </div>
            <StatusBadge status={order.orderStatus} size="lg" />
          </div>

          <div className="grid lg:grid-cols-3 gap-8">
            {/* Main content */}
            <div className="lg:col-span-2 space-y-6">
              {/* Vehicle card */}
              {order.vehicules?.[0] && (
                <Card variant="elevated">
                  <div className="flex gap-6">
                    <div className="w-40 h-28 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                      <img
                        src={getImageUrl(order.vehicules[0].pictures?.[0])}
                        alt={`${order.vehicules[0].brand} ${order.vehicules[0].model}`}
                        className="w-full h-full object-cover"
                      />
                    </div>
                    <div className="flex-1">
                      <h3 className="text-lg font-bold text-gray-900 mb-2">
                        {order.vehicules[0].brand} {order.vehicules[0].model}
                      </h3>
                      <div className="grid grid-cols-3 gap-4 text-sm">
                        <div className="flex items-center gap-2 text-gray-600">
                          <Calendar className="w-4 h-4" />
                          {order.vehicules[0].year ? getYearFromDate(order.vehicules[0].year) : '-'}
                        </div>
                        <div className="flex items-center gap-2 text-gray-600">
                          <Gauge className="w-4 h-4" />
                          {order.vehicules[0].mileage?.toLocaleString('fr-FR') ?? '-'} km
                        </div>
                        <div className="flex items-center gap-2 text-gray-600">
                          <Fuel className="w-4 h-4" />
                          {order.vehicules[0].fuelType ?? '-'}
                        </div>
                      </div>
                      <Link
                        to={`/vehicules/${order.vehicules[0].id}`}
                        className="mt-3 inline-block text-sm text-[#FF6B00] hover:underline"
                      >
                        Voir le véhicule →
                      </Link>
                    </div>
                  </div>
                </Card>
              )}

              {/* Order details */}
              <Card variant="elevated">
                <h3 className="font-bold text-gray-900 mb-4">Détails de la commande</h3>
                <dl className="space-y-3">
                  <div className="flex justify-between">
                    <dt className="text-gray-600">Date de commande</dt>
                    <dd className="font-medium">
                      {format(new Date(order.createdAt), 'dd MMMM yyyy à HH:mm', { locale: fr })}
                    </dd>
                  </div>
                  {order.deliveryAdress && (
                    <div className="pt-3 border-t border-gray-100">
                      <dt className="text-gray-600 mb-2">Adresse de livraison</dt>
                      <dd className="text-gray-900">
                        {order.deliveryAdress}<br />
                        {order.deliveryPostalCode} {order.deliveryCity}<br />
                        {order.deliveryCountry}
                      </dd>
                    </div>
                  )}
                </dl>
              </Card>
            </div>

            {/* Sidebar */}
            <div className="space-y-6">
              {/* Price summary */}
              <Card variant="elevated">
                <h3 className="font-bold text-gray-900 mb-4">Récapitulatif</h3>
                {order.vehicules?.[0] && (
                  <div className="space-y-3 pb-4 border-b border-gray-100">
                    <div className="flex justify-between text-gray-600">
                      <span>Prix du véhicule</span>
                      <span>{formatPrice(order.vehicules[0].salePrice)} €</span>
                    </div>
                  </div>
                )}
                <div className="flex justify-between items-baseline pt-4">
                  <span className="text-gray-900 font-semibold">Total</span>
                  <span className="text-2xl font-black text-[#FF6B00]">
                    {formatPrice(order.totalPrice)} €
                  </span>
                </div>
              </Card>

              {/* Actions */}
              {order.orderStatus === 'pending' && (
                <div className="space-y-3">
                  <Button
                    fullWidth
                    size="lg"
                    onClick={() => checkoutMutation.mutate()}
                    isLoading={checkoutMutation.isPending}
                    leftIcon={<CreditCard className="w-5 h-5" />}
                  >
                    Payer maintenant
                  </Button>
                  <Button
                    fullWidth
                    variant="ghost"
                    onClick={() => {
                      if (window.confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) {
                        cancelMutation.mutate();
                      }
                    }}
                    isLoading={cancelMutation.isPending}
                    leftIcon={<XCircle className="w-5 h-5" />}
                  >
                    Annuler la commande
                  </Button>
                </div>
              )}

              {order.orderStatus === 'paid' && (
                <Card variant="outlined" className="bg-green-50 border-green-200 text-center">
                  <p className="text-green-700 font-medium">
                    Paiement effectué. Notre équipe vous contactera bientôt.
                  </p>
                </Card>
              )}

              {order.orderStatus === 'cancelled' && (
                <Card variant="outlined" className="bg-red-50 border-red-200 text-center">
                  <p className="text-red-700 font-medium">
                    Cette commande a été annulée.
                  </p>
                </Card>
              )}
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
}

export default OrderDetailPage;
