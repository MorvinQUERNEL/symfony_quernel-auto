import { useQuery } from '@tanstack/react-query';
import { ArrowLeft, Mail } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Layout } from '@/components/layout';
import { Card, Badge, Spinner } from '@/components/ui';
import { adminApi } from '@/api';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

export function AdminUsersPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['admin', 'users'],
    queryFn: () => adminApi.getUsers(),
  });

  const getRoleBadge = (roles: string[]) => {
    if (roles.includes('ROLE_SUPER_ADMIN')) {
      return <Badge variant="error">Super Admin</Badge>;
    }
    if (roles.includes('ROLE_ADMIN')) {
      return <Badge variant="warning">Admin</Badge>;
    }
    return <Badge variant="default">Utilisateur</Badge>;
  };

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
              <h1 className="text-3xl font-black text-gray-900">Utilisateurs</h1>
              <p className="text-gray-600 mt-1">
                {data?.total || 0} utilisateur(s) inscrit(s)
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
                        Utilisateur
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Email
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Rôle
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Inscrit le
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Vérifié
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {data?.data.map((user) => (
                      <tr key={user.id} className="hover:bg-gray-50 transition-colors">
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-full bg-gradient-to-br from-[#FF6B00] to-[#FFAA00] flex items-center justify-center">
                              <span className="text-white font-bold">
                                {user.firstname?.[0]?.toUpperCase() || 'U'}
                              </span>
                            </div>
                            <div>
                              <p className="font-medium text-gray-900">
                                {user.firstname} {user.lastname}
                              </p>
                              {user.phone && (
                                <p className="text-sm text-gray-500">{user.phone}</p>
                              )}
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-2 text-gray-600">
                            <Mail className="w-4 h-4" />
                            {user.email}
                          </div>
                        </td>
                        <td className="px-6 py-4">{getRoleBadge(user.roles)}</td>
                        <td className="px-6 py-4 text-gray-600">
                          {format(new Date(user.createdAt), 'dd MMM yyyy', {
                            locale: fr,
                          })}
                        </td>
                        <td className="px-6 py-4">
                          {user.isVerified ? (
                            <Badge variant="success" dot>
                              Vérifié
                            </Badge>
                          ) : (
                            <Badge variant="warning" dot>
                              Non vérifié
                            </Badge>
                          )}
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

export default AdminUsersPage;
