<?php

namespace App\Controller;

use App\Entity\Preference;
use App\Form\PreferenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PreferenceController extends AbstractController
{
    #[Route('/preference/ajouter', name: 'app_preference_add')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function add(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $preference = new Preference();
        $form = $this->createForm(PreferenceType::class, $preference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $preference->setUsers($this->getUser());
            $em->persist($preference);
            $em->flush();
            $this->addFlash('success', 'Préférence enregistrée avec succès !');

            // Envoi d'un email de confirmation
            $user = $this->getUser();
            $email = (new Email())
                ->from('no-reply@quernel-auto.fr')
                ->to($user->getEmail())
                ->subject('Confirmation de votre préférence véhicule')
                ->html($this->renderView('emails/preference_confirmation.html.twig', [
                    'user' => $user,
                    'preference' => $preference,
                ]));
            $mailer->send($email);

            return $this->redirectToRoute('app_profile');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Veuillez remplir correctement tous les champs obligatoires.');
        }

        return $this->render('preference/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 