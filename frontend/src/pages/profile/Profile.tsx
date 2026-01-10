import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation } from '@tanstack/react-query';
import { User, Mail, Phone, MapPin, Save, Lock } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Button, Input, Card, CardHeader, CardTitle, CardContent } from '@/components/ui';
import { useAuthStore } from '@/stores';
import { usersApi } from '@/api';

const profileSchema = z.object({
  firstname: z.string().min(2, 'Le prénom doit contenir au moins 2 caractères'),
  lastname: z.string().min(2, 'Le nom doit contenir au moins 2 caractères'),
  email: z.string().email('Adresse email invalide'),
  phoneNumber: z.string().optional(),
  address: z.string().optional(),
  city: z.string().optional(),
  postalCode: z.string().optional(),
  country: z.string().optional(),
});

type ProfileFormData = z.infer<typeof profileSchema>;

const passwordSchema = z.object({
  currentPassword: z.string().min(1, 'Le mot de passe actuel est requis'),
  newPassword: z.string()
    .min(8, 'Le mot de passe doit contenir au moins 8 caractères')
    .regex(/[A-Z]/, 'Le mot de passe doit contenir au moins une majuscule')
    .regex(/[0-9]/, 'Le mot de passe doit contenir au moins un chiffre'),
  confirmPassword: z.string(),
}).refine((data) => data.newPassword === data.confirmPassword, {
  message: 'Les mots de passe ne correspondent pas',
  path: ['confirmPassword'],
});

type PasswordFormData = z.infer<typeof passwordSchema>;

export function ProfilePage() {
  const { user, fetchUser } = useAuthStore();
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  const {
    register: registerProfile,
    handleSubmit: handleProfileSubmit,
    formState: { errors: profileErrors },
  } = useForm<ProfileFormData>({
    resolver: zodResolver(profileSchema),
    defaultValues: {
      firstname: user?.firstname || '',
      lastname: user?.lastname || '',
      email: user?.email || '',
      phoneNumber: user?.phoneNumber || '',
      address: user?.address || '',
      city: user?.city || '',
      postalCode: user?.postalCode?.toString() || '',
      country: user?.country || '',
    },
  });

  const {
    register: registerPassword,
    handleSubmit: handlePasswordSubmit,
    formState: { errors: passwordErrors },
    reset: resetPassword,
  } = useForm<PasswordFormData>({
    resolver: zodResolver(passwordSchema),
  });

  const updateProfileMutation = useMutation({
    mutationFn: (data: ProfileFormData) => {
      // Convert postalCode from string to number for API
      const apiData = {
        ...data,
        postalCode: data.postalCode ? parseInt(data.postalCode, 10) : undefined,
      };
      return usersApi.updateProfile(apiData);
    },
    onSuccess: () => {
      fetchUser();
      setSuccessMessage('Profil mis à jour avec succès');
      setTimeout(() => setSuccessMessage(null), 3000);
    },
  });

  const changePasswordMutation = useMutation({
    mutationFn: (data: PasswordFormData) =>
      usersApi.changePassword(data.currentPassword, data.newPassword),
    onSuccess: () => {
      resetPassword();
      setSuccessMessage('Mot de passe modifié avec succès');
      setTimeout(() => setSuccessMessage(null), 3000);
    },
  });

  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen py-12">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl font-black text-gray-900 mb-8">Mon profil</h1>

          {successMessage && (
            <div className="mb-6 p-4 rounded-xl bg-green-50 border border-green-200">
              <p className="text-sm text-green-600">{successMessage}</p>
            </div>
          )}

          {/* Profile form */}
          <Card variant="elevated" className="mb-8">
            <CardHeader>
              <CardTitle>Informations personnelles</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleProfileSubmit((data) => updateProfileMutation.mutate(data))} className="space-y-5">
                <div className="grid grid-cols-2 gap-4">
                  <Input
                    label="Prénom"
                    leftIcon={<User />}
                    error={profileErrors.firstname?.message}
                    {...registerProfile('firstname')}
                  />
                  <Input
                    label="Nom"
                    leftIcon={<User />}
                    error={profileErrors.lastname?.message}
                    {...registerProfile('lastname')}
                  />
                </div>

                <Input
                  label="Email"
                  type="email"
                  leftIcon={<Mail />}
                  error={profileErrors.email?.message}
                  {...registerProfile('email')}
                />

                <Input
                  label="Téléphone"
                  type="tel"
                  leftIcon={<Phone />}
                  error={profileErrors.phoneNumber?.message}
                  {...registerProfile('phoneNumber')}
                />

                <Input
                  label="Adresse"
                  leftIcon={<MapPin />}
                  error={profileErrors.address?.message}
                  {...registerProfile('address')}
                />

                <div className="grid grid-cols-2 gap-4">
                  <Input
                    label="Ville"
                    error={profileErrors.city?.message}
                    {...registerProfile('city')}
                  />
                  <Input
                    label="Code postal"
                    error={profileErrors.postalCode?.message}
                    {...registerProfile('postalCode')}
                  />
                </div>

                <Input
                  label="Pays"
                  error={profileErrors.country?.message}
                  {...registerProfile('country')}
                />

                <div className="pt-4">
                  <Button
                    type="submit"
                    isLoading={updateProfileMutation.isPending}
                    leftIcon={<Save className="w-5 h-5" />}
                  >
                    Enregistrer les modifications
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>

          {/* Password form */}
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>Changer le mot de passe</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handlePasswordSubmit((data) => changePasswordMutation.mutate(data))} className="space-y-5">
                <Input
                  label="Mot de passe actuel"
                  type="password"
                  leftIcon={<Lock />}
                  error={passwordErrors.currentPassword?.message}
                  {...registerPassword('currentPassword')}
                />

                <Input
                  label="Nouveau mot de passe"
                  type="password"
                  leftIcon={<Lock />}
                  error={passwordErrors.newPassword?.message}
                  hint="8 caractères min., 1 majuscule, 1 chiffre"
                  {...registerPassword('newPassword')}
                />

                <Input
                  label="Confirmer le nouveau mot de passe"
                  type="password"
                  leftIcon={<Lock />}
                  error={passwordErrors.confirmPassword?.message}
                  {...registerPassword('confirmPassword')}
                />

                {changePasswordMutation.error && (
                  <div className="p-4 rounded-xl bg-red-50 border border-red-200">
                    <p className="text-sm text-red-600">
                      {(changePasswordMutation.error as { message?: string })?.message || 'Une erreur est survenue'}
                    </p>
                  </div>
                )}

                <div className="pt-4">
                  <Button
                    type="submit"
                    variant="secondary"
                    isLoading={changePasswordMutation.isPending}
                    leftIcon={<Lock className="w-5 h-5" />}
                  >
                    Changer le mot de passe
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </Layout>
  );
}

export default ProfilePage;
