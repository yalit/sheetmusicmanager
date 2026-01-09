<?php

namespace App\Repository;

use App\Entity\Sheet;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<Sheet>
 */
class SheetRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sheet::class);
    }

}
