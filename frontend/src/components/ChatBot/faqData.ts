import type { Language } from '@/i18n';

export interface ChatMessage {
  id: string;
  type: 'user' | 'bot';
  content: string;
  timestamp: Date;
}

interface FAQEntry {
  keywords: {
    fr: string[];
    en: string[];
  };
  response: {
    fr: string;
    en: string;
  };
}

export const faqResponses: FAQEntry[] = [
  // Processus d'achat
  {
    keywords: {
      fr: ['acheter', 'achat', 'commander', 'commande', 'processus', 'étapes', 'comment faire'],
      en: ['buy', 'purchase', 'order', 'process', 'steps', 'how to'],
    },
    response: {
      fr: `Pour acheter un véhicule chez Quernel Auto, c'est simple :

1. Parcourez notre catalogue et choisissez votre véhicule
2. Cliquez sur "Commander" pour créer votre commande
3. Renseignez vos informations de livraison
4. Effectuez le paiement sécurisé (acompte de 5000€)
5. Nous nous occupons de l'importation et de la livraison

Vous pouvez suivre votre commande à tout moment depuis votre espace personnel.`,
      en: `Buying a vehicle at Quernel Auto is simple:

1. Browse our catalog and choose your vehicle
2. Click "Order" to create your order
3. Enter your delivery information
4. Make the secure payment (€5,000 deposit)
5. We handle the import and delivery

You can track your order at any time from your personal space.`,
    },
  },

  // Paiement
  {
    keywords: {
      fr: ['paiement', 'payer', 'carte', 'virement', 'acompte', 'prix', 'coût', 'stripe', 'apple pay'],
      en: ['payment', 'pay', 'card', 'transfer', 'deposit', 'price', 'cost', 'stripe', 'apple pay'],
    },
    response: {
      fr: `Nous acceptons plusieurs moyens de paiement sécurisés :

• Carte bancaire (Visa, Mastercard)
• Apple Pay
• Virement bancaire

Un acompte de 5000€ est demandé à la commande. Le solde est à régler à la livraison du véhicule. Tous les paiements sont sécurisés via Stripe.`,
      en: `We accept several secure payment methods:

• Credit/debit card (Visa, Mastercard)
• Apple Pay
• Bank transfer

A €5,000 deposit is required at order. The balance is due upon vehicle delivery. All payments are secured via Stripe.`,
    },
  },

  // Livraison
  {
    keywords: {
      fr: ['livraison', 'livrer', 'délai', 'délais', 'transport', 'recevoir', 'quand'],
      en: ['delivery', 'deliver', 'delay', 'shipping', 'transport', 'receive', 'when'],
    },
    response: {
      fr: `Les délais de livraison varient selon le véhicule :

• Véhicules en stock : 2 à 4 semaines
• Véhicules sur commande : 4 à 8 semaines

La livraison est effectuée directement à votre domicile ou à l'adresse de votre choix. Vous serez informé à chaque étape du transport.`,
      en: `Delivery times vary depending on the vehicle:

• In-stock vehicles: 2 to 4 weeks
• Custom order vehicles: 4 to 8 weeks

Delivery is made directly to your home or the address of your choice. You will be informed at each stage of transport.`,
    },
  },

  // Garantie
  {
    keywords: {
      fr: ['garantie', 'garanti', 'protection', 'assurance', 'couvert', 'panne'],
      en: ['warranty', 'guarantee', 'protection', 'insurance', 'covered', 'breakdown'],
    },
    response: {
      fr: `Tous nos véhicules sont vendus avec une garantie :

• Garantie constructeur (si encore valide)
• Garantie Quernel Auto de 12 mois minimum
• Extension de garantie disponible jusqu'à 36 mois

La garantie couvre les pannes mécaniques, électriques et électroniques. Les détails sont fournis avec chaque véhicule.`,
      en: `All our vehicles are sold with a warranty:

• Manufacturer warranty (if still valid)
• Quernel Auto warranty of at least 12 months
• Extended warranty available up to 36 months

The warranty covers mechanical, electrical, and electronic failures. Details are provided with each vehicle.`,
    },
  },

  // Origine des véhicules
  {
    keywords: {
      fr: ['origine', 'import', 'importation', 'provenance', 'allemagne', 'europe', 'étranger'],
      en: ['origin', 'import', 'source', 'germany', 'europe', 'foreign'],
    },
    response: {
      fr: `Nos véhicules proviennent principalement :

• D'Allemagne (premier marché européen)
• De Belgique et du Luxembourg
• D'autres pays européens

Tous les véhicules sont vérifiés, révisés et immatriculés en France avant livraison. L'historique complet est fourni (kilométrage certifié, entretiens).`,
      en: `Our vehicles mainly come from:

• Germany (Europe's largest market)
• Belgium and Luxembourg
• Other European countries

All vehicles are inspected, serviced, and registered in France before delivery. Complete history is provided (certified mileage, service records).`,
    },
  },

  // Financement
  {
    keywords: {
      fr: ['financement', 'crédit', 'mensualité', 'prêt', 'loa', 'leasing'],
      en: ['financing', 'credit', 'monthly', 'loan', 'lease', 'leasing'],
    },
    response: {
      fr: `Nous proposons plusieurs solutions de financement :

• Crédit classique avec nos partenaires bancaires
• LOA (Location avec Option d'Achat)
• LLD (Location Longue Durée)

Contactez-nous pour une simulation personnalisée selon votre budget et vos besoins.`,
      en: `We offer several financing solutions:

• Traditional loan with our banking partners
• Lease with purchase option
• Long-term rental

Contact us for a personalized simulation based on your budget and needs.`,
    },
  },

  // Contact
  {
    keywords: {
      fr: ['contact', 'contacter', 'téléphone', 'email', 'adresse', 'joindre', 'appeler'],
      en: ['contact', 'phone', 'email', 'address', 'reach', 'call'],
    },
    response: {
      fr: `Vous pouvez nous contacter de plusieurs façons :

• Par email : contact@quernelauto.fr
• Via le formulaire de contact sur notre site
• Par message depuis votre espace personnel

Notre équipe vous répond sous 24h ouvrées.`,
      en: `You can contact us in several ways:

• By email: contact@quernelauto.fr
• Via the contact form on our website
• By message from your personal space

Our team responds within 24 business hours.`,
    },
  },

  // Compte / Inscription
  {
    keywords: {
      fr: ['compte', 'inscription', 'inscrire', 'créer', 'connexion', 'mot de passe'],
      en: ['account', 'register', 'sign up', 'create', 'login', 'password'],
    },
    response: {
      fr: `Pour créer un compte :

1. Cliquez sur "Inscription" en haut de page
2. Remplissez le formulaire avec vos informations
3. Validez votre email

Votre compte vous permet de :
• Sauvegarder vos véhicules favoris
• Suivre vos commandes
• Contacter notre support
• Gérer vos préférences`,
      en: `To create an account:

1. Click "Register" at the top of the page
2. Fill in the form with your information
3. Validate your email

Your account allows you to:
• Save your favorite vehicles
• Track your orders
• Contact our support
• Manage your preferences`,
    },
  },

  // Reprise
  {
    keywords: {
      fr: ['reprise', 'reprendre', 'échanger', 'échange', 'ancien', 'vendre'],
      en: ['trade-in', 'exchange', 'old', 'sell', 'trade'],
    },
    response: {
      fr: `Nous proposons un service de reprise de votre ancien véhicule :

• Estimation gratuite en ligne ou sur place
• Déduction du montant de votre nouvelle commande
• Procédure simplifiée

Contactez-nous avec les détails de votre véhicule (marque, modèle, année, kilométrage) pour une estimation.`,
      en: `We offer a trade-in service for your old vehicle:

• Free online or on-site estimate
• Deduction from your new order amount
• Simplified procedure

Contact us with your vehicle details (make, model, year, mileage) for an estimate.`,
    },
  },

  // Véhicules disponibles
  {
    keywords: {
      fr: ['disponible', 'stock', 'catalogue', 'véhicule', 'voiture', 'modèle', 'marque'],
      en: ['available', 'stock', 'catalog', 'vehicle', 'car', 'model', 'brand'],
    },
    response: {
      fr: `Notre catalogue propose une large sélection :

• Berlines, SUV, coupés, breaks
• Toutes marques premium (BMW, Mercedes, Audi, etc.)
• Véhicules récents avec faible kilométrage

Utilisez les filtres sur notre page Véhicules pour trouver le modèle idéal. Le catalogue est mis à jour quotidiennement.`,
      en: `Our catalog offers a wide selection:

• Sedans, SUVs, coupes, wagons
• All premium brands (BMW, Mercedes, Audi, etc.)
• Recent vehicles with low mileage

Use the filters on our Vehicles page to find the ideal model. The catalog is updated daily.`,
    },
  },

  // Annulation
  {
    keywords: {
      fr: ['annuler', 'annulation', 'rembourser', 'remboursement', 'rétracter'],
      en: ['cancel', 'cancellation', 'refund', 'withdraw'],
    },
    response: {
      fr: `Concernant l'annulation :

• Vous disposez de 14 jours pour vous rétracter (véhicules sur commande)
• L'acompte est remboursé sous 14 jours ouvrés
• Pour les véhicules déjà en cours d'importation, des frais peuvent s'appliquer

Contactez notre service client pour toute demande d'annulation.`,
      en: `Regarding cancellation:

• You have 14 days to withdraw (custom order vehicles)
• The deposit is refunded within 14 business days
• For vehicles already being imported, fees may apply

Contact our customer service for any cancellation request.`,
    },
  },

  // Documents
  {
    keywords: {
      fr: ['document', 'papier', 'carte grise', 'immatriculation', 'certificat'],
      en: ['document', 'paper', 'registration', 'certificate'],
    },
    response: {
      fr: `À la livraison, vous recevez tous les documents nécessaires :

• Carte grise française à votre nom
• Certificat de conformité européen
• Carnet d'entretien et historique
• Facture d'achat
• Contrat de garantie

L'immatriculation est effectuée par nos soins avant la livraison.`,
      en: `Upon delivery, you receive all necessary documents:

• French registration document in your name
• European certificate of conformity
• Service book and history
• Purchase invoice
• Warranty contract

Registration is done by us before delivery.`,
    },
  },
];

