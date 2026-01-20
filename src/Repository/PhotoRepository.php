<?php

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.displayOrder IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('p.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Photo $photo, bool $flush = false): void
    {
        $this->getEntityManager()->persist($photo);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Photo $photo, bool $flush = false): void
    {
        $this->getEntityManager()->remove($photo);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
