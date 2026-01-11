import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { ArrowLeft, Plus, Edit, Trash2, Car } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Card, Button, StatusBadge, Spinner } from '@/components/ui';
import { vehiculesApi } from '@/api';
import { getImageUrl, getYearFromDate, formatPrice } from '@/types';

export function AdminVehiculesPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['vehicules', { limit: 100 }],
    queryFn: () => vehiculesApi.list({ limit: 100 }),
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
              <h1 className="text-3xl font-black text-gray-900">Véhicules</h1>
              <p className="text-gray-600 mt-1">
                {data?.meta.total || 0} véhicule(s) au catalogue
              </p>
            </div>
            <Button leftIcon={<Plus className="w-5 h-5" />}>
              Ajouter un véhicule
            </Button>
          </div>

          {isLoading ? (
            <div className="flex justify-center py-20">
              <Spinner size="xl" />
            </div>
          ) : data?.data?.length === 0 ? (
            <Card variant="outlined" className="text-center py-12">
              <Car className="w-16 h-16 mx-auto mb-4 text-gray-300" />
              <h3 className="text-lg font-semibold text-gray-900 mb-2">
                Aucun véhicule
              </h3>
              <p className="text-gray-500 mb-6">
                Commencez par ajouter votre premier véhicule au catalogue.
              </p>
              <Button leftIcon={<Plus className="w-5 h-5" />}>
                Ajouter un véhicule
              </Button>
            </Card>
          ) : (
            <Card variant="elevated" padding="none">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Véhicule
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Année
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Prix
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Kilométrage
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Statut
                      </th>
                      <th className="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {data?.data?.map((vehicule) => (
                      <tr key={vehicule.id} className="hover:bg-gray-50 transition-colors">
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-4">
                            <div className="w-16 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                              <img
                                src={getImageUrl(vehicule.pictures[0])}
                                alt={`${vehicule.brand} ${vehicule.model}`}
                                className="w-full h-full object-cover"
                              />
                            </div>
                            <div>
                              <p className="font-medium text-gray-900">
                                {vehicule.brand} {vehicule.model}
                              </p>
                              <p className="text-sm text-gray-500">
                                {vehicule.fuelType} • {vehicule.trasmission}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4 text-gray-600">{getYearFromDate(vehicule.year)}</td>
                        <td className="px-6 py-4">
                          <span className="font-semibold text-gray-900">
                            {formatPrice(vehicule.salePrice)} €
                          </span>
                        </td>
                        <td className="px-6 py-4 text-gray-600">
                          {vehicule.mileage.toLocaleString('fr-FR')} km
                        </td>
                        <td className="px-6 py-4">
                          <StatusBadge status={vehicule.status} />
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex items-center justify-end gap-2">
                            <Link to={`/vehicules/${vehicule.id}`}>
                              <Button variant="ghost" size="sm">
                                Voir
                              </Button>
                            </Link>
                            <Button variant="ghost" size="sm" leftIcon={<Edit className="w-4 h-4" />}>
                              Modifier
                            </Button>
                            <Button variant="ghost" size="sm" className="text-red-600 hover:bg-red-50">
                              <Trash2 className="w-4 h-4" />
                            </Button>
                          </div>
                        </td>
                      </tr>
                    ))}
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

export default AdminVehiculesPage;
