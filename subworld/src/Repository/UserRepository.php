<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUnverifiedAfter24Hours(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.verifiedAt IS NULL')
            ->andWhere('u.verificationToken IS NOT NULL')
            ->andWhere('u.createdAt <= :threshold')
            ->setParameter('threshold', new \DateTime('-24 hours'))
            ->getQuery()
            ->getResult();
    }

    public function findUnverifiedAfter48Hours(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.verifiedAt IS NULL')
            ->andWhere('u.createdAt <= :threshold')
            ->setParameter('threshold', new \DateTime('-48 hours'))
            ->getQuery()
            ->getResult();
    }

    public function deleteUnverifiedUsersAfter48Hours(EntityManagerInterface $entityManager): void
    {
        $users = $this->findUnverifiedAfter48Hours();

        foreach ($users as $user) {
            $entityManager->remove($user);
        }

        $entityManager->flush();
    }



//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
