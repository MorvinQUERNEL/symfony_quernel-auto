import { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Mail, Lock, ArrowRight } from 'lucide-react';
import { Button, Input, Card } from '@/components/ui';
import { AuthLayout } from '@/components/layout';
import { useAuthStore } from '@/stores';

const loginSchema = z.object({
  email: z.string().email('Adresse email invalide'),
  password: z.string().min(1, 'Le mot de passe est requis'),
});

type LoginFormData = z.infer<typeof loginSchema>;

export function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const [serverError, setServerError] = useState<string | null>(null);

  const { login, isLoading } = useAuthStore();

  const from = (location.state as { from?: { pathname: string } })?.from?.pathname || '/';

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginFormData) => {
    setServerError(null);
    try {
      await login(data);
      navigate(from, { replace: true });
    } catch (err) {
      const error = err as { message?: string };
      setServerError(error.message || 'Une erreur est survenue');
    }
  };

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
            Connexion
          </h1>
          <p className="mt-2 text-gray-600">
            Accédez à votre espace personnel
          </p>
        </div>

        {/* Error message */}
        {serverError && (
          <div className="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
            <p className="text-sm text-red-600">{serverError}</p>
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
          <Input
            label="Adresse email"
            type="email"
            placeholder="votre@email.com"
            leftIcon={<Mail />}
            error={errors.email?.message}
            {...register('email')}
          />

          <Input
            label="Mot de passe"
            type="password"
            placeholder="••••••••"
            leftIcon={<Lock />}
            error={errors.password?.message}
            {...register('password')}
          />

          {/* Forgot password link */}
          <div className="text-right">
            <Link
              to="/password-reset"
              className="text-sm text-[#FF6B00] hover:underline"
            >
              Mot de passe oublié ?
            </Link>
          </div>

          <Button
            type="submit"
            fullWidth
            size="lg"
            isLoading={isLoading}
            rightIcon={<ArrowRight className="w-5 h-5" />}
          >
            Se connecter
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

        {/* Register link */}
        <p className="text-center text-gray-600">
          Pas encore de compte ?{' '}
          <Link
            to="/register"
            className="font-semibold text-[#FF6B00] hover:underline"
          >
            Créer un compte
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

export default LoginPage;
