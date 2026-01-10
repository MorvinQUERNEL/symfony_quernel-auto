import { type ReactNode } from 'react';
import { Navbar } from './Navbar';
import { Footer } from './Footer';

interface LayoutProps {
  children: ReactNode;
  showFooter?: boolean;
}

export function Layout({ children, showFooter = true }: LayoutProps) {
  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />

      {/* Main content - account for fixed navbar height */}
      <main className="flex-1 pt-20">
        {children}
      </main>

      {showFooter && <Footer />}
    </div>
  );
}

// Simple layout without navbar/footer for auth pages
export function AuthLayout({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 p-4">
      {/* Background decoration */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        {/* Top right blob */}
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-[#FF6B00]/10 rounded-full blur-3xl" />
        {/* Bottom left blob */}
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-[#FFAA00]/10 rounded-full blur-3xl" />
      </div>

      <div className="relative w-full max-w-md">
        {children}
      </div>
    </div>
  );
}

// Admin layout with sidebar
export function AdminLayout({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />
      <div className="flex-1 pt-20 bg-gray-50">
        {children}
      </div>
    </div>
  );
}

export default Layout;
