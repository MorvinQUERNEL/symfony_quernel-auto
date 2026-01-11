import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Mail, Lock, User, Phone, ArrowRight, CheckCircle, AlertCircle } from 'lucide-react';
import { Button, Input, Card } from '@/components/ui';
import { AuthLayout } from '@/components/layout';
import { useAuthStore } from '@/stores';
import { useToast } from '@/hooks/useToast';

const registerSchema = z.object({
  firstname: z.string().min(2, 'Le prénom doit contenir au moins 2 caractères').max(50, 'Le prénom ne peut pas dépasser 50 caractères'),
  lastname: z.string().min(2, 'Le nom doit contenir au moins 2 caractères').max(50, 'Le nom ne peut pas dépasser 50 caractères'),
  email: z.string().email('Adresse email invalide'),
  phone: z.string().optional(),
  password: z.string()
    .min(8, 'Le mot de passe doit contenir au moins 8 caractères')
    .regex(/[A-Z]/, 'Le mot de passe doit contenir au moins une majuscule')
    .regex(/[0-9]/, 'Le mot de passe doit contenir au moins un chiffre'),
  confirmPassword: z.string(),
}).refine((data) => data.password === data.confirmPassword, {
  message: 'Les mots de passe ne correspondent pas',
  path: ['confirmPassword'],
});

type RegisterFormData = z.infer<typeof registerSchema>;

interface ServerErrors {
  [key: string]: string;
}

