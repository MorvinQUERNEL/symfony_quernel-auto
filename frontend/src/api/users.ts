import { get, put } from './client';
import type { User } from '@/types';

export const usersApi = {
  getProfile: () =>
    get<User>('/users/me'),

  updateProfile: (data: Partial<User>) =>
    put<User>('/users/me', data),

  changePassword: (currentPassword: string, newPassword: string) =>
    put<{ message: string }>('/users/me/password', {
      currentPassword,
      newPassword,
    }),
};

export default usersApi;
