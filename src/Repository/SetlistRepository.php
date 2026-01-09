<?php

namespace App\Repository;

use App\Entity\Setlist;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends  BaseRepository<Setlist>
 */
class SetlistRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setlist::class);
    }
}
