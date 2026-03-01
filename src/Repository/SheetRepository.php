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
        $tags = $this->createQueryBuilder('s')
            ->select('s.refs')
            ->getQuery()
            ->getResult();

        return array_unique(array_merge(...array_column($tags, 'refs')));
    }

    /**
     * @return string[]
     */
    public function getAllGenres(): array
    {
        $results = $this->createQueryBuilder('s')
            ->select('s.genre')
            ->getQuery()
            ->getResult();

        return array_values(array_column($results, 'genre'));
    }
}
