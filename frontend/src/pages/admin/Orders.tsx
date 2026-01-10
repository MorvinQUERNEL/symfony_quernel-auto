import { useQuery } from '@tanstack/react-query';
import { ArrowLeft, Eye } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Layout } from '@/components/layout';
import { Card, Button, StatusBadge, Spinner } from '@/components/ui';
import { adminApi } from '@/api';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

export function AdminOrdersPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['admin', 'orders'],
    queryFn: () => adminApi.getOrders(),
  });

  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen py-8">
        <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Back link */}
          <Link
            to="/admin"
            className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6"
          >
            <ArrowLeft className="w-5 h-5" />
            Retour au tableau de bord
          </Link>

          <div className="flex items-center justify-between mb-8">
            <div>
              <h1 className="text-3xl font-black text-gray-900">Commandes</h1>
              <p className="text-gray-600 mt-1">
                {data?.total || 0} commande(s) au total
              </p>
            </div>
          </div>

          {isLoading ? (
            <div className="flex justify-center py-20">
              <Spinner size="xl" />
            </div>
          ) : (
            <Card variant="elevated" padding="none">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Commande
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Client
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Véhicule
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Statut
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Montant
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Date
                      </th>
                      <th className="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {data?.data.map((order) => {
                      const vehicule = order.vehicules?.[0];
                      return (
                        <tr key={order.id} className="hover:bg-gray-50 transition-colors">
                          <td className="px-6 py-4">
                            <span className="font-medium text-gray-900">
                              #{order.id}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            <div>
                              <p className="font-medium text-gray-900">
                                {order.users?.firstname} {order.users?.lastname}
                              </p>
                              <p className="text-sm text-gray-500">
                                {order.users?.email}
                              </p>
                            </div>
                          </td>
                          <td className="px-6 py-4">
                            <p className="text-gray-900">
                              {vehicule?.brand} {vehicule?.model}
                            </p>
                            <p className="text-sm text-gray-500">
                              {vehicule?.year ? new Date(vehicule.year).getFullYear() : '-'}
                            </p>
                          </td>
                          <td className="px-6 py-4">
                            <StatusBadge status={order.orderStatus} />
                          </td>
                          <td className="px-6 py-4">
                            <span className="font-semibold text-gray-900">
                              {(order.totalPrice / 100).toLocaleString('fr-FR')} €
                            </span>
                          </td>
                          <td className="px-6 py-4 text-gray-600">
                            {format(new Date(order.createdAt), 'dd MMM yyyy', {
                              locale: fr,
                            })}
                          </td>
                          <td className="px-6 py-4 text-right">
                            <Link to={`/orders/${order.id}`}>
                              <Button variant="ghost" size="sm" leftIcon={<Eye className="w-4 h-4" />}>
                                Voir
                              </Button>
                            </Link>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </Card>
          )}
        </div>
      </div>
    </Layout>
  );
}

export default AdminOrdersPage;
