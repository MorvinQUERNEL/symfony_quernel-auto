<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProcessusController extends AbstractController
{
    #[Route('/processus', name: 'app_processus')]
    public function index(): Response
    {
        return $this->render('processus/index.html.twig', [
            'controller_name' => 'ProcessusController',
        ]);
    }
} 