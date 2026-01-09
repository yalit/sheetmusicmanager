<?php

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<Organization>
 */
class OrganizationRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }
}
