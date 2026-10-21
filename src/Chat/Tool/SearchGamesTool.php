<?php

declare(strict_types=1);

namespace App\Chat\Tool;

use Anthropic\Messages\Tool;
use Anthropic\Messages\Tool\InputSchema;
use App\Entity\Game;
use App\Repository\GameRepository;

final class SearchGamesTool
{
    public function __construct(
        private readonly GameRepository $games,
    ) {
    }

    public function definition(): Tool
    {
        return Tool::with(
            name: 'search_games',
            inputSchema: InputSchema::with(properties: [
                'query' => [
                    'type' => 'string',
                    'description' => 'Zoekterm in de titel of beschrijving.',
                ],
                'genre' => [
                    'type' => 'string',
                    'description' => 'Genre, bijvoorbeeld party, horror of rpg.',
                ],
                'players' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'description' => 'Exact aantal spelers dat moet passen binnen het spelersbereik van de game.',
                ],
                'max_price' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'description' => 'Maximale prijs in euro.',
                ],
                'coop' => [
                    'type' => 'boolean',
                    'description' => 'Filter op games met (true) of zonder (false) co-op.',
                ],
            ]),
        )->withDescription(
            'Zoek games in de workshopcatalogus. Gebruik filters op genre, spelersaantal, maximumprijs of co-op. Laat filters weg om de catalogus te verkennen.',
        );
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return list<array{
     *     title: string,
     *     genre: string,
     *     minPlayers: int,
     *     maxPlayers: int,
     *     price: float,
     *     platforms: string,
     *     coop: bool,
     *     description: string
     * }>
     */
    public function search(array $input): array
    {
        $allowedKeys = ['query', 'genre', 'players', 'max_price', 'coop'];
        foreach ($input as $key => $value) {
            if (!in_array($key, $allowedKeys, true)) {
                throw new \InvalidArgumentException(\sprintf('Onbekend gamefilter: %s.', $key));
            }

            if ('query' === $key || 'genre' === $key) {
                if (!is_string($value)) {
                    throw new \InvalidArgumentException(\sprintf('Filter "%s" moet tekst zijn.', $key));
                }
            } elseif ('players' === $key) {
                if (!is_int($value) || $value < 1) {
                    throw new \InvalidArgumentException('Filter "players" moet een positief geheel getal zijn.');
                }
            } elseif ('max_price' === $key) {
                if ((!is_float($value) && !is_int($value)) || $value < 0) {
                    throw new \InvalidArgumentException('Filter "max_price" moet een positief getal zijn.');
                }
            } elseif ('coop' === $key && !is_bool($value)) {
                throw new \InvalidArgumentException('Filter "coop" moet true of false zijn.');
            }
        }

        $query = $input['query'] ?? null;
        $genre = $input['genre'] ?? null;
        $players = $input['players'] ?? null;
        $maxPrice = $input['max_price'] ?? null;
        $coop = $input['coop'] ?? null;

        if (null !== $query && !is_string($query)) {
            throw new \InvalidArgumentException('Filter "query" moet tekst zijn.');
        }
        if (null !== $genre && !is_string($genre)) {
            throw new \InvalidArgumentException('Filter "genre" moet tekst zijn.');
        }
        if (null !== $players && !is_int($players)) {
            throw new \InvalidArgumentException('Filter "players" moet een geheel getal zijn.');
        }
        if (null !== $maxPrice && !is_float($maxPrice) && !is_int($maxPrice)) {
            throw new \InvalidArgumentException('Filter "max_price" moet een getal zijn.');
        }
        if (null !== $coop && !is_bool($coop)) {
            throw new \InvalidArgumentException('Filter "coop" moet true of false zijn.');
        }

        $games = $this->games->search(
            genre: $genre,
            players: $players,
            maxPrice: null === $maxPrice ? null : (float) $maxPrice,
            coop: $coop,
            query: $query,
        );

        return array_map(
            static fn (Game $game): array => [
                'title' => $game->getTitle(),
                'genre' => $game->getGenre(),
                'minPlayers' => $game->getMinPlayers(),
                'maxPlayers' => $game->getMaxPlayers(),
                'price' => $game->getPrice(),
                'platforms' => $game->getPlatforms(),
                'coop' => $game->isCoop(),
                'description' => $game->getDescription(),
            ],
            $games,
        );
    }
}
