<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<Person>
 */
class PersonRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }
}
