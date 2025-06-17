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

class PreferenceController extends AbstractController
{
    #[Route('/preference/ajouter', name: 'app_preference_add')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $preference = new Preference();
        $form = $this->createForm(PreferenceType::class, $preference);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $preference->setUsers($this->getUser());
                $em->persist($preference);
                $em->flush();
                $this->addFlash('success', 'Préférence enregistrée avec succès !');
                return $this->redirectToRoute('app_profile');
            } else {
                $this->addFlash('danger', 'Veuillez remplir correctement tous les champs obligatoires.');
            }
        }

        return $this->render('preference/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 