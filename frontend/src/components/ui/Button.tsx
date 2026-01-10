import { forwardRef, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { clsx } from 'clsx';
import { Spinner } from './Spinner';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
  leftIcon?: ReactNode;
  rightIcon?: ReactNode;
  fullWidth?: boolean;
}

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      children,
      variant = 'primary',
      size = 'md',
      isLoading = false,
      leftIcon,
      rightIcon,
      fullWidth = false,
      disabled,
      className,
      ...props
    },
    ref
  ) => {
    const baseStyles = `
      relative inline-flex items-center justify-center gap-2
      font-semibold tracking-wide uppercase
      transition-all duration-300 ease-out
      focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
      disabled:cursor-not-allowed
      overflow-hidden
      group
    `;

    const variants = {
      primary: `
        bg-gradient-to-r from-[#FF6B00] to-[#FF8533]
        text-white
        shadow-[0_4px_20px_rgba(255,107,0,0.4)]
        hover:shadow-[0_6px_30px_rgba(255,107,0,0.6)]
        hover:translate-y-[-2px]
        active:translate-y-0
        active:shadow-[0_2px_10px_rgba(255,107,0,0.4)]
        focus-visible:ring-[#FF6B00]
        disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-[0_4px_20px_rgba(255,107,0,0.4)]
        before:absolute before:inset-0 before:bg-gradient-to-r before:from-[#FF8533] before:to-[#FFAA00]
        before:opacity-0 before:transition-opacity before:duration-300
        hover:before:opacity-100
      `,
      secondary: `
        bg-gray-900 text-white
        border border-gray-700
        shadow-[0_4px_15px_rgba(0,0,0,0.3)]
        hover:bg-gray-800 hover:border-gray-600
        hover:shadow-[0_6px_20px_rgba(0,0,0,0.4)]
        hover:translate-y-[-2px]
        active:translate-y-0
        focus-visible:ring-gray-500
        disabled:opacity-50 disabled:hover:translate-y-0
      `,
      outline: `
        bg-transparent
        text-[#FF6B00]
        border-2 border-[#FF6B00]
        hover:bg-[#FF6B00] hover:text-white
        hover:shadow-[0_4px_20px_rgba(255,107,0,0.4)]
        hover:translate-y-[-2px]
        active:translate-y-0
        focus-visible:ring-[#FF6B00]
        disabled:opacity-50 disabled:hover:bg-transparent disabled:hover:text-[#FF6B00] disabled:hover:translate-y-0
      `,
      ghost: `
        bg-transparent
        text-gray-700
        hover:bg-gray-100 hover:text-gray-900
        active:bg-gray-200
        focus-visible:ring-gray-400
        disabled:opacity-50
      `,
      danger: `
        bg-gradient-to-r from-red-600 to-red-500
        text-white
        shadow-[0_4px_20px_rgba(220,38,38,0.4)]
        hover:shadow-[0_6px_30px_rgba(220,38,38,0.6)]
        hover:translate-y-[-2px]
        active:translate-y-0
        focus-visible:ring-red-500
        disabled:opacity-50 disabled:hover:translate-y-0
      `,
    };

    const sizes = {
      sm: 'px-4 py-2 text-xs rounded-lg',
      md: 'px-6 py-3 text-sm rounded-xl',
      lg: 'px-8 py-4 text-base rounded-xl',
    };

    return (
      <button
        ref={ref}
        disabled={disabled || isLoading}
        className={clsx(
          baseStyles,
          variants[variant],
          sizes[size],
          fullWidth && 'w-full',
          className
        )}
        {...props}
      >
        {/* Shine effect on hover */}
        <span className="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:translate-x-full transition-transform duration-700 ease-out" />

        {/* Content */}
        <span className="relative z-10 flex items-center gap-2">
          {isLoading ? (
            <Spinner size={size === 'lg' ? 'md' : 'sm'} />
          ) : (
            leftIcon
          )}
          {children}
          {!isLoading && rightIcon}
        </span>
      </button>
    );
  }
);

Button.displayName = 'Button';

export default Button;
