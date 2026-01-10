import { useMutation, useQuery } from '@tanstack/react-query';
import { paymentApi } from '@/api/payment';

export function useCreateCheckoutSession() {
  return useMutation({
    mutationFn: (orderId: number) => paymentApi.createCheckoutSession(orderId),
  });
}

export function useCreatePaymentIntent() {
  return useMutation({
    mutationFn: (orderId: number) => paymentApi.createPaymentIntent(orderId),
  });
}

export function usePaymentStatus(orderId: number) {
  return useQuery({
    queryKey: ['payment', 'status', orderId],
    queryFn: () => paymentApi.getPaymentStatus(orderId),
    enabled: !!orderId,
  });
}
