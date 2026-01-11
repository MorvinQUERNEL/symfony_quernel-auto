import { useToastStore, type ToastType } from '@/stores/toastStore';

export function useToast() {
  const { addToast, removeToast, clearToasts, toasts } = useToastStore();

  const toast = {
    success: (message: string, duration?: number) => {
      addToast({ type: 'success', message, duration });
    },
    error: (message: string, duration?: number) => {
      addToast({ type: 'error', message, duration: duration ?? 6000 });
    },
    warning: (message: string, duration?: number) => {
      addToast({ type: 'warning', message, duration });
    },
    info: (message: string, duration?: number) => {
      addToast({ type: 'info', message, duration });
    },
    custom: (type: ToastType, message: string, duration?: number) => {
      addToast({ type, message, duration });
    },
  };

  return {
    toast,
    toasts,
    removeToast,
    clearToasts,
  };
}

export default useToast;
