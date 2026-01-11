import { useState } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { Search, SlidersHorizontal, X, Car } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Button, Input, Select, Card, VehicleCard, Spinner } from '@/components/ui';
import { vehiculesApi } from '@/api';
import type { VehiculeFilters } from '@/types';
import { getImageUrl, getYearFromDate } from '@/types';

export function VehiculesListPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [showFilters, setShowFilters] = useState(false);

  // Parse filters from URL
  const filters: VehiculeFilters = {
    search: searchParams.get('search') || undefined,
    brand: searchParams.get('brand') || undefined,
    minPrice: searchParams.get('minPrice') ? Number(searchParams.get('minPrice')) : undefined,
    maxPrice: searchParams.get('maxPrice') ? Number(searchParams.get('maxPrice')) : undefined,
    fuelType: searchParams.get('fuelType') || undefined,
    page: searchParams.get('page') ? Number(searchParams.get('page')) : 1,
    limit: 12,
  };

  // Fetch vehicules
  const { data, isLoading, error } = useQuery({
    queryKey: ['vehicules', filters],
    queryFn: () => vehiculesApi.list(filters),
  });

  // Fetch brands for filter
  const { data: brands } = useQuery({
    queryKey: ['brands'],
    queryFn: () => vehiculesApi.getBrands(),
  });

  // Fetch fuel types for filter
  const { data: fuelTypes } = useQuery({
    queryKey: ['fuelTypes'],
    queryFn: () => vehiculesApi.getFuelTypes(),
  });

  const updateFilter = (key: string, value: string | undefined) => {
    const newParams = new URLSearchParams(searchParams);
    if (value) {
      newParams.set(key, value);
    } else {
      newParams.delete(key);
    }
    // Reset to page 1 when filters change
    if (key !== 'page') {
      newParams.delete('page');
    }
    setSearchParams(newParams);
  };

  const clearFilters = () => {
    setSearchParams({});
  };

  const activeFiltersCount = [
    filters.brand,
    filters.minPrice,
    filters.maxPrice,
    filters.fuelType,
  ].filter(Boolean).length;

  return (
    <Layout>
      {/* Hero section */}
      <section className="relative py-16 bg-gradient-to-br from-gray-900 to-gray-800 overflow-hidden">
        {/* Background decoration */}
        <div className="absolute inset-0 opacity-10">
          <div
            className="absolute inset-0"
            style={{
              backgroundImage: `linear-gradient(rgba(255,107,0,0.2) 1px, transparent 1px),
                               linear-gradient(90deg, rgba(255,107,0,0.2) 1px, transparent 1px)`,
              backgroundSize: '40px 40px',
            }}
          />
        </div>
        <div className="absolute top-0 right-1/4 w-96 h-96 bg-[#FF6B00]/10 rounded-full blur-[100px]" />

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 className="text-4xl sm:text-5xl font-black text-white mb-4">
            Nos <span className="text-[#FF6B00]">Véhicules</span>
          </h1>
          <p className="text-gray-300 max-w-2xl mx-auto">
            Découvrez notre sélection de véhicules importés avec soin.
            Qualité garantie, prix compétitifs.
          </p>
        </div>
      </section>

      {/* Search and filters */}
      <section className="sticky top-20 z-30 bg-white border-b border-gray-100 shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex flex-col lg:flex-row gap-4">
            {/* Search bar */}
            <div className="flex-1 relative">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Rechercher un véhicule..."
                value={filters.search || ''}
                onChange={(e) => updateFilter('search', e.target.value || undefined)}
                className="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-[#FF6B00] focus:ring-4 focus:ring-[#FF6B00]/10 outline-none transition-all"
              />
            </div>

            {/* Filter toggle button */}
            <Button
              variant={showFilters ? 'primary' : 'secondary'}
              onClick={() => setShowFilters(!showFilters)}
              leftIcon={<SlidersHorizontal className="w-5 h-5" />}
            >
              Filtres
              {activeFiltersCount > 0 && (
                <span className="ml-2 w-5 h-5 rounded-full bg-white text-[#FF6B00] text-xs font-bold flex items-center justify-center">
                  {activeFiltersCount}
                </span>
              )}
            </Button>
          </div>

          {/* Expanded filters */}
          {showFilters && (
            <div className="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4 animate-in slide-in-from-top-2 duration-200">
              <Select
                label="Marque"
                placeholder="Toutes les marques"
                value={filters.brand || ''}
                onChange={(e) => updateFilter('brand', e.target.value || undefined)}
                options={[
                  { value: '', label: 'Toutes les marques' },
                  ...(brands || []).map((brand) => ({ value: brand, label: brand })),
                ]}
              />

              <Select
                label="Carburant"
                placeholder="Tous les types"
                value={filters.fuelType || ''}
                onChange={(e) => updateFilter('fuelType', e.target.value || undefined)}
                options={[
                  { value: '', label: 'Tous les types' },
                  ...(fuelTypes || []).map((type) => ({ value: type, label: type })),
                ]}
              />

              <Input
                label="Prix min"
                type="number"
                placeholder="0 €"
                value={filters.minPrice || ''}
                onChange={(e) => updateFilter('minPrice', e.target.value || undefined)}
              />

              <Input
                label="Prix max"
                type="number"
                placeholder="100 000 €"
                value={filters.maxPrice || ''}
                onChange={(e) => updateFilter('maxPrice', e.target.value || undefined)}
              />

              {/* Clear filters */}
              {activeFiltersCount > 0 && (
                <div className="col-span-full flex justify-end">
                  <Button variant="ghost" size="sm" onClick={clearFilters} leftIcon={<X className="w-4 h-4" />}>
                    Effacer les filtres
                  </Button>
                </div>
              )}
            </div>
          )}
        </div>
      </section>

      {/* Results */}
      <section className="py-12 bg-gray-50 min-h-[60vh]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {isLoading ? (
            <div className="flex flex-col items-center justify-center py-20">
              <Spinner size="xl" />
              <p className="mt-4 text-gray-500">Chargement des véhicules...</p>
            </div>
          ) : error ? (
            <Card variant="outlined" className="text-center py-12">
              <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <X className="w-8 h-8 text-red-600" />
              </div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">
                Erreur de chargement
              </h3>
              <p className="text-gray-500 mb-6">
                Impossible de charger les véhicules. Veuillez réessayer.
              </p>
              <Button onClick={() => window.location.reload()}>
                Réessayer
              </Button>
            </Card>
          ) : data?.data?.length === 0 ? (
            <Card variant="outlined" className="text-center py-12">
              <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <Car className="w-8 h-8 text-gray-400" />
              </div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">
                Aucun véhicule trouvé
              </h3>
              <p className="text-gray-500 mb-6">
                Modifiez vos critères de recherche ou effacez les filtres.
              </p>
              {activeFiltersCount > 0 && (
                <Button variant="outline" onClick={clearFilters}>
                  Effacer les filtres
                </Button>
              )}
            </Card>
          ) : (
            <>
              {/* Results count */}
              <div className="flex items-center justify-between mb-6">
                <p className="text-gray-600">
                  <span className="font-semibold text-gray-900">{data?.meta?.total}</span> véhicule(s) trouvé(s)
                </p>
              </div>

              {/* Grid */}
              <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {data?.data?.map((vehicule) => (
                  <Link key={vehicule.id} to={`/vehicules/${vehicule.id}`}>
                    <VehicleCard
                      image={getImageUrl(vehicule.pictures[0])}
                      brand={vehicule.brand}
                      model={vehicule.model}
                      year={getYearFromDate(vehicule.year)}
                      price={vehicule.salePrice / 100}
                      mileage={vehicule.mileage}
                      fuelType={vehicule.fuelType}
                      status={vehicule.status}
                    />
                  </Link>
                ))}
              </div>

              {/* Pagination */}
              {data?.meta && data.meta.pages > 1 && (
                <div className="mt-12 flex items-center justify-center gap-2">
                  <Button
                    variant="secondary"
                    size="sm"
                    disabled={filters.page === 1}
                    onClick={() => updateFilter('page', String((filters.page || 1) - 1))}
                  >
                    Précédent
                  </Button>

                  <div className="flex items-center gap-1">
                    {Array.from({ length: Math.min(5, data.meta.pages) }, (_, i) => {
                      const page = i + 1;
                      return (
                        <button
                          key={page}
                          onClick={() => updateFilter('page', String(page))}
                          className={`w-10 h-10 rounded-lg font-medium transition-colors ${
                            filters.page === page
                              ? 'bg-[#FF6B00] text-white'
                              : 'text-gray-600 hover:bg-gray-100'
                          }`}
                        >
                          {page}
                        </button>
                      );
                    })}
                  </div>

                  <Button
                    variant="secondary"
                    size="sm"
                    disabled={filters.page === data.meta.pages}
                    onClick={() => updateFilter('page', String((filters.page || 1) + 1))}
                  >
                    Suivant
                  </Button>
                </div>
              )}
            </>
          )}
        </div>
      </section>
    </Layout>
  );
}

export default VehiculesListPage;
