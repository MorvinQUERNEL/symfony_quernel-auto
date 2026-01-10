import { get, post, del } from './client';
import type { Order, CreateOrderData, CheckoutSession } from '@/types';

export const ordersApi = {
  list: () =>
    get<Order[]>('/orders'),

  getById: (id: number) =>
    get<Order>(`/orders/${id}`),

  create: (data: CreateOrderData) =>
    post<Order>('/orders', data),

  cancel: (id: number) =>
    del<{ message: string }>(`/orders/${id}`),

  checkout: (id: number) =>
    post<CheckoutSession>(`/orders/${id}/checkout`),

  // My orders (for regular users)
  myOrders: () =>
    get<Order[]>('/orders/my'),
};

export default ordersApi;
