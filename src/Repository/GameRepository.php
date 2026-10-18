<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
final class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * @return list<Game>
     */
    public function search(
        ?string $genre = null,
        ?int $players = null,
        ?float $maxPrice = null,
        ?bool $coop = null,
        ?string $query = null,
        int $limit = 10,
    ): array {
        $qb = $this->createQueryBuilder('g')->orderBy('g.price', 'ASC')->setMaxResults($limit);

        if (null !== $genre && '' !== $genre) {
            $qb->andWhere('LOWER(g.genre) LIKE :genre')->setParameter('genre', '%'.mb_strtolower($genre).'%');
        }
        if (null !== $players) {
            $qb->andWhere('g.minPlayers <= :players AND g.maxPlayers >= :players')->setParameter('players', $players);
        }
        if (null !== $maxPrice) {
            $qb->andWhere('g.price <= :maxPrice')->setParameter('maxPrice', $maxPrice);
        }
        if (null !== $coop) {
            $qb->andWhere('g.coop = :coop')->setParameter('coop', $coop);
        }
        if (null !== $query && '' !== $query) {
            $qb->andWhere('LOWER(g.title) LIKE :query OR LOWER(g.description) LIKE :query')
                ->setParameter('query', '%'.mb_strtolower($query).'%');
        }

        /** @var list<Game> $games */
        $games = $qb->getQuery()->getResult();

        return $games;
    }
}
