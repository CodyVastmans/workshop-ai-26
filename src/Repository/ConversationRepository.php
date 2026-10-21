<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
final class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOwned(int $id, string $ownerTokenHash): ?Conversation
    {
        return $this->findOneBy([
            'id' => $id,
            'ownerTokenHash' => $ownerTokenHash,
        ]);
    }

    /**
     * @return list<Conversation>
     */
    public function findAllOwned(string $ownerTokenHash): array
    {
        /** @var list<Conversation> $conversations */
        $conversations = $this->createQueryBuilder('c')
            ->andWhere('c.ownerTokenHash = :ownerTokenHash')
            ->setParameter('ownerTokenHash', $ownerTokenHash)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $conversations;
    }
}
