<?php

namespace App\Repository;

use App\Entity\PublicService;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicService>
 *
 * @method PublicService|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicService|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicService[]    findAll()
 * @method PublicService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicService::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(PublicService $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(PublicService $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return PublicService[] Returns an array of PublicService objects
     */
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.institution', 'i')
            ->innerJoin('i.members', 'm')
            ->andWhere('m = :user')
            ->setParameter('user', $user)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return PublicService[] Returns an array of PublicService objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PublicService
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
