import { get, put, del } from './client';
import type { User, Order, Conversation, AdminStats } from '@/types';

export const adminApi = {
  // Stats
  getStats: () =>
    get<AdminStats>('/admin/stats'),

  // Users management
  getUsers: (page = 1, limit = 20) =>
    get<{ data: User[]; total: number }>(`/admin/users?page=${page}&limit=${limit}`),

  getUser: (id: number) =>
    get<User>(`/admin/users/${id}`),

  updateUserRoles: (id: number, roles: string[]) =>
    put<User>(`/admin/users/${id}/roles`, { roles }),

  deleteUser: (id: number) =>
    del<{ message: string }>(`/admin/users/${id}`),

  // Orders management
  getOrders: (page = 1, limit = 20, status?: string) => {
    const params = new URLSearchParams({ page: String(page), limit: String(limit) });
    if (status) params.append('status', status);
    return get<{ data: Order[]; total: number }>(`/admin/orders?${params}`);
  },

  updateOrderStatus: (id: number, status: string) =>
    put<Order>(`/admin/orders/${id}/status`, { status }),

  // Messages/Conversations management
  getConversations: () =>
    get<Conversation[]>('/admin/messages/conversations'),
};

export default adminApi;
