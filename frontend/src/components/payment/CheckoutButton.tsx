import { useState } from 'react';
import { Button } from '@/components/ui/Button';
import { useCreateCheckoutSession } from '@/hooks/usePayment';
import { CreditCard, Loader2 } from 'lucide-react';

interface CheckoutButtonProps {
  orderId: number;
  className?: string;
  disabled?: boolean;
}

export function CheckoutButton({
  orderId,
  className,
  disabled,
}: CheckoutButtonProps) {
  const [isLoading, setIsLoading] = useState(false);
  const createCheckoutSession = useCreateCheckoutSession();

  const handleCheckout = async () => {
    setIsLoading(true);
    try {
      const session = await createCheckoutSession.mutateAsync(orderId);

      // Redirect to Stripe Checkout URL
      if (session.url) {
        window.location.href = session.url;
      }
    } catch (error) {
      console.error('Checkout error:', error);
      setIsLoading(false);
    }
  };

  return (
    <Button
      onClick={handleCheckout}
      disabled={disabled || isLoading || createCheckoutSession.isPending}
      className={className}
    >
      {isLoading || createCheckoutSession.isPending ? (
        <>
          <Loader2 className="h-4 w-4 mr-2 animate-spin" />
          Redirection...
        </>
      ) : (
        <>
          <CreditCard className="h-4 w-4 mr-2" />
          Payer maintenant
        </>
      )}
    </Button>
  );
}

export default CheckoutButton;
