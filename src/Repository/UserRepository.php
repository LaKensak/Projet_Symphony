<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findUsersWithPublishedPhotos(): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.photos', 'p')
            ->where('p.displayOrder IS NOT NULL')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }

    public function findRandomUserWithPhotos(): ?User
    {
        $users = $this->findUsersWithPublishedPhotos();
        if (empty($users)) {
            return null;
        }
        return $users[array_rand($users)];
    }

    public function findAllExceptAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->orderBy('u.pseudo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
