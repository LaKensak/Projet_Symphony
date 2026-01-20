<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAllExceptAdmins();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $photosDir = $this->getParameter('photos_directory');
            foreach ($user->getPhotos() as $photo) {
                $filepath = $photosDir . '/' . $photo->getFilename();
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }

            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/user/{id}/unblock', name: 'app_admin_user_unblock', methods: ['POST'])]
    public function unblockUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('unblock' . $user->getId(), $request->request->get('_token'))) {
            $user->setIsBlocked(false);
            $user->setFailedLoginAttempts(0);
            $em->flush();

            $this->addFlash('success', 'Utilisateur débloqué.');
        }

        return $this->redirectToRoute('app_admin');
    }
}
