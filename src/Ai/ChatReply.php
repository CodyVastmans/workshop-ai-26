<?php

declare(strict_types=1);

namespace App\Ai;

/**
 * Wat het model teruggaf, plus de metadata die we in de UI tonen (tokens = kost en contextgebruik).
 */
final readonly class ChatReply
{
    /**
     * @param list<ToolCall> $toolCalls
     */
    public function __construct(
        public string $text,
        public string $model,
        public ?string $stopReason,
        public int $inputTokens,
        public int $outputTokens,
        public array $toolCalls = [],
        public int $cacheReadTokens = 0,
        public int $cacheCreationTokens = 0,
    ) {
    }

    /**
     * De volledige prompt die het model zag. Bij caching telt de API enkel het ongecachte stuk als inputTokens,
     * dus voor "hoe groot is mijn context" moeten we de cache-tokens erbij optellen.
     */
    public function promptTokens(): int
    {
        return $this->inputTokens + $this->cacheReadTokens + $this->cacheCreationTokens;
    }
}
