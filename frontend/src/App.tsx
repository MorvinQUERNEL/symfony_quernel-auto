import { useEffect, Suspense, lazy } from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useAuthStore } from '@/stores';
import { LoadingOverlay } from '@/components/ui';
import { ProtectedRoute } from '@/components/ProtectedRoute';

// Eager-loaded pages (critical path)
import { HomePage } from '@/pages/Home';
import { LoginPage, RegisterPage } from '@/pages/auth';
import { VehiculesListPage, VehiculeDetailPage } from '@/pages/vehicules';

// Lazy-loaded pages
const ProfilePage = lazy(() => import('@/pages/profile/Profile'));
const OrdersPage = lazy(() => import('@/pages/orders/OrdersList'));
const OrderDetailPage = lazy(() => import('@/pages/orders/OrderDetail'));
const NewOrderPage = lazy(() => import('@/pages/orders/NewOrder'));
const MessagesPage = lazy(() => import('@/pages/chat/Messages'));
const ContactPage = lazy(() => import('@/pages/Contact'));
const ProcessusPage = lazy(() => import('@/pages/Processus'));

// Legal pages
const MentionsLegalesPage = lazy(() => import('@/pages/legal/MentionsLegales'));
const CGVPage = lazy(() => import('@/pages/legal/CGV'));
const PolitiqueConfidentialitePage = lazy(() => import('@/pages/legal/PolitiqueConfidentialite'));

// Payment pages
const PaymentSuccessPage = lazy(() => import('@/pages/payment/PaymentSuccess'));
const PaymentCancelPage = lazy(() => import('@/pages/payment/PaymentCancel'));

// Admin pages
const AdminDashboardPage = lazy(() => import('@/pages/admin/Dashboard'));
const AdminUsersPage = lazy(() => import('@/pages/admin/Users'));
const AdminOrdersPage = lazy(() => import('@/pages/admin/Orders'));
const AdminVehiculesPage = lazy(() => import('@/pages/admin/Vehicules'));

// Create Query Client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5, // 5 minutes
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

// Auth initializer component
function AuthInitializer({ children }: { children: React.ReactNode }) {
  const { checkAuth, isLoading } = useAuthStore();

  useEffect(() => {
    checkAuth();
  }, [checkAuth]);

  if (isLoading) {
    return <LoadingOverlay message="Chargement..." />;
  }

  return <>{children}</>;
}

// Suspense fallback
function PageLoader() {
  return <LoadingOverlay message="Chargement de la page..." />;
}

// Not found page
function NotFoundPage() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="text-center">
        <h1 className="text-6xl font-black text-gray-900 mb-4">404</h1>
        <p className="text-xl text-gray-600 mb-8">Page non trouvée</p>
        <a
          href="/"
          className="inline-flex items-center justify-center px-6 py-3 bg-[#FF6B00] text-white font-semibold rounded-xl hover:bg-[#CC5500] transition-colors"
        >
          Retour à l'accueil
        </a>
      </div>
    </div>
  );
}

export function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <AuthInitializer>
          <Suspense fallback={<PageLoader />}>
            <Routes>
              {/* Public routes */}
              <Route path="/" element={<HomePage />} />
              <Route path="/login" element={<LoginPage />} />
              <Route path="/register" element={<RegisterPage />} />
              <Route path="/vehicules" element={<VehiculesListPage />} />
              <Route path="/vehicules/:id" element={<VehiculeDetailPage />} />
              <Route path="/contact" element={<ContactPage />} />
              <Route path="/processus" element={<ProcessusPage />} />

              {/* Legal routes */}
              <Route path="/mentions-legales" element={<MentionsLegalesPage />} />
              <Route path="/cgv" element={<CGVPage />} />
              <Route path="/politique-confidentialite" element={<PolitiqueConfidentialitePage />} />

              {/* Payment routes */}
              <Route path="/paiement/succes" element={<PaymentSuccessPage />} />
              <Route path="/paiement/annulation" element={<PaymentCancelPage />} />

              {/* Protected routes - User */}
              <Route
                path="/profile"
                element={
                  <ProtectedRoute>
                    <ProfilePage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/orders"
                element={
                  <ProtectedRoute>
                    <OrdersPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/orders/new"
                element={
                  <ProtectedRoute>
                    <NewOrderPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/orders/:id"
                element={
                  <ProtectedRoute>
                    <OrderDetailPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/messages"
                element={
                  <ProtectedRoute>
                    <MessagesPage />
                  </ProtectedRoute>
                }
              />

              {/* Admin routes */}
              <Route
                path="/admin"
                element={
                  <ProtectedRoute requireAdmin>
                    <AdminDashboardPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/admin/users"
                element={
                  <ProtectedRoute requireSuperAdmin>
                    <AdminUsersPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/admin/orders"
                element={
                  <ProtectedRoute requireAdmin>
                    <AdminOrdersPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/admin/vehicules"
                element={
                  <ProtectedRoute requireAdmin>
                    <AdminVehiculesPage />
                  </ProtectedRoute>
                }
              />

              {/* 404 */}
              <Route path="*" element={<NotFoundPage />} />
            </Routes>
          </Suspense>
        </AuthInitializer>
      </BrowserRouter>
    </QueryClientProvider>
  );
}

export default App;
