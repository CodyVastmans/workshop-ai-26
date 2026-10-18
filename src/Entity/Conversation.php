<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * Samenvatting van de berichten die niet meer naar het model gaan (zie HistoryCompactor).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    /** @var Collection<int, Message> */
    #[ORM\OneToMany(
        targetEntity: Message::class,
        mappedBy: 'conversation',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): void
    {
        $this->summary = $summary;
    }

    /**
     * @return list<Message>
     */
    public function getMessages(): array
    {
        return $this->messages->getValues();
    }

    public function addMessage(Message $message): void
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
        }
    }
}
