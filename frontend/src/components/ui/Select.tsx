import { forwardRef, type SelectHTMLAttributes, type ReactNode } from 'react';
import { clsx } from 'clsx';
import { ChevronDown, AlertCircle } from 'lucide-react';

export interface SelectOption {
  value: string;
  label: string;
  disabled?: boolean;
}

export interface SelectProps extends Omit<SelectHTMLAttributes<HTMLSelectElement>, 'size'> {
  label?: string;
  error?: string;
  hint?: string;
  options: SelectOption[];
  placeholder?: string;
  leftIcon?: ReactNode;
  size?: 'sm' | 'md' | 'lg';
  fullWidth?: boolean;
}

export const Select = forwardRef<HTMLSelectElement, SelectProps>(
  (
    {
      label,
      error,
      hint,
      options,
      placeholder,
      leftIcon,
      size = 'md',
      fullWidth = true,
      className,
      id,
      disabled,
      ...props
    },
    ref
  ) => {
    const selectId = id || `select-${Math.random().toString(36).slice(2, 9)}`;

    const sizes = {
      sm: 'px-3 py-2 text-sm',
      md: 'px-4 py-3 text-base',
      lg: 'px-5 py-4 text-lg',
    };

    const iconSizes = {
      sm: 'w-4 h-4',
      md: 'w-5 h-5',
      lg: 'w-6 h-6',
    };

    const hasError = Boolean(error);

    return (
      <div className={clsx('relative', fullWidth && 'w-full')}>
        {/* Label */}
        {label && (
          <label
            htmlFor={selectId}
            className={clsx(
              'block mb-2 font-medium tracking-wide transition-colors',
              hasError ? 'text-red-600' : 'text-gray-700',
              size === 'sm' && 'text-xs',
              size === 'md' && 'text-sm',
              size === 'lg' && 'text-base'
            )}
          >
            {label}
          </label>
        )}

        {/* Select container */}
        <div className="relative group">
          {/* Left icon */}
          {leftIcon && (
            <div
              className={clsx(
                'absolute left-4 top-1/2 -translate-y-1/2 z-10',
                'text-gray-400 transition-colors',
                'group-focus-within:text-[#FF6B00]',
                hasError && 'text-red-400 group-focus-within:text-red-500',
                iconSizes[size]
              )}
            >
              {leftIcon}
            </div>
          )}

          {/* Select field */}
          <select
            ref={ref}
            id={selectId}
            disabled={disabled}
            className={clsx(
              // Base styles
              'w-full rounded-xl border-2 bg-white appearance-none cursor-pointer',
              'font-medium',
              'transition-all duration-300 ease-out',
              'focus:outline-none',

              // Size variants
              sizes[size],

              // Icon padding
              leftIcon && 'pl-12',
              'pr-12', // Always space for chevron

              // State variants
              !hasError && [
                'border-gray-200',
                'hover:border-gray-300',
                'focus:border-[#FF6B00] focus:ring-4 focus:ring-[#FF6B00]/10',
                'focus:shadow-[0_0_20px_rgba(255,107,0,0.15)]',
              ],

              hasError && [
                'border-red-300',
                'bg-red-50/50',
                'focus:border-red-500 focus:ring-4 focus:ring-red-500/10',
                'text-red-900',
              ],

              // Disabled state
              disabled && 'opacity-50 cursor-not-allowed bg-gray-50',

              className
            )}
            {...props}
          >
            {placeholder && (
              <option value="" disabled>
                {placeholder}
              </option>
            )}
            {options.map((option) => (
              <option
                key={option.value}
                value={option.value}
                disabled={option.disabled}
              >
                {option.label}
              </option>
            ))}
          </select>

          {/* Chevron icon */}
          <div
            className={clsx(
              'absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none',
              'text-gray-400 transition-transform duration-200',
              'group-focus-within:text-[#FF6B00] group-focus-within:rotate-180',
              iconSizes[size]
            )}
          >
            <ChevronDown className="w-full h-full" />
          </div>
        </div>

        {/* Helper text */}
        {(error || hint) && (
          <p
            className={clsx(
              'mt-2 flex items-center gap-1.5',
              size === 'sm' && 'text-xs',
              size === 'md' && 'text-sm',
              size === 'lg' && 'text-base',
              error && 'text-red-600',
              hint && !error && 'text-gray-500'
            )}
          >
            {error && <AlertCircle className="w-4 h-4 flex-shrink-0" />}
            {error || hint}
          </p>
        )}
      </div>
    );
  }
);

Select.displayName = 'Select';

export default Select;
