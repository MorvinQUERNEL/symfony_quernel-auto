import { useEffect, useState } from 'react';
import { X, CheckCircle, XCircle, AlertTriangle, Info } from 'lucide-react';
import { clsx } from 'clsx';
import { useToastStore, type Toast as ToastType } from '@/stores/toastStore';

const icons = {
  success: CheckCircle,
  error: XCircle,
  warning: AlertTriangle,
  info: Info,
};

const styles = {
  success: {
    bg: 'bg-green-50 border-green-200',
    icon: 'text-green-500',
    text: 'text-green-800',
    progress: 'bg-green-500',
  },
  error: {
    bg: 'bg-red-50 border-red-200',
    icon: 'text-red-500',
    text: 'text-red-800',
    progress: 'bg-red-500',
  },
  warning: {
    bg: 'bg-amber-50 border-amber-200',
    icon: 'text-amber-500',
    text: 'text-amber-800',
    progress: 'bg-amber-500',
  },
  info: {
    bg: 'bg-blue-50 border-blue-200',
    icon: 'text-blue-500',
    text: 'text-blue-800',
    progress: 'bg-blue-500',
  },
};

interface ToastItemProps {
  toast: ToastType;
  onRemove: (id: string) => void;
}

function ToastItem({ toast, onRemove }: ToastItemProps) {
  const [isLeaving, setIsLeaving] = useState(false);
  const [progress, setProgress] = useState(100);

  const Icon = icons[toast.type];
  const style = styles[toast.type];

  useEffect(() => {
    if (!toast.duration || toast.duration <= 0) return;

    const startTime = Date.now();
    const interval = setInterval(() => {
      const elapsed = Date.now() - startTime;
      const remaining = Math.max(0, 100 - (elapsed / toast.duration!) * 100);
      setProgress(remaining);

      if (remaining <= 0) {
        clearInterval(interval);
      }
    }, 50);

    return () => clearInterval(interval);
  }, [toast.duration]);

  const handleRemove = () => {
    setIsLeaving(true);
    setTimeout(() => onRemove(toast.id), 200);
  };

  return (
    <div
      className={clsx(
        'relative flex items-start gap-3 p-4 rounded-lg border shadow-lg overflow-hidden',
        'transform transition-all duration-200',
        style.bg,
        isLeaving ? 'opacity-0 translate-x-full' : 'opacity-100 translate-x-0'
      )}
    >
      <Icon className={clsx('w-5 h-5 flex-shrink-0 mt-0.5', style.icon)} />

      <p className={clsx('flex-1 text-sm font-medium', style.text)}>
        {toast.message}
      </p>

      <button
        onClick={handleRemove}
        className={clsx(
          'flex-shrink-0 p-1 rounded-full transition-colors',
          'hover:bg-black/10'
        )}
      >
        <X className="w-4 h-4 text-gray-500" />
      </button>

      {/* Progress bar */}
      {toast.duration && toast.duration > 0 && (
        <div className="absolute bottom-0 left-0 right-0 h-1 bg-black/10">
          <div
            className={clsx('h-full transition-all duration-100', style.progress)}
            style={{ width: `${progress}%` }}
          />
        </div>
      )}
    </div>
  );
}

export function ToastContainer() {
  const { toasts, removeToast } = useToastStore();

  if (toasts.length === 0) return null;

  return (
    <div className="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
      {toasts.map((toast) => (
        <div key={toast.id} className="pointer-events-auto">
          <ToastItem toast={toast} onRemove={removeToast} />
        </div>
      ))}
    </div>
  );
}

export default ToastContainer;
