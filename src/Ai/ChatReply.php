<?php

declare(strict_types=1);

namespace App\Ai;

/**
 * Wat het model teruggaf, plus de metadata die we in de UI tonen (tokens = kost en contextgebruik).
 */
final readonly class ChatReply
{
    public function __construct(
        public string $text,
        public string $model,
        public ?string $stopReason,
        public int $inputTokens,
        public int $outputTokens,
    ) {
    }
}
