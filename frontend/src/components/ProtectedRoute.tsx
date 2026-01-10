import { Navigate, useLocation } from 'react-router-dom';
import { useAuthStore, selectIsAdmin, selectIsSuperAdmin } from '@/stores';
import { LoadingOverlay } from '@/components/ui';

interface ProtectedRouteProps {
  children: React.ReactNode;
  requireAuth?: boolean;
  requireAdmin?: boolean;
  requireSuperAdmin?: boolean;
}

export function ProtectedRoute({
  children,
  requireAuth = true,
  requireAdmin = false,
  requireSuperAdmin = false,
}: ProtectedRouteProps) {
  const location = useLocation();
  const { isAuthenticated, isLoading } = useAuthStore();
  const isAdmin = useAuthStore(selectIsAdmin);
  const isSuperAdmin = useAuthStore(selectIsSuperAdmin);

  // Show loading while checking auth
  if (isLoading) {
    return <LoadingOverlay message="Vérification de l'authentification..." />;
  }

  // Redirect to login if auth required but not authenticated
  if (requireAuth && !isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  // Check admin role
  if (requireAdmin && !isAdmin) {
    return <Navigate to="/" replace />;
  }

  // Check super admin role
  if (requireSuperAdmin && !isSuperAdmin) {
    return <Navigate to="/" replace />;
  }

  return <>{children}</>;
}

export default ProtectedRoute;
