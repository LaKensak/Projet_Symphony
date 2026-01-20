<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(UserRepository $userRepository): Response
    {
        $usersWithPhotos = $userRepository->findUsersWithPublishedPhotos();
        $randomUser = $userRepository->findRandomUserWithPhotos();

        return $this->render('home/index.html.twig', [
            'usersWithPhotos' => $usersWithPhotos,
            'selectedUser' => $randomUser,
        ]);
    }

    #[Route('/galerie/{pseudo}', name: 'app_gallery_public')]
    public function publicGallery(string $pseudo, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['pseudo' => $pseudo]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $usersWithPhotos = $userRepository->findUsersWithPublishedPhotos();

        return $this->render('home/index.html.twig', [
            'usersWithPhotos' => $usersWithPhotos,
            'selectedUser' => $user,
        ]);
    }
}
