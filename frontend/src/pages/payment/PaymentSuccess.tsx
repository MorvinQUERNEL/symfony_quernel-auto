import { useEffect } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { CheckCircle, ArrowRight, Mail, FileText } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';

export function PaymentSuccess() {
  const [searchParams] = useSearchParams();
  const sessionId = searchParams.get('session_id');

  useEffect(() => {
    // Analytics or tracking could be added here
    console.log('Payment successful, session:', sessionId);
  }, [sessionId]);

  return (
    <div className="min-h-[80vh] flex items-center justify-center px-4">
      <Card className="max-w-lg w-full text-center">
        <CardContent className="py-12">
          <div className="mb-6">
            <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
              <CheckCircle className="h-10 w-10 text-green-600" />
            </div>
          </div>

          <h1 className="text-2xl font-bold text-gray-900 mb-2">
            Paiement réussi !
          </h1>

          <p className="text-gray-600 mb-8">
            Merci pour votre achat. Votre commande a été confirmée et est en
            cours de traitement.
          </p>

          <div className="bg-gray-50 rounded-lg p-6 mb-8">
            <h3 className="font-medium text-gray-900 mb-4">
              Prochaines étapes
            </h3>
            <div className="space-y-4 text-left">
              <div className="flex items-start gap-3">
                <div className="w-8 h-8 bg-brand/10 rounded-full flex items-center justify-center flex-shrink-0">
                  <Mail className="h-4 w-4 text-brand" />
                </div>
                <div>
                  <p className="font-medium text-gray-900">
                    Email de confirmation
                  </p>
                  <p className="text-sm text-gray-600">
                    Vous allez recevoir un email avec les détails de votre
                    commande.
                  </p>
                </div>
              </div>

              <div className="flex items-start gap-3">
                <div className="w-8 h-8 bg-brand/10 rounded-full flex items-center justify-center flex-shrink-0">
                  <FileText className="h-4 w-4 text-brand" />
                </div>
                <div>
                  <p className="font-medium text-gray-900">
                    Suivi de commande
                  </p>
                  <p className="text-sm text-gray-600">
                    Vous pouvez suivre l'avancement de votre commande depuis
                    votre espace personnel.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row gap-4">
            <Link
              to="/orders"
              className="flex-1 inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors"
            >
              Voir mes commandes
            </Link>
            <Link
              to="/vehicules"
              className="flex-1 inline-flex items-center justify-center px-6 py-3 bg-brand text-white font-semibold rounded-xl hover:bg-brand-dark transition-colors"
            >
              Continuer à explorer
              <ArrowRight className="h-4 w-4 ml-2" />
            </Link>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

export default PaymentSuccess;
