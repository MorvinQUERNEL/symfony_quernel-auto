import { Link } from 'react-router-dom';
import { XCircle, ArrowLeft, MessageCircle } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';

export function PaymentCancel() {
  return (
    <div className="min-h-[80vh] flex items-center justify-center px-4">
      <Card className="max-w-lg w-full text-center">
        <CardContent className="py-12">
          <div className="mb-6">
            <div className="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
              <XCircle className="h-10 w-10 text-red-600" />
            </div>
          </div>

          <h1 className="text-2xl font-bold text-gray-900 mb-2">
            Paiement annulé
          </h1>

          <p className="text-gray-600 mb-8">
            Votre paiement a été annulé. Aucun montant n'a été débité de votre
            compte. Votre commande reste en attente.
          </p>

          <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-8">
            <p className="text-amber-800 text-sm">
              Si vous avez rencontré un problème technique ou si vous avez
              des questions, n'hésitez pas à nous contacter.
            </p>
          </div>

          <div className="flex flex-col sm:flex-row gap-4">
            <Link
              to="/orders"
              className="flex-1 inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors"
            >
              <ArrowLeft className="h-4 w-4 mr-2" />
              Retour aux commandes
            </Link>
            <Link
              to="/contact"
              className="flex-1 inline-flex items-center justify-center px-6 py-3 bg-brand text-white font-semibold rounded-xl hover:bg-brand-dark transition-colors"
            >
              <MessageCircle className="h-4 w-4 mr-2" />
              Nous contacter
            </Link>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

export default PaymentCancel;
