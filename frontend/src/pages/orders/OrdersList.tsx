import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { ShoppingBag, Eye } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Card, Button, StatusBadge, Spinner } from '@/components/ui';
import { ordersApi } from '@/api';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { getImageUrl } from '@/types';

export function OrdersListPage() {
  const { data: orders, isLoading, error } = useQuery({
    queryKey: ['orders', 'my'],
    queryFn: () => ordersApi.myOrders(),
  });

  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen py-12">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl font-black text-gray-900 mb-8">Mes commandes</h1>

          {isLoading ? (
            <div className="flex justify-center py-20">
              <Spinner size="xl" />
            </div>
          ) : error ? (
            <Card variant="outlined" className="text-center py-12">
              <p className="text-red-600">Erreur lors du chargement des commandes</p>
            </Card>
          ) : orders?.length === 0 ? (
            <Card variant="outlined" className="text-center py-12">
              <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <ShoppingBag className="w-8 h-8 text-gray-400" />
              </div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">
                Aucune commande
              </h3>
              <p className="text-gray-500 mb-6">
                Vous n'avez pas encore passé de commande.
              </p>
              <Link to="/vehicules">
                <Button>Voir les véhicules</Button>
              </Link>
            </Card>
          ) : (
            <div className="space-y-4">
              {orders?.map((order) => (
                <Card key={order.id} variant="elevated" hover className="group">
                  <div className="flex flex-col md:flex-row md:items-center gap-6">
                    {/* Vehicle image */}
                    <div className="w-full md:w-48 h-32 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                      <img
                        src={getImageUrl(order.vehicule.pictures[0])}
                        alt={`${order.vehicule.brand} ${order.vehicule.model}`}
                        className="w-full h-full object-cover"
                      />
                    </div>

                    {/* Order info */}
                    <div className="flex-1">
                      <div className="flex items-start justify-between mb-2">
                        <div>
                          <p className="text-sm text-gray-500">
                            Commande #{order.id}
                          </p>
                          <h3 className="text-lg font-bold text-gray-900">
                            {order.vehicule.brand} {order.vehicule.model}
                          </h3>
                        </div>
                        <StatusBadge status={order.status} />
                      </div>

                      <div className="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                        <span>
                          {format(new Date(order.createdAt), 'dd MMMM yyyy', { locale: fr })}
                        </span>
                        <span>•</span>
                        <span className="font-semibold text-[#FF6B00]">
                          {order.totalPrice.toLocaleString('fr-FR')} €
                        </span>
                      </div>

                      <Link to={`/orders/${order.id}`}>
                        <Button variant="outline" size="sm" leftIcon={<Eye className="w-4 h-4" />}>
                          Voir les détails
                        </Button>
                      </Link>
                    </div>
                  </div>
                </Card>
              ))}
            </div>
          )}
        </div>
      </div>
    </Layout>
  );
}

export default OrdersListPage;
