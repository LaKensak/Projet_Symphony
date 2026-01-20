<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof User) {
            $user->resetFailedLoginAttempts();
            $this->em->flush();
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $email = $event->getRequest()->request->get('_username');
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user instanceof User && !$user->isBlocked()) {
            $user->incrementFailedLoginAttempts();
            $this->em->flush();

            $attempts = $user->getFailedLoginAttempts();
            $remaining = 3 - $attempts;

            if ($remaining > 0) {
                $session = $this->requestStack->getSession();
                $session->getFlashBag()->add('warning',
                    "Attention : il vous reste $remaining tentative(s) avant le blocage de votre compte."
                );
            }
        }
    }
}
