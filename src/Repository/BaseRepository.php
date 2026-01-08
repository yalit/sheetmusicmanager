<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of object
 * @extends ServiceEntityRepository<T>
 */
class BaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function save(T $organization, bool $flush = false): void
    {
        $this->getEntityManager()->persist($organization);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(T $organization, bool $flush = false): void
    {
        $this->getEntityManager()->remove($organization);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