export function RegisterPage() {
  const [serverError, setServerError] = useState<string | null>(null);
  const [serverFieldErrors, setServerFieldErrors] = useState<ServerErrors>({});
  const [isSuccess, setIsSuccess] = useState(false);
  const { toast } = useToast();

  const { register: registerUser, isLoading } = useAuthStore();

  const {
    register,
    handleSubmit,
    formState: { errors },
    setError,
  } = useForm<RegisterFormData>({
    resolver: zodResolver(registerSchema),
  });

  const onSubmit = async (data: RegisterFormData) => {
    setServerError(null);
    setServerFieldErrors({});
    try {
      await registerUser({
        firstname: data.firstname,
        lastname: data.lastname,
        email: data.email,
        phone: data.phone,
        password: data.password,
      });
      setIsSuccess(true);
      toast.success('Compte créé avec succès !');
    } catch (err) {
      const error = err as { message?: string; errors?: ServerErrors };

      // Si le serveur retourne des erreurs par champ
      if (error.errors) {
        setServerFieldErrors(error.errors);
        // Mapper les erreurs du serveur aux champs du formulaire
        Object.entries(error.errors).forEach(([field, message]) => {
          if (field in data) {
            setError(field as keyof RegisterFormData, {
              type: 'server',
              message: message
            });
          }
        });
      }

      setServerError(error.message || 'Une erreur est survenue lors de l\'inscription');
      toast.error(error.message || 'Erreur lors de l\'inscription');
    }
  };

  if (isSuccess) {
    return (
      <AuthLayout>
        <Card variant="elevated" padding="lg" className="text-center">
          <div className="w-16 h-16 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
            <CheckCircle className="w-8 h-8 text-green-600" />
          </div>

          <h1 className="text-2xl font-bold text-gray-900 mb-2">
            Inscription réussie !
          </h1>
          <p className="text-gray-600 mb-8">
            Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.
          </p>

          <Link to="/login">
            <Button fullWidth rightIcon={<ArrowRight className="w-5 h-5" />}>
              Se connecter
            </Button>
          </Link>
        </Card>
      </AuthLayout>
    );
  }

  return (
    <AuthLayout>
      {/* Logo */}
      <Link to="/" className="flex items-center justify-center gap-3 mb-8 group">
        <div className="relative w-12 h-12 flex items-center justify-center">
          <div className="absolute inset-0 bg-gradient-to-br from-[#FF6B00] to-[#FF8533] rounded-xl rotate-6 group-hover:rotate-12 transition-transform duration-300" />
          <span className="relative text-white font-black text-2xl">Q</span>
        </div>
        <div>
          <span className="text-2xl font-black text-gray-900 tracking-tight">QUERNEL</span>
          <span className="text-2xl font-light text-[#FF6B00] tracking-tight ml-1">AUTO</span>
        </div>
      </Link>

      <Card variant="elevated" padding="lg" className="w-full">
        {/* Header */}
        <div className="text-center mb-8">
          <h1 className="text-2xl font-bold text-gray-900">
            Créer un compte
          </h1>
          <p className="mt-2 text-gray-600">
            Rejoignez Quernel Auto dès maintenant
          </p>
        </div>

        {/* Error message */}
        {serverError && (
          <div className="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
            <div className="flex items-start gap-3">
              <AlertCircle className="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
              <div>
                <p className="font-medium text-red-800">{serverError}</p>
                {Object.keys(serverFieldErrors).length > 0 && (
                  <ul className="mt-2 text-sm text-red-600 list-disc list-inside">
                    {Object.entries(serverFieldErrors).map(([field, message]) => (
                      <li key={field}>{message}</li>
                    ))}
                  </ul>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
          <div className="grid grid-cols-2 gap-4">
            <Input
              label="Prénom"
              placeholder="Jean"
              leftIcon={<User />}
              error={errors.firstname?.message}
              {...register('firstname')}
            />

            <Input
              label="Nom"
              placeholder="Dupont"
              leftIcon={<User />}
              error={errors.lastname?.message}
              {...register('lastname')}
            />
          </div>

          <Input
            label="Adresse email"
            type="email"
            placeholder="votre@email.com"
            leftIcon={<Mail />}
            error={errors.email?.message}
            {...register('email')}
          />

          <Input
            label="Téléphone (optionnel)"
            type="tel"
            placeholder="+33 6 12 34 56 78"
            leftIcon={<Phone />}
            error={errors.phone?.message}
            {...register('phone')}
          />

          <Input
            label="Mot de passe"
            type="password"
            placeholder="••••••••"
            leftIcon={<Lock />}
            error={errors.password?.message}
            hint="8 caractères min., 1 majuscule, 1 chiffre"
            {...register('password')}
          />

          <Input
            label="Confirmer le mot de passe"
            type="password"
            placeholder="••••••••"
            leftIcon={<Lock />}
            error={errors.confirmPassword?.message}
            {...register('confirmPassword')}
          />

          {/* Terms */}
          <p className="text-xs text-gray-500">
            En créant un compte, vous acceptez nos{' '}
            <Link to="/cgv" className="text-[#FF6B00] hover:underline">
              conditions générales
            </Link>{' '}
            et notre{' '}
            <Link to="/politique-confidentialite" className="text-[#FF6B00] hover:underline">
              politique de confidentialité
            </Link>.
          </p>

          <Button
            type="submit"
            fullWidth
            size="lg"
            isLoading={isLoading}
            rightIcon={<ArrowRight className="w-5 h-5" />}
          >
            Créer mon compte
          </Button>
        </form>

        {/* Divider */}
        <div className="relative my-8">
          <div className="absolute inset-0 flex items-center">
            <div className="w-full border-t border-gray-200" />
          </div>
          <div className="relative flex justify-center">
            <span className="bg-white px-4 text-sm text-gray-500">ou</span>
          </div>
        </div>

        {/* Login link */}
        <p className="text-center text-gray-600">
          Déjà un compte ?{' '}
          <Link
            to="/login"
            className="font-semibold text-[#FF6B00] hover:underline"
          >
            Se connecter
          </Link>
        </p>
      </Card>

      {/* Back to home */}
      <p className="mt-8 text-center">
        <Link
          to="/"
          className="text-sm text-gray-500 hover:text-gray-700 transition-colors"
        >
          ← Retour à l'accueil
        </Link>
      </p>
    </AuthLayout>
  );
}

export default RegisterPage;
