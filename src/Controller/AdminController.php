<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(Request $request, UsersRepository $usersRepository): Response
    {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $users = $usersRepository->findBySearch($search);
        } else {
            $users = $usersRepository->findAllWithPreferences();
        }

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'search' => $search,
        ]);
    }
} 