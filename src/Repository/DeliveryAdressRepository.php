<?php

namespace App\Repository;

use App\Entity\DeliveryAdress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeliveryAdress|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryAdress|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryAdress[]    findAll()
 * @method DeliveryAdress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryAdressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryAdress::class);
    }

    // /**
    //  * @return DeliveryAdress[] Returns an array of DeliveryAdress objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeliveryAdress
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
