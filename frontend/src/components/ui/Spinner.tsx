import { clsx } from 'clsx';

export interface SpinnerProps {
  size?: 'sm' | 'md' | 'lg' | 'xl';
  className?: string;
  label?: string;
}

export function Spinner({ size = 'md', className, label }: SpinnerProps) {
  const sizes = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
  };

  const strokeWidths = {
    sm: 3,
    md: 2.5,
    lg: 2,
    xl: 1.5,
  };

  return (
    <div
      role="status"
      aria-label={label || 'Chargement'}
      className={clsx('relative inline-flex items-center justify-center', className)}
    >
      {/* Outer glow ring */}
      <div
        className={clsx(
          sizes[size],
          'absolute rounded-full',
          'bg-gradient-to-r from-[#FF6B00]/30 to-transparent',
          'animate-[spin_2s_linear_infinite]'
        )}
      />

      {/* Main spinner */}
      <svg
        className={clsx(sizes[size], 'animate-spin')}
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        {/* Background track */}
        <circle
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          strokeWidth={strokeWidths[size]}
          className="opacity-20"
        />
        {/* Spinning arc with gradient effect */}
        <circle
          cx="12"
          cy="12"
          r="10"
          stroke="url(#spinnerGradient)"
          strokeWidth={strokeWidths[size]}
          strokeLinecap="round"
          strokeDasharray="32 62"
          className="drop-shadow-[0_0_8px_rgba(255,107,0,0.6)]"
        />
        <defs>
          <linearGradient id="spinnerGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#FF6B00" />
            <stop offset="50%" stopColor="#FF8533" />
            <stop offset="100%" stopColor="#FFAA00" />
          </linearGradient>
        </defs>
      </svg>

      {/* Screen reader text */}
      <span className="sr-only">{label || 'Chargement...'}</span>
    </div>
  );
}

// Full page loading overlay
export function LoadingOverlay({ message = 'Chargement...' }: { message?: string }) {
  return (
    <div className="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">
      {/* Animated logo/spinner container */}
      <div className="relative">
        {/* Pulsing background */}
        <div className="absolute inset-0 -m-8 rounded-full bg-gradient-to-r from-[#FF6B00]/10 to-[#FFAA00]/10 animate-pulse" />

        {/* Rotating ring */}
        <div className="relative">
          <Spinner size="xl" />
        </div>
      </div>

      {/* Loading text */}
      <p className="mt-6 text-lg font-medium text-gray-700 tracking-wide">
        {message}
      </p>

      {/* Animated dots */}
      <div className="flex gap-1.5 mt-2">
        {[0, 1, 2].map((i) => (
          <span
            key={i}
            className="w-2 h-2 rounded-full bg-[#FF6B00] animate-bounce"
            style={{ animationDelay: `${i * 150}ms` }}
          />
        ))}
      </div>
    </div>
  );
}

// Inline loading state for content areas
export function ContentLoader({ className }: { className?: string }) {
  return (
    <div className={clsx('flex flex-col items-center justify-center py-12', className)}>
      <Spinner size="lg" />
      <p className="mt-4 text-sm text-gray-500">Chargement en cours...</p>
    </div>
  );
}

export default Spinner;
