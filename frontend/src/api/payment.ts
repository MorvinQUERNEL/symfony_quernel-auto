import { get, post } from './client';
import type { CheckoutSession, PaymentIntent } from '@/types';

export const paymentApi = {
  createCheckoutSession: (orderId: number) =>
    post<CheckoutSession>(`/payments/checkout-session`, { orderId }),

  createPaymentIntent: (orderId: number) =>
    post<PaymentIntent>(`/payments/payment-intent`, { orderId }),

  getPaymentStatus: (orderId: number) =>
    get<{ status: string; paid: boolean }>(`/payments/status/${orderId}`),
};

export default paymentApi;
