<?php

namespace App\Repository;

use App\Entity\CreditedPerson;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<CreditedPerson>
 */
class CreditedPersonRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreditedPerson::class);
    }
}
