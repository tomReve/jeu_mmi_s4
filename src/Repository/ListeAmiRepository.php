<?php

namespace App\Repository;

use App\Entity\ListeAmi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ListeAmi|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeAmi|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeAmi[]    findAll()
 * @method ListeAmi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeAmiRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ListeAmi::class);
    }

    // /**
    //  * @return ListeAmi[] Returns an array of ListeAmi objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ListeAmi
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