// Default responses
const defaultResponses = {
  fr: `Je n'ai pas trouvé de réponse précise à votre question.

Voici ce que je peux vous aider avec :
• Processus d'achat
• Moyens de paiement
• Délais de livraison
• Garanties
• Financement
• Contact

Vous pouvez également contacter notre équipe via la page Contact ou depuis votre espace Messages si vous êtes connecté.`,
  en: `I couldn't find a precise answer to your question.

Here's what I can help you with:
• Purchase process
• Payment methods
• Delivery times
• Warranties
• Financing
• Contact

You can also contact our team via the Contact page or from your Messages if you're logged in.`,
};

// Welcome message
export const welcomeMessages = {
  fr: "Bonjour ! Je suis l'assistant Quernel Auto. Comment puis-je vous aider ?",
  en: "Hello! I'm the Quernel Auto assistant. How can I help you?",
};

// Quick questions
export const quickQuestions = {
  fr: [
    'Comment acheter un véhicule ?',
    'Quels sont les moyens de paiement ?',
    'Comment fonctionne la livraison ?',
  ],
  en: [
    'How do I buy a vehicle?',
    'What payment methods are available?',
    'How does delivery work?',
  ],
};

// UI texts
export const chatbotTexts = {
  fr: {
    title: 'Assistant Quernel Auto',
    online: 'En ligne - Réponse instantanée',
    placeholder: 'Posez votre question...',
    frequentQuestions: 'Questions fréquentes :',
    close: 'Fermer le chat',
    open: 'Ouvrir le chat',
  },
  en: {
    title: 'Quernel Auto Assistant',
    online: 'Online - Instant response',
    placeholder: 'Ask your question...',
    frequentQuestions: 'Frequent questions:',
    close: 'Close chat',
    open: 'Open chat',
  },
};

// Fonction pour trouver la meilleure réponse
export function findBestResponse(userInput: string, language: Language): string {
  const input = userInput.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  let bestMatch: FAQEntry | null = null;
  let bestScore = 0;

  for (const entry of faqResponses) {
    let score = 0;
    // Check keywords in both languages for better matching
    const allKeywords = [...entry.keywords.fr, ...entry.keywords.en];
    for (const keyword of allKeywords) {
      const normalizedKeyword = keyword.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      if (input.includes(normalizedKeyword)) {
        score += normalizedKeyword.length; // Longer matches are better
      }
    }
    if (score > bestScore) {
      bestScore = score;
      bestMatch = entry;
    }
  }

  if (bestMatch && bestScore > 0) {
    return bestMatch.response[language];
  }

  return defaultResponses[language];
}
