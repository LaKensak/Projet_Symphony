<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_gallery');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $session = $request->getSession();

        // Stocker l'URL de référence à la première visite du formulaire
        if (!$request->isMethod('POST') && !$session->has('_register_referer')) {
            $referer = $request->headers->get('referer');
            if ($referer && !str_contains($referer, '/register') && !str_contains($referer, '/login')) {
                $session->set('_register_referer', $referer);
            }
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $generatedPassword = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $captchaResponse = $form->get('captcha')->getData();
            if ($captchaResponse != '7') {
                $this->addFlash('error', 'Mauvaise réponse au captcha.');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $generatedPassword = $this->generatePassword();
            $hashedPassword = $passwordHasher->hashPassword($user, $generatedPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Compte créé ! Votre mot de passe est : ' . $generatedPassword);

            // Rediriger vers la page précédente ou home
            $redirectUrl = $session->get('_register_referer', $this->generateUrl('app_home'));
            $session->remove('_register_referer');

            return $this->redirect($redirectUrl);
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    private function generatePassword(int $length = 10): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
