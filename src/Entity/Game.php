<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
final class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column]
        private string $title,
        #[ORM\Column]
        private string $genre,
        #[ORM\Column]
        private int $minPlayers,
        #[ORM\Column]
        private int $maxPlayers,
        #[ORM\Column]
        private float $price,
        #[ORM\Column]
        private string $platforms,
        #[ORM\Column]
        private bool $coop,
        #[ORM\Column(type: Types::TEXT)]
        private string $description,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getGenre(): string
    {
        return $this->genre;
    }

    public function getMinPlayers(): int
    {
        return $this->minPlayers;
    }

    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getPlatforms(): string
    {
        return $this->platforms;
    }

    public function isCoop(): bool
    {
        return $this->coop;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
