// User types
export interface User {
  id: number;
  email: string;
  firstname: string;
  lastname: string;
  phone?: string;
  address?: string;
  city?: string;
  zipcode?: string;
  roles: string[];
  createdAt: string;
  isVerified: boolean;
}

export interface AuthResponse {
  token: string;
  user?: User;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  email: string;
  password: string;
  firstname: string;
  lastname: string;
  phone?: string;
}

// Vehicule types
export interface Picture {
  id: number;
  name: string; // Path to the image (e.g., "/uploads/vehicules/demo/ferrari-458.jpg")
  description?: string;
}

export interface Vehicule {
  id: number;
  brand: string;
  model: string;
  year: string; // ISO date string from backend
  salePrice: number; // Price in centimes
  mileage: number;
  fuelType: string;
  trasmission: string; // Note: typo from backend entity
  color?: string;
  doorCount?: number;
  description?: string;
  status: VehiculeStatus;
  pictures: Picture[];
}

export type VehiculeStatus = 'available' | 'reserved' | 'sold' | 'pending';

export interface VehiculeFilters {
  search?: string;
  brand?: string;
  minPrice?: number;
  maxPrice?: number;
  minYear?: number;
  maxYear?: number;
  fuelType?: string;
  transmission?: string;
  status?: VehiculeStatus;
  page?: number;
  limit?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
}

export interface VehiculeListResponse {
  data: Vehicule[];
  meta: {
    total: number;
    page: number;
    limit: number;
    pages: number;
  };
}

// Order types
export interface Order {
  id: number;
  user: User;
  vehicule: Vehicule;
  status: OrderStatus;
  totalPrice: number;
  createdAt: string;
  updatedAt?: string;
  paymentId?: string;
  notes?: string;
}

export type OrderStatus = 'pending' | 'confirmed' | 'paid' | 'cancelled' | 'completed';

export interface CreateOrderData {
  vehiculeId: number;
  notes?: string;
}

// Payment types
export interface PaymentIntent {
  clientSecret: string;
  paymentIntentId: string;
}

export interface CheckoutSession {
  sessionId: string;
  url: string;
}

// Message types
export interface Message {
  id: number;
  content: string;
  conversationId: string;
  isFromUser: boolean;
  createdAt: string;
  isRead: boolean;
}

export interface Conversation {
  id: string;
  lastMessage?: Message;
  unreadCount: number;
  user?: User;
}

export interface SendMessageData {
  content: string;
  conversationId?: string;
}

// Preference types
export interface Preference {
  id: number;
  brand?: string;
  minPrice?: number;
  maxPrice?: number;
  minYear?: number;
  maxYear?: number;
  fuelType?: string;
  transmission?: string;
}

// API response types
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  statusCode?: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    total: number;
    page: number;
    limit: number;
    pages: number;
  };
}

// Admin stats
export interface AdminStats {
  totalUsers: number;
  totalVehicules: number;
  totalOrders: number;
  pendingOrders: number;
  revenue: number;
  recentOrders: Order[];
}

// Helper function to format price from centimes to euros
export function formatPrice(priceInCentimes: number): string {
  return (priceInCentimes / 100).toLocaleString('fr-FR');
}

// Helper function to get image URL
export function getImageUrl(picture: Picture | undefined, fallback = '/placeholder-car.jpg'): string {
  if (!picture) return fallback;

  // Base URL for images (backend server)
  const baseUrl = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8080';

  // If the name starts with /, it's a relative path from backend
  if (picture.name.startsWith('/')) {
    return `${baseUrl}${picture.name}`;
  }
  return `${baseUrl}/uploads/vehicules/${picture.name}`;
}

// Helper function to extract year from date string
export function getYearFromDate(dateString: string): number {
  return new Date(dateString).getFullYear();
}
