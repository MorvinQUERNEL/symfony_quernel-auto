import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { Users, Car, ShoppingBag, TrendingUp, ArrowRight } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Card, Button, StatusBadge, Spinner } from '@/components/ui';
import { adminApi } from '@/api';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

export function AdminDashboardPage() {
  const { data: stats, isLoading } = useQuery({
    queryKey: ['admin', 'stats'],
    queryFn: () => adminApi.getStats(),
  });

  const statCards = [
    {
      icon: <Users className="w-6 h-6" />,
      label: 'Utilisateurs',
      value: stats?.totalUsers || 0,
      color: 'blue',
      link: '/admin/users',
    },
    {
      icon: <Car className="w-6 h-6" />,
      label: 'Véhicules',
      value: stats?.totalVehicules || 0,
      color: 'green',
      link: '/admin/vehicules',
    },
    {
      icon: <ShoppingBag className="w-6 h-6" />,
      label: 'Commandes',
      value: stats?.totalOrders || 0,
      color: 'orange',
      link: '/admin/orders',
    },
    {
      icon: <TrendingUp className="w-6 h-6" />,
      label: 'Revenus',
      value: `${(stats?.revenue || 0).toLocaleString('fr-FR')} €`,
      color: 'purple',
    },
  ];

  const colorClasses = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-green-100 text-green-600',
    orange: 'bg-orange-100 text-orange-600',
    purple: 'bg-purple-100 text-purple-600',
  };

  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen py-8">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between mb-8">
            <div>
              <h1 className="text-3xl font-black text-gray-900">Administration</h1>
              <p className="text-gray-600 mt-1">
                Tableau de bord et gestion du site
              </p>
            </div>
          </div>

          {isLoading ? (
            <div className="flex justify-center py-20">
              <Spinner size="xl" />
            </div>
          ) : (
            <>
              {/* Stats grid */}
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {statCards.map((stat) => (
                  <Card key={stat.label} variant="elevated" hover={!!stat.link}>
                    {stat.link ? (
                      <Link to={stat.link} className="block">
                        <div className="flex items-center gap-4">
                          <div
                            className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                              colorClasses[stat.color as keyof typeof colorClasses]
                            }`}
                          >
                            {stat.icon}
                          </div>
                          <div>
                            <p className="text-sm text-gray-500">{stat.label}</p>
                            <p className="text-2xl font-bold text-gray-900">
                              {stat.value}
                            </p>
                          </div>
                        </div>
                      </Link>
                    ) : (
                      <div className="flex items-center gap-4">
                        <div
                          className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                            colorClasses[stat.color as keyof typeof colorClasses]
                          }`}
                        >
                          {stat.icon}
                        </div>
                        <div>
                          <p className="text-sm text-gray-500">{stat.label}</p>
                          <p className="text-2xl font-bold text-gray-900">
                            {stat.value}
                          </p>
                        </div>
                      </div>
                    )}
                  </Card>
                ))}
              </div>

              {/* Quick actions */}
              <div className="grid lg:grid-cols-2 gap-8">
                {/* Pending orders */}
                <Card variant="elevated">
                  <div className="flex items-center justify-between mb-6">
                    <h2 className="text-lg font-bold text-gray-900">
                      Commandes en attente
                    </h2>
                    <span className="px-3 py-1 text-sm font-semibold bg-yellow-100 text-yellow-700 rounded-full">
                      {stats?.pendingOrders || 0}
                    </span>
                  </div>

                  {stats?.recentOrders && stats.recentOrders.length > 0 ? (
                    <div className="space-y-4">
                      {stats.recentOrders.slice(0, 5).map((order) => (
                        <div
                          key={order.id}
                          className="flex items-center justify-between py-3 border-b border-gray-100 last:border-0"
                        >
                          <div>
                            <p className="font-medium text-gray-900">
                              {order.vehicule.brand} {order.vehicule.model}
                            </p>
                            <p className="text-sm text-gray-500">
                              {order.user.firstname} {order.user.lastname} •{' '}
                              {format(new Date(order.createdAt), 'dd MMM', {
                                locale: fr,
                              })}
                            </p>
                          </div>
                          <div className="text-right">
                            <StatusBadge status={order.status} size="sm" />
                            <p className="text-sm font-semibold text-gray-900 mt-1">
                              {order.totalPrice.toLocaleString('fr-FR')} €
                            </p>
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p className="text-gray-500 text-center py-8">
                      Aucune commande récente
                    </p>
                  )}

                  <div className="mt-6 pt-4 border-t border-gray-100">
                    <Link to="/admin/orders">
                      <Button variant="ghost" fullWidth rightIcon={<ArrowRight className="w-4 h-4" />}>
                        Voir toutes les commandes
                      </Button>
                    </Link>
                  </div>
                </Card>

                {/* Quick links */}
                <Card variant="elevated">
                  <h2 className="text-lg font-bold text-gray-900 mb-6">
                    Actions rapides
                  </h2>

                  <div className="space-y-3">
                    <Link to="/admin/vehicules" className="block">
                      <Card variant="outlined" hover padding="sm">
                        <div className="flex items-center gap-4">
                          <div className="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                            <Car className="w-5 h-5" />
                          </div>
                          <div className="flex-1">
                            <p className="font-medium text-gray-900">
                              Gérer les véhicules
                            </p>
                            <p className="text-sm text-gray-500">
                              Ajouter, modifier ou supprimer
                            </p>
                          </div>
                          <ArrowRight className="w-5 h-5 text-gray-400" />
                        </div>
                      </Card>
                    </Link>

                    <Link to="/admin/orders" className="block">
                      <Card variant="outlined" hover padding="sm">
                        <div className="flex items-center gap-4">
                          <div className="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                            <ShoppingBag className="w-5 h-5" />
                          </div>
                          <div className="flex-1">
                            <p className="font-medium text-gray-900">
                              Traiter les commandes
                            </p>
                            <p className="text-sm text-gray-500">
                              Valider et suivre les commandes
                            </p>
                          </div>
                          <ArrowRight className="w-5 h-5 text-gray-400" />
                        </div>
                      </Card>
                    </Link>

                    <Link to="/admin/users" className="block">
                      <Card variant="outlined" hover padding="sm">
                        <div className="flex items-center gap-4">
                          <div className="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                            <Users className="w-5 h-5" />
                          </div>
                          <div className="flex-1">
                            <p className="font-medium text-gray-900">
                              Gérer les utilisateurs
                            </p>
                            <p className="text-sm text-gray-500">
                              Voir et gérer les comptes
                            </p>
                          </div>
                          <ArrowRight className="w-5 h-5 text-gray-400" />
                        </div>
                      </Card>
                    </Link>
                  </div>
                </Card>
              </div>
            </>
          )}
        </div>
      </div>
    </Layout>
  );
}

export default AdminDashboardPage;
