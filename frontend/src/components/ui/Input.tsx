import { forwardRef, type InputHTMLAttributes, type ReactNode, useState } from 'react';
import { clsx } from 'clsx';
import { Eye, EyeOff, AlertCircle, CheckCircle } from 'lucide-react';

export interface InputProps extends Omit<InputHTMLAttributes<HTMLInputElement>, 'size'> {
  label?: string;
  error?: string;
  success?: string;
  hint?: string;
  leftIcon?: ReactNode;
  rightIcon?: ReactNode;
  size?: 'sm' | 'md' | 'lg';
  fullWidth?: boolean;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  (
    {
      label,
      error,
      success,
      hint,
      leftIcon,
      rightIcon,
      size = 'md',
      fullWidth = true,
      type = 'text',
      className,
      id,
      disabled,
      ...props
    },
    ref
  ) => {
    const [showPassword, setShowPassword] = useState(false);
    const inputId = id || `input-${Math.random().toString(36).slice(2, 9)}`;
    const isPassword = type === 'password';

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
    const hasSuccess = Boolean(success);

    return (
      <div className={clsx('relative', fullWidth && 'w-full')}>
        {/* Label */}
        {label && (
          <label
            htmlFor={inputId}
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

        {/* Input container */}
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

          {/* Input field */}
          <input
            ref={ref}
            id={inputId}
            type={isPassword && showPassword ? 'text' : type}
            disabled={disabled}
            className={clsx(
              // Base styles
              'w-full rounded-xl border-2 bg-white',
              'font-medium placeholder:text-gray-400 placeholder:font-normal',
              'transition-all duration-300 ease-out',
              'focus:outline-none',

              // Size variants
              sizes[size],

              // Icon padding
              leftIcon && 'pl-12',
              (rightIcon || isPassword) && 'pr-12',

              // State variants
              !hasError &&
                !hasSuccess && [
                  'border-gray-200',
                  'hover:border-gray-300',
                  'focus:border-[#FF6B00] focus:ring-4 focus:ring-[#FF6B00]/10',
                  'focus:shadow-[0_0_20px_rgba(255,107,0,0.15)]',
                ],

              hasError && [
                'border-red-300',
                'bg-red-50/50',
                'focus:border-red-500 focus:ring-4 focus:ring-red-500/10',
                'text-red-900 placeholder:text-red-300',
              ],

              hasSuccess && [
                'border-green-300',
                'bg-green-50/50',
                'focus:border-green-500 focus:ring-4 focus:ring-green-500/10',
              ],

              // Disabled state
              disabled && 'opacity-50 cursor-not-allowed bg-gray-50',

              className
            )}
            {...props}
          />

          {/* Right icon or password toggle */}
          {(rightIcon || isPassword || hasError || hasSuccess) && (
            <div
              className={clsx(
                'absolute right-4 top-1/2 -translate-y-1/2',
                'flex items-center gap-2'
              )}
            >
              {hasError && !isPassword && (
                <AlertCircle className={clsx(iconSizes[size], 'text-red-500')} />
              )}

              {hasSuccess && !isPassword && (
                <CheckCircle className={clsx(iconSizes[size], 'text-green-500')} />
              )}

              {isPassword && (
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className={clsx(
                    'p-1 rounded-lg text-gray-400 transition-colors',
                    'hover:text-gray-600 hover:bg-gray-100',
                    'focus:outline-none focus:ring-2 focus:ring-[#FF6B00]/50'
                  )}
                  tabIndex={-1}
                  aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                >
                  {showPassword ? (
                    <EyeOff className={iconSizes[size]} />
                  ) : (
                    <Eye className={iconSizes[size]} />
                  )}
                </button>
              )}

              {rightIcon && !isPassword && !hasError && !hasSuccess && (
                <span className={clsx('text-gray-400', iconSizes[size])}>
                  {rightIcon}
                </span>
              )}
            </div>
          )}

          {/* Focus ring animation */}
          <div
            className={clsx(
              'absolute inset-0 rounded-xl pointer-events-none',
              'border-2 border-transparent',
              'opacity-0 scale-105',
              'group-focus-within:opacity-100 group-focus-within:scale-100',
              'transition-all duration-300',
              !hasError && 'group-focus-within:border-[#FF6B00]/30',
              hasError && 'group-focus-within:border-red-500/30'
            )}
          />
        </div>

        {/* Helper text */}
        {(error || success || hint) && (
          <p
            className={clsx(
              'mt-2 flex items-center gap-1.5',
              size === 'sm' && 'text-xs',
              size === 'md' && 'text-sm',
              size === 'lg' && 'text-base',
              error && 'text-red-600',
              success && 'text-green-600',
              hint && !error && !success && 'text-gray-500'
            )}
          >
            {error && <AlertCircle className="w-4 h-4 flex-shrink-0" />}
            {success && <CheckCircle className="w-4 h-4 flex-shrink-0" />}
            {error || success || hint}
          </p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';

export default Input;
