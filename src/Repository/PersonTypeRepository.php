<?php

namespace App\Repository;

use App\Entity\PersonType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<PersonType>
 */
class PersonTypeRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonType::class);
    }
}
