import { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { clsx } from 'clsx';
import {
  Menu,
  X,
  User,
  LogOut,
  ShoppingBag,
  MessageSquare,
  Settings,
  Car,
  ChevronDown,
} from 'lucide-react';
import { useAuthStore, selectIsAdmin } from '@/stores';
import { Button, CountBadge } from '@/components/ui';
import { LanguageSwitcher } from '@/components/LanguageSwitcher';
import { useTranslation } from '@/i18n';

export function Navbar() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const location = useLocation();
  const t = useTranslation();

  const { user, isAuthenticated, logout } = useAuthStore();
  const isAdmin = useAuthStore(selectIsAdmin);

  const publicLinks = [
    { label: t.nav.home, href: '/' },
    { label: t.nav.vehicles, href: '/vehicules', icon: <Car className="w-4 h-4" /> },
    { label: t.nav.process, href: '/processus' },
    { label: t.nav.contact, href: '/contact' },
  ];

  const handleLogout = async () => {
    await logout();
    setIsUserMenuOpen(false);
  };

  const isActive = (href: string) => {
    if (href === '/') return location.pathname === '/';
    return location.pathname.startsWith(href);
  };

  return (
    <header className="fixed top-0 left-0 right-0 z-50">
      {/* Glass background */}
      <div className="absolute inset-0 bg-white/80 backdrop-blur-xl border-b border-gray-100" />

      <nav className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-20">
          {/* Logo */}
          <Link
            to="/"
            className="flex items-center gap-3 group"
          >
            {/* Logo mark */}
            <div className="relative w-10 h-10 flex items-center justify-center">
              <div className="absolute inset-0 bg-gradient-to-br from-[#FF6B00] to-[#FF8533] rounded-xl rotate-6 group-hover:rotate-12 transition-transform duration-300" />
              <span className="relative text-white font-black text-xl">Q</span>
            </div>
            {/* Logo text */}
            <div className="hidden sm:block">
              <span className="text-xl font-black text-gray-900 tracking-tight">
                QUERNEL
              </span>
              <span className="text-xl font-light text-[#FF6B00] tracking-tight ml-1">
                AUTO
              </span>
            </div>
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden lg:flex items-center gap-1">
            {publicLinks.map((link) => (
              <Link
                key={link.href}
                to={link.href}
                className={clsx(
                  'flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200',
                  isActive(link.href)
                    ? 'text-[#FF6B00] bg-[#FF6B00]/10'
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                )}
              >
                {link.icon}
                {link.label}
              </Link>
            ))}
          </div>

          {/* Actions */}
          <div className="flex items-center gap-3">
            {isAuthenticated ? (
              <>
                {/* Messages icon with badge */}
                <Link
                  to="/messages"
                  className="relative p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                >
                  <MessageSquare className="w-5 h-5" />
                  <div className="absolute -top-1 -right-1">
                    <CountBadge count={0} />
                  </div>
                </Link>

                {/* User menu */}
                <div className="relative">
                  <button
                    onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
                    className={clsx(
                      'flex items-center gap-2 px-3 py-2 rounded-xl transition-all duration-200',
                      'hover:bg-gray-100',
                      isUserMenuOpen && 'bg-gray-100'
                    )}
                  >
                    {/* Avatar */}
                    <div className="w-8 h-8 rounded-full bg-gradient-to-br from-[#FF6B00] to-[#FFAA00] flex items-center justify-center">
                      <span className="text-white text-sm font-bold">
                        {user?.firstname?.[0]?.toUpperCase() || 'U'}
                      </span>
                    </div>
                    <span className="hidden sm:block text-sm font-medium text-gray-700 max-w-[120px] truncate">
                      {user?.firstname}
                    </span>
                    <ChevronDown
                      className={clsx(
                        'w-4 h-4 text-gray-400 transition-transform duration-200',
                        isUserMenuOpen && 'rotate-180'
                      )}
                    />
                  </button>

                  {/* Dropdown menu */}
                  {isUserMenuOpen && (
                    <>
                      {/* Backdrop - closes menu when clicking outside */}
                      <div
                        className="fixed inset-0"
                        onClick={() => setIsUserMenuOpen(false)}
                      />

                      {/* Menu - positioned above backdrop */}
                      <div className="absolute right-0 mt-2 w-64 z-50 py-2 bg-white rounded-2xl shadow-xl border border-gray-100 animate-in fade-in slide-in-from-top-2 duration-200">
                        {/* User info */}
                        <div className="px-4 py-3 border-b border-gray-100">
                          <p className="text-sm font-semibold text-gray-900">
                            {user?.firstname} {user?.lastname}
                          </p>
                          <p className="text-xs text-gray-500 truncate">
                            {user?.email}
                          </p>
                        </div>

                        {/* Links */}
                        <div className="py-2">
                          <Link
                            to="/profile"
                            onClick={() => setIsUserMenuOpen(false)}
                            className="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                          >
                            <User className="w-4 h-4" />
                            {t.nav.myProfile}
                          </Link>
                          <Link
                            to="/orders"
                            onClick={() => setIsUserMenuOpen(false)}
                            className="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                          >
                            <ShoppingBag className="w-4 h-4" />
                            {t.nav.myOrders}
                          </Link>
                          <Link
                            to="/messages"
                            onClick={() => setIsUserMenuOpen(false)}
                            className="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                          >
                            <MessageSquare className="w-4 h-4" />
                            {t.nav.messages}
                          </Link>

                          {isAdmin && (
                            <>
                              <div className="my-2 border-t border-gray-100" />
                              <Link
                                to="/admin"
                                onClick={() => setIsUserMenuOpen(false)}
                                className="flex items-center gap-3 px-4 py-2 text-sm text-[#FF6B00] font-medium hover:bg-[#FF6B00]/5 transition-colors"
                              >
                                <Settings className="w-4 h-4" />
                                {t.nav.admin}
                              </Link>
                            </>
                          )}
                        </div>

                        {/* Logout */}
                        <div className="pt-2 border-t border-gray-100">
                          <button
                            onClick={handleLogout}
                            className="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                          >
                            <LogOut className="w-4 h-4" />
                            {t.nav.logout}
                          </button>
                        </div>
                      </div>
                    </>
                  )}
                </div>
              </>
            ) : (
              <div className="flex items-center gap-3">
                <Link to="/login">
                  <Button variant="ghost" size="sm">
                    {t.nav.login}
                  </Button>
                </Link>
                <Link to="/register" className="hidden sm:block">
                  <Button variant="primary" size="sm">
                    {t.nav.register}
                  </Button>
                </Link>
              </div>
            )}

            {/* Language Switcher */}
            <LanguageSwitcher />

            {/* Mobile menu button */}
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="lg:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
            >
              {isMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>
        </div>

        {/* Mobile menu */}
        {isMenuOpen && (
          <div className="lg:hidden py-4 border-t border-gray-100 animate-in slide-in-from-top-2 duration-200">
            <div className="flex flex-col gap-1">
              {publicLinks.map((link) => (
                <Link
                  key={link.href}
                  to={link.href}
                  onClick={() => setIsMenuOpen(false)}
                  className={clsx(
                    'flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors',
                    isActive(link.href)
                      ? 'text-[#FF6B00] bg-[#FF6B00]/10'
                      : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                  )}
                >
                  {link.icon}
                  {link.label}
                </Link>
              ))}

              {!isAuthenticated && (
                <div className="pt-4 mt-4 border-t border-gray-100">
                  <Link to="/register" onClick={() => setIsMenuOpen(false)}>
                    <Button variant="primary" fullWidth>
                      {t.nav.register}
                    </Button>
                  </Link>
                </div>
              )}
            </div>
          </div>
        )}
      </nav>
    </header>
  );
}

export default Navbar;
