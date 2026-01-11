import { useState, useEffect } from 'react';
import { X, Info } from 'lucide-react';
import { clsx } from 'clsx';
import { useLanguage } from '@/i18n';

const STORAGE_KEY = 'quernel-auto-demo-popup-dismissed';

export function DemoPopup() {
  const [isVisible, setIsVisible] = useState(false);
  const { language } = useLanguage();

  useEffect(() => {
    // Check if popup was already dismissed in this session
    const dismissed = sessionStorage.getItem(STORAGE_KEY);
    if (!dismissed) {
      // Show popup after a short delay
      const timer = setTimeout(() => {
        setIsVisible(true);
      }, 1000);
      return () => clearTimeout(timer);
    }
  }, []);

  const handleDismiss = () => {
    setIsVisible(false);
    sessionStorage.setItem(STORAGE_KEY, 'true');
  };

  if (!isVisible) return null;

  return (
    <>
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] animate-in fade-in duration-300"
        onClick={handleDismiss}
      />

      {/* Popup */}
      <div
        className={clsx(
          'fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[101]',
          'w-[90%] max-w-md',
          'bg-white rounded-2xl shadow-2xl',
          'animate-in fade-in zoom-in-95 duration-300'
        )}
      >
        {/* Header */}
        <div className="relative bg-gradient-to-r from-[#FF6B00] to-[#FF8533] px-6 py-5 rounded-t-2xl">
          <button
            onClick={handleDismiss}
            className="absolute top-4 right-4 p-1 rounded-full bg-white/20 hover:bg-white/30 transition-colors"
            aria-label="Close"
          >
            <X className="w-5 h-5 text-white" />
          </button>

          <div className="flex items-center gap-3">
            <div className="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
              <Info className="w-6 h-6 text-white" />
            </div>
            <div>
              <h2 className="text-xl font-bold text-white">
                {language === 'fr' ? 'Site de Démonstration' : 'Demo Website'}
              </h2>
              <p className="text-white/80 text-sm">
                {language === 'fr' ? 'Information importante' : 'Important Information'}
              </p>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="px-6 py-6">
          {/* French message */}
          <div className="mb-4">
            <div className="flex items-center gap-2 mb-2">
              <span className="text-lg">🇫🇷</span>
              <span className="font-semibold text-gray-900">Français</span>
            </div>
            <p className="text-gray-600 text-sm leading-relaxed">
              Ceci est un <span className="font-semibold text-[#FF6B00]">site de démonstration</span> créé par{' '}
              <span className="font-semibold">Quernel Morvin</span>.
              Les données présentées sont fictives et aucune transaction réelle ne peut être effectuée.
            </p>
          </div>

          <div className="border-t border-gray-100 my-4" />

          {/* English message */}
          <div>
            <div className="flex items-center gap-2 mb-2">
              <span className="text-lg">🇬🇧</span>
              <span className="font-semibold text-gray-900">English</span>
            </div>
            <p className="text-gray-600 text-sm leading-relaxed">
              This is a <span className="font-semibold text-[#FF6B00]">demo website</span> created by{' '}
              <span className="font-semibold">Quernel Morvin</span>.
              The data shown is fictional and no real transactions can be made.
            </p>
          </div>
        </div>

        {/* Footer */}
        <div className="px-6 pb-6">
          <button
            onClick={handleDismiss}
            className={clsx(
              'w-full py-3 rounded-xl font-semibold transition-all duration-200',
              'bg-gradient-to-r from-[#FF6B00] to-[#FF8533]',
              'text-white hover:from-[#FF8533] hover:to-[#FFAA00]',
              'shadow-lg shadow-[#FF6B00]/25 hover:shadow-[#FF6B00]/40'
            )}
          >
            {language === 'fr' ? "J'ai compris" : 'I understand'}
          </button>
        </div>
      </div>
    </>
  );
}

export default DemoPopup;
