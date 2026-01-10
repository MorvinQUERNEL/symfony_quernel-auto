import { useState } from 'react';
import {
  PaymentElement,
  useStripe,
  useElements,
} from '@stripe/react-stripe-js';
import { Button } from '@/components/ui/Button';
import { Loader2, AlertCircle, CheckCircle } from 'lucide-react';

interface PaymentFormProps {
  onSuccess?: () => void;
  onError?: (error: string) => void;
  returnUrl: string;
}

export function PaymentForm({ onSuccess, onError, returnUrl }: PaymentFormProps) {
  const stripe = useStripe();
  const elements = useElements();
  const [isLoading, setIsLoading] = useState(false);
  const [message, setMessage] = useState<string | null>(null);
  const [isSuccess, setIsSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!stripe || !elements) {
      return;
    }

    setIsLoading(true);
    setMessage(null);

    const { error, paymentIntent } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: returnUrl,
      },
      redirect: 'if_required',
    });

    if (error) {
      if (error.type === 'card_error' || error.type === 'validation_error') {
        setMessage(error.message || 'Une erreur est survenue');
      } else {
        setMessage('Une erreur inattendue est survenue');
      }
      onError?.(error.message || 'Erreur de paiement');
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
      setIsSuccess(true);
      setMessage('Paiement réussi !');
      onSuccess?.();
    }

    setIsLoading(false);
  };

  if (isSuccess) {
    return (
      <div className="text-center py-8">
        <CheckCircle className="h-16 w-16 text-green-500 mx-auto mb-4" />
        <h3 className="text-xl font-semibold text-gray-900 mb-2">
          Paiement réussi !
        </h3>
        <p className="text-gray-600">
          Votre commande a été confirmée. Vous allez recevoir un email de
          confirmation.
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <PaymentElement
        options={{
          layout: 'tabs',
        }}
      />

      {message && (
        <div
          className={`flex items-center gap-2 p-4 rounded-lg ${
            isSuccess
              ? 'bg-green-50 text-green-700'
              : 'bg-red-50 text-red-700'
          }`}
        >
          {isSuccess ? (
            <CheckCircle className="h-5 w-5" />
          ) : (
            <AlertCircle className="h-5 w-5" />
          )}
          <p>{message}</p>
        </div>
      )}

      <Button
        type="submit"
        disabled={isLoading || !stripe || !elements}
        className="w-full"
        size="lg"
      >
        {isLoading ? (
          <>
            <Loader2 className="h-5 w-5 mr-2 animate-spin" />
            Traitement...
          </>
        ) : (
          'Payer'
        )}
      </Button>

      <p className="text-center text-sm text-gray-500">
        Paiement sécurisé par Stripe
      </p>
    </form>
  );
}

export default PaymentForm;
