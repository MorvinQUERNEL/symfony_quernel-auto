import { get, post } from './client';
import type { AuthResponse, LoginCredentials, RegisterData, User } from '@/types';

export const authApi = {
  login: (credentials: LoginCredentials) =>
    post<AuthResponse>('/auth/login', credentials),

  register: (data: RegisterData) =>
    post<{ message: string; user: User }>('/auth/register', data),

  me: () =>
    get<User>('/users/me'),

  logout: () =>
    post<{ message: string }>('/auth/logout'),

  requestPasswordReset: (email: string) =>
    post<{ message: string }>('/auth/password-reset/request', { email }),

  resetPassword: (token: string, password: string) =>
    post<{ message: string }>('/auth/password-reset/reset', { token, password }),
};

export default authApi;
