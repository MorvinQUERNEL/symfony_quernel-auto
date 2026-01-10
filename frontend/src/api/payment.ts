import { get, post } from './client';
import type { CheckoutSession, PaymentIntent } from '@/types';

export const paymentApi = {
  createCheckoutSession: (orderId: number) =>
    post<CheckoutSession>(`/payment/checkout-session`, { orderId }),

  createPaymentIntent: (orderId: number) =>
    post<PaymentIntent>(`/payment/payment-intent`, { orderId }),

  getPaymentStatus: (orderId: number) =>
    get<{ status: string; paid: boolean }>(`/payment/status/${orderId}`),
};

export default paymentApi;
