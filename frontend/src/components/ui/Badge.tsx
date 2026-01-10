import { type HTMLAttributes, type ReactNode } from 'react';
import { clsx } from 'clsx';

export interface BadgeProps extends HTMLAttributes<HTMLSpanElement> {
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'error' | 'info';
  size?: 'sm' | 'md' | 'lg';
  dot?: boolean;
  pulse?: boolean;
  icon?: ReactNode;
}

export function Badge({
  children,
  variant = 'default',
  size = 'md',
  dot = false,
  pulse = false,
  icon,
  className,
  ...props
}: BadgeProps) {
  const variants = {
    default: `
      bg-gray-100 text-gray-700
      border border-gray-200
    `,
    primary: `
      bg-gradient-to-r from-[#FF6B00]/10 to-[#FFAA00]/10
      text-[#FF6B00]
      border border-[#FF6B00]/20
    `,
    success: `
      bg-gradient-to-r from-green-50 to-emerald-50
      text-green-700
      border border-green-200
    `,
    warning: `
      bg-gradient-to-r from-yellow-50 to-amber-50
      text-yellow-700
      border border-yellow-200
    `,
    error: `
      bg-gradient-to-r from-red-50 to-rose-50
      text-red-700
      border border-red-200
    `,
    info: `
      bg-gradient-to-r from-blue-50 to-indigo-50
      text-blue-700
      border border-blue-200
    `,
  };

  const dotColors = {
    default: 'bg-gray-400',
    primary: 'bg-[#FF6B00]',
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
  };

  const sizes = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-xs',
    lg: 'px-3 py-1.5 text-sm',
  };

  return (
    <span
      className={clsx(
        'inline-flex items-center gap-1.5 rounded-full font-semibold uppercase tracking-wider',
        variants[variant],
        sizes[size],
        className
      )}
      {...props}
    >
      {/* Dot indicator */}
      {dot && (
        <span className="relative flex h-2 w-2">
          {pulse && (
            <span
              className={clsx(
                'absolute inline-flex h-full w-full rounded-full opacity-75 animate-ping',
                dotColors[variant]
              )}
            />
          )}
          <span
            className={clsx(
              'relative inline-flex rounded-full h-2 w-2',
              dotColors[variant]
            )}
          />
        </span>
      )}

      {/* Icon */}
      {icon && <span className="w-3.5 h-3.5">{icon}</span>}

      {/* Text */}
      {children}
    </span>
  );
}

// Status badge specifically for orders/vehicles
export type StatusType = 'pending' | 'confirmed' | 'paid' | 'completed' | 'cancelled' | 'available' | 'reserved' | 'sold';

export interface StatusBadgeProps {
  status: StatusType;
  size?: 'sm' | 'md' | 'lg';
  showDot?: boolean;
}

export function StatusBadge({ status, size = 'md', showDot = true }: StatusBadgeProps) {
  const statusConfig: Record<StatusType, { variant: BadgeProps['variant']; label: string; pulse?: boolean }> = {
    pending: { variant: 'warning', label: 'En attente', pulse: true },
    confirmed: { variant: 'info', label: 'Confirmé' },
    paid: { variant: 'success', label: 'Payé' },
    completed: { variant: 'success', label: 'Terminé' },
    cancelled: { variant: 'error', label: 'Annulé' },
    available: { variant: 'success', label: 'Disponible', pulse: true },
    reserved: { variant: 'warning', label: 'Réservé' },
    sold: { variant: 'error', label: 'Vendu' },
  };

  const config = statusConfig[status] || { variant: 'default', label: status };

  return (
    <Badge
      variant={config.variant}
      size={size}
      dot={showDot}
      pulse={config.pulse}
    >
      {config.label}
    </Badge>
  );
}

// Notification count badge
export interface CountBadgeProps {
  count: number;
  max?: number;
  className?: string;
}

export function CountBadge({ count, max = 99, className }: CountBadgeProps) {
  if (count <= 0) return null;

  const displayCount = count > max ? `${max}+` : count;

  return (
    <span
      className={clsx(
        'inline-flex items-center justify-center',
        'min-w-[1.25rem] h-5 px-1.5',
        'text-xs font-bold text-white',
        'bg-gradient-to-r from-[#FF6B00] to-[#FF8533]',
        'rounded-full',
        'shadow-[0_2px_8px_rgba(255,107,0,0.4)]',
        'animate-in zoom-in-50 duration-200',
        className
      )}
    >
      {displayCount}
    </span>
  );
}

export default Badge;
