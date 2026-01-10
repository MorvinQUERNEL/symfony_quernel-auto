import { Link } from 'react-router-dom';
import { Mail, Phone, MapPin, Facebook, Instagram, Linkedin } from 'lucide-react';

export function Footer() {
  const currentYear = new Date().getFullYear();

  const footerLinks = {
    company: [
      { label: 'Notre processus', href: '/processus' },
      { label: 'Contact', href: '/contact' },
    ],
    vehicles: [
      { label: 'Tous les véhicules', href: '/vehicules' },
      { label: 'Import', href: '/vehicules?type=import' },
      { label: 'Export', href: '/vehicules?type=export' },
    ],
    legal: [
      { label: 'Mentions légales', href: '/mentions-legales' },
      { label: 'CGV', href: '/cgv' },
      { label: 'Politique de confidentialité', href: '/politique-confidentialite' },
    ],
  };

  return (
    <footer className="relative bg-gray-900 text-white overflow-hidden">
      {/* Background pattern */}
      <div className="absolute inset-0 opacity-5">
        <div
          className="absolute inset-0"
          style={{
            backgroundImage: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`,
          }}
        />
      </div>

      {/* Top accent line */}
      <div className="h-1 bg-gradient-to-r from-[#FF6B00] via-[#FFAA00] to-[#FF6B00]" />

      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Main footer content */}
        <div className="py-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12">
          {/* Brand column */}
          <div className="lg:col-span-2">
            {/* Logo */}
            <Link to="/" className="inline-flex items-center gap-3 group">
              <div className="relative w-10 h-10 flex items-center justify-center">
                <div className="absolute inset-0 bg-gradient-to-br from-[#FF6B00] to-[#FF8533] rounded-xl rotate-6 group-hover:rotate-12 transition-transform duration-300" />
                <span className="relative text-white font-black text-xl">Q</span>
              </div>
              <div>
                <span className="text-xl font-black tracking-tight">QUERNEL</span>
                <span className="text-xl font-light text-[#FF6B00] ml-1">AUTO</span>
              </div>
            </Link>

            <p className="mt-6 text-gray-400 text-sm leading-relaxed max-w-sm">
              Votre partenaire de confiance pour l'import et l'export de véhicules.
              Qualité, transparence et service personnalisé depuis plus de 10 ans.
            </p>

            {/* Contact info */}
            <div className="mt-8 space-y-3">
              <a
                href="tel:+33123456789"
                className="flex items-center gap-3 text-gray-400 hover:text-white transition-colors"
              >
                <Phone className="w-4 h-4 text-[#FF6B00]" />
                <span className="text-sm">+33 1 23 45 67 89</span>
              </a>
              <a
                href="mailto:contact@quernel-auto.fr"
                className="flex items-center gap-3 text-gray-400 hover:text-white transition-colors"
              >
                <Mail className="w-4 h-4 text-[#FF6B00]" />
                <span className="text-sm">contact@quernel-auto.fr</span>
              </a>
              <div className="flex items-center gap-3 text-gray-400">
                <MapPin className="w-4 h-4 text-[#FF6B00] flex-shrink-0" />
                <span className="text-sm">123 Avenue des Champs-Élysées, 75008 Paris</span>
              </div>
            </div>
          </div>

          {/* Links columns */}
          <div>
            <h4 className="text-sm font-bold uppercase tracking-wider text-white mb-4">
              Entreprise
            </h4>
            <ul className="space-y-3">
              {footerLinks.company.map((link) => (
                <li key={link.href}>
                  <Link
                    to={link.href}
                    className="text-sm text-gray-400 hover:text-[#FF6B00] transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="text-sm font-bold uppercase tracking-wider text-white mb-4">
              Véhicules
            </h4>
            <ul className="space-y-3">
              {footerLinks.vehicles.map((link) => (
                <li key={link.href}>
                  <Link
                    to={link.href}
                    className="text-sm text-gray-400 hover:text-[#FF6B00] transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="text-sm font-bold uppercase tracking-wider text-white mb-4">
              Légal
            </h4>
            <ul className="space-y-3">
              {footerLinks.legal.map((link) => (
                <li key={link.href}>
                  <Link
                    to={link.href}
                    className="text-sm text-gray-400 hover:text-[#FF6B00] transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="py-6 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-sm text-gray-500">
            © {currentYear} Quernel Auto. Tous droits réservés.
          </p>

          {/* Social links */}
          <div className="flex items-center gap-4">
            <a
              href="https://facebook.com"
              target="_blank"
              rel="noopener noreferrer"
              className="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
              aria-label="Facebook"
            >
              <Facebook className="w-5 h-5" />
            </a>
            <a
              href="https://instagram.com"
              target="_blank"
              rel="noopener noreferrer"
              className="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
              aria-label="Instagram"
            >
              <Instagram className="w-5 h-5" />
            </a>
            <a
              href="https://linkedin.com"
              target="_blank"
              rel="noopener noreferrer"
              className="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
              aria-label="LinkedIn"
            >
              <Linkedin className="w-5 h-5" />
            </a>
          </div>
        </div>
      </div>
    </footer>
  );
}

export default Footer;
