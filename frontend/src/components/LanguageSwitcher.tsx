import { useLanguage } from '@/i18n';
import { clsx } from 'clsx';

export function LanguageSwitcher() {
  const { language, setLanguage } = useLanguage();

  const toggleLanguage = () => {
    setLanguage(language === 'fr' ? 'en' : 'fr');
  };

  return (
    <button
      onClick={toggleLanguage}
      className={clsx(
        'flex items-center gap-1.5 px-3 py-1.5 rounded-lg',
        'text-sm font-medium transition-all duration-200',
        'border-2 border-gray-200 hover:border-[#FF6B00]',
        'hover:bg-[#FF6B00]/5'
      )}
      aria-label={language === 'fr' ? 'Switch to English' : 'Passer en français'}
    >
      <span className={clsx(
        'transition-opacity',
        language === 'fr' ? 'opacity-100 font-bold text-[#FF6B00]' : 'opacity-50'
      )}>
        FR
      </span>
      <span className="text-gray-300">/</span>
      <span className={clsx(
        'transition-opacity',
        language === 'en' ? 'opacity-100 font-bold text-[#FF6B00]' : 'opacity-50'
      )}>
        EN
      </span>
    </button>
  );
}

export default LanguageSwitcher;
