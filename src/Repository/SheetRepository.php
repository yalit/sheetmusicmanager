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

    /**
     * @return string[]
     */
    public function getAllRefs(): array
    {
        $refs = $this->createQueryBuilder('s')
            ->select('s.refs')
            ->where('s.refs IS NOT NULL')
            ->getQuery()
            ->getResult();

        return array_values(array_unique(array_merge(...array_column($refs, 'refs'))));
    }

    /**
     * @return string[]
     */
    public function getAllTags(): array
    {
        $tags = $this->createQueryBuilder('s')
            ->select('s.tags')
            ->where('s.tags IS NOT NULL')
            ->getQuery()
            ->getResult();

        return array_values(array_unique(array_merge(...array_column($tags, 'tags'))));
    }
}
