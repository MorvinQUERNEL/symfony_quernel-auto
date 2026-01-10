import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { vehiculesApi } from '@/api/vehicules';
import type { VehiculeFilters, Vehicule } from '@/types';

export function useVehicules(filters?: VehiculeFilters) {
  return useQuery({
    queryKey: ['vehicules', filters],
    queryFn: () => vehiculesApi.list(filters),
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
}

export function useVehicule(id: number) {
  return useQuery({
    queryKey: ['vehicules', id],
    queryFn: () => vehiculesApi.getById(id),
    enabled: !!id,
  });
}

export function useCreateVehicule() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: Partial<Vehicule>) => vehiculesApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['vehicules'] });
    },
  });
}

export function useUpdateVehicule() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<Vehicule> }) =>
      vehiculesApi.update(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['vehicules'] });
      queryClient.invalidateQueries({ queryKey: ['vehicules', variables.id] });
    },
  });
}

export function useDeleteVehicule() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => vehiculesApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['vehicules'] });
    },
  });
}

export function useUploadVehiculePictures() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, files }: { id: number; files: File[] }) =>
      vehiculesApi.uploadPictures(id, files),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['vehicules', variables.id] });
    },
  });
}

export function useDeleteVehiculePicture() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      vehiculeId,
      pictureId,
    }: {
      vehiculeId: number;
      pictureId: number;
    }) => vehiculesApi.deletePicture(vehiculeId, pictureId),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({
        queryKey: ['vehicules', variables.vehiculeId],
      });
    },
  });
}

export function useVehiculeBrands() {
  return useQuery({
    queryKey: ['vehicules', 'brands'],
    queryFn: () => vehiculesApi.getBrands(),
    staleTime: 30 * 60 * 1000, // 30 minutes
  });
}

export function useVehiculeFuelTypes() {
  return useQuery({
    queryKey: ['vehicules', 'fuel-types'],
    queryFn: () => vehiculesApi.getFuelTypes(),
    staleTime: 30 * 60 * 1000, // 30 minutes
  });
}
