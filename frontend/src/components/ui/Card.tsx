import { type HTMLAttributes, type ReactNode } from 'react';
import { clsx } from 'clsx';

export interface CardProps extends HTMLAttributes<HTMLDivElement> {
  variant?: 'default' | 'elevated' | 'outlined' | 'glass';
  hover?: boolean;
  padding?: 'none' | 'sm' | 'md' | 'lg';
  children: ReactNode;
}

export function Card({
  variant = 'default',
  hover = false,
  padding = 'md',
  children,
  className,
  ...props
}: CardProps) {
  const variants = {
    default: `
      bg-white
      border border-gray-100
      shadow-sm
    `,
    elevated: `
      bg-white
      border border-gray-100
      shadow-[0_4px_20px_rgba(0,0,0,0.08)]
    `,
    outlined: `
      bg-white
      border-2 border-gray-200
    `,
    glass: `
      bg-white/70 backdrop-blur-xl
      border border-white/20
      shadow-[0_8px_32px_rgba(0,0,0,0.1)]
    `,
  };

  const paddings = {
    none: '',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
  };

  const hoverStyles = hover
    ? `
      cursor-pointer
      transition-all duration-300 ease-out
      hover:shadow-[0_8px_30px_rgba(255,107,0,0.15)]
      hover:border-[#FF6B00]/30
      hover:-translate-y-1
      active:translate-y-0
      active:shadow-[0_4px_15px_rgba(255,107,0,0.1)]
    `
    : '';

  return (
    <div
      className={clsx(
        'rounded-2xl overflow-hidden',
        variants[variant],
        paddings[padding],
        hoverStyles,
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
}

// Card subcomponents for structured layouts
export function CardHeader({
  children,
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={clsx(
        'flex items-center justify-between',
        'pb-4 mb-4 border-b border-gray-100',
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
}

export function CardTitle({
  children,
  className,
  ...props
}: HTMLAttributes<HTMLHeadingElement>) {
  return (
    <h3
      className={clsx(
        'text-lg font-bold text-gray-900 tracking-tight',
        className
      )}
      {...props}
    >
      {children}
    </h3>
  );
}

export function CardDescription({
  children,
  className,
  ...props
}: HTMLAttributes<HTMLParagraphElement>) {
  return (
    <p
      className={clsx('text-sm text-gray-500 mt-1', className)}
      {...props}
    >
      {children}
    </p>
  );
}

export function CardContent({
  children,
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div className={clsx('', className)} {...props}>
      {children}
    </div>
  );
}

export function CardFooter({
  children,
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={clsx(
        'flex items-center justify-between',
        'pt-4 mt-4 border-t border-gray-100',
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
}

// Vehicle-specific card variant
export interface VehicleCardProps {
  image: string;
  brand: string;
  model: string;
  year: number;
  price: number;
  mileage: number;
  fuelType: string;
  status?: 'available' | 'reserved' | 'sold' | 'pending';
  onClick?: () => void;
}

export function VehicleCard({
  image,
  brand,
  model,
  year,
  price,
  mileage,
  fuelType,
  status = 'available',
  onClick,
}: VehicleCardProps) {
  const statusStyles = {
    available: 'bg-green-100 text-green-800',
    reserved: 'bg-yellow-100 text-yellow-800',
    sold: 'bg-red-100 text-red-800',
    pending: 'bg-blue-100 text-blue-800',
  };

  const statusLabels = {
    available: 'Disponible',
    reserved: 'Réservé',
    sold: 'Vendu',
    pending: 'En attente',
  };

  return (
    <Card
      hover
      padding="none"
      className="group overflow-hidden"
      onClick={onClick}
    >
      {/* Image container */}
      <div className="relative aspect-[4/3] overflow-hidden bg-gray-100">
        <img
          src={image}
          alt={`${brand} ${model}`}
          className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
        />

        {/* Status badge */}
        <div className="absolute top-4 left-4">
          <span
            className={clsx(
              'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider',
              statusStyles[status]
            )}
          >
            {statusLabels[status]}
          </span>
        </div>

        {/* Gradient overlay */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

        {/* Quick info on hover */}
        <div className="absolute bottom-4 left-4 right-4 flex items-center justify-between opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
          <span className="text-white text-sm font-medium">
            {mileage.toLocaleString('fr-FR')} km
          </span>
          <span className="text-white text-sm font-medium">{fuelType}</span>
        </div>
      </div>

      {/* Content */}
      <div className="p-5">
        {/* Title */}
        <div className="mb-3">
          <h3 className="text-lg font-bold text-gray-900 tracking-tight">
            {brand} {model}
          </h3>
          <p className="text-sm text-gray-500">{year}</p>
        </div>

        {/* Price */}
        <div className="flex items-baseline gap-1">
          <span className="text-2xl font-black text-[#FF6B00]">
            {price.toLocaleString('fr-FR')}
          </span>
          <span className="text-sm font-medium text-gray-500">€</span>
        </div>
      </div>

      {/* Bottom accent line */}
      <div className="h-1 w-full bg-gradient-to-r from-[#FF6B00] to-[#FFAA00] transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left" />
    </Card>
  );
}

export default Card;
