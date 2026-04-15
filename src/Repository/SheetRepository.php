<?php

namespace App\Repository;

use App\Entity\Sheet\Sheet;
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

        return array_values(array_filter(array_unique(array_merge(...array_column($refs, 'refs'))), fn(string $s) => $s !== ""));
    }

    /**
     * @return string[]
     */
    public function getAllTags(): array
    {
        $tags = $this->createQueryBuilder('s')
            ->select('s.tags')
            ->where('s.tags is NOT NULL')
            ->getQuery()
            ->getResult();

        return array_values(array_filter(array_unique(array_merge(...array_column($tags, 'tags'))), fn(string $s) => $s !== ""));
    }

    /**
     * @return Sheet[]
     */
    public function findByTag(string $tag): array
    {
        $qb = $this->createQueryBuilder('s');

        return $qb
            ->where('s.tags LIKE :tag')
            ->setParameter('tag', '%'.$tag.'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Sheet[]
     */
    public function findUntagged(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.tags is null')
            ->getQuery()
            ->getResult();
    }
}
