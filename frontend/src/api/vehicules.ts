import { get, post, put, del } from './client';
import type { Vehicule, VehiculeFilters, VehiculeListResponse } from '@/types';

export const vehiculesApi = {
  list: (filters?: VehiculeFilters) => {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, String(value));
        }
      });
    }
    const queryString = params.toString();
    return get<VehiculeListResponse>(`/vehicules${queryString ? `?${queryString}` : ''}`);
  },

  getById: (id: number) =>
    get<Vehicule>(`/vehicules/${id}`),

  create: (data: Partial<Vehicule>) =>
    post<Vehicule>('/vehicules', data),

  update: (id: number, data: Partial<Vehicule>) =>
    put<Vehicule>(`/vehicules/${id}`, data),

  delete: (id: number) =>
    del<{ message: string }>(`/vehicules/${id}`),

  uploadPictures: (id: number, files: File[]) => {
    const formData = new FormData();
    files.forEach((file) => {
      formData.append('pictures[]', file);
    });
    return post<{ pictures: Array<{ id: number; url: string }> }>(
      `/vehicules/${id}/pictures`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );
  },

  deletePicture: (vehiculeId: number, pictureId: number) =>
    del<{ message: string }>(`/vehicules/${vehiculeId}/pictures/${pictureId}`),

  getBrands: () =>
    get<string[]>('/vehicules/brands'),

  getFuelTypes: () =>
    get<string[]>('/vehicules/fuel-types'),
};

export default vehiculesApi;
