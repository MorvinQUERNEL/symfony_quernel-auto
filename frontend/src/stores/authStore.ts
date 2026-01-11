import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import { authApi, setAccessToken, clearAccessToken, getAccessToken } from '@/api';
import type { User, LoginCredentials, RegisterData } from '@/types';

interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;

  // Actions
  login: (credentials: LoginCredentials) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => Promise<void>;
  fetchUser: () => Promise<void>;
  clearError: () => void;
  checkAuth: () => Promise<boolean>;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      isAuthenticated: false,
      isLoading: false,
      error: null,

      login: async (credentials: LoginCredentials) => {
        set({ isLoading: true, error: null });
        try {
          const response = await authApi.login(credentials);
          setAccessToken(response.token);

          // Fetch user data
          const user = await authApi.me();
          set({
            user,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch (err) {
          const error = err as { message?: string };
          set({
            error: error.message || 'Erreur de connexion',
            isLoading: false,
            isAuthenticated: false,
            user: null,
          });
          clearAccessToken();
          throw err;
        }
      },

      register: async (data: RegisterData) => {
        set({ isLoading: true, error: null });
        try {
          await authApi.register(data);
          set({ isLoading: false });
        } catch (err) {
          const error = err as { message?: string };
          set({
            error: error.message || "Erreur lors de l'inscription",
            isLoading: false,
          });
          throw err;
        }
      },

      logout: async () => {
        set({ isLoading: true });
        try {
          await authApi.logout();
        } catch {
          // Ignore logout errors
        } finally {
          clearAccessToken();
          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
            error: null,
          });
        }
      },

      fetchUser: async () => {
        const token = getAccessToken();
        if (!token) {
          set({ isAuthenticated: false, user: null });
          return;
        }

        set({ isLoading: true });
        try {
          const user = await authApi.me();
          set({
            user,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch {
          clearAccessToken();
          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
          });
        }
      },

      checkAuth: async () => {
        const token = getAccessToken();
        if (!token) {
          set({ isAuthenticated: false, user: null });
          return false;
        }

        try {
          const user = await authApi.me();
          set({ user, isAuthenticated: true });
          return true;
        } catch {
          clearAccessToken();
          set({ user: null, isAuthenticated: false });
          return false;
        }
      },

      clearError: () => set({ error: null }),
    }),
    {
      name: 'auth-storage',
      storage: createJSONStorage(() => localStorage),
      partialize: (state) => ({
        user: state.user,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
);

// Selectors
export const selectUser = (state: AuthState) => state.user;
export const selectIsAuthenticated = (state: AuthState) => state.isAuthenticated;
export const selectIsAdmin = (state: AuthState) =>
  state.user?.roles?.some((role) => ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'].includes(role)) ?? false;
export const selectIsSuperAdmin = (state: AuthState) =>
  state.user?.roles?.includes('ROLE_SUPER_ADMIN') ?? false;

export default useAuthStore;
