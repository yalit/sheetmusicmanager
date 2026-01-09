<?php

namespace App\Repository;

use App\Entity\SetListItem;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<SetListItem>
 */
class SetListItemRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SetListItem::class);
    }
}
