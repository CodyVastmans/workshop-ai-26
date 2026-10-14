<?php

declare(strict_types=1);

namespace App\Ai;

use Anthropic\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ClaudeChatModel implements ChatModel
{
    private const int MAX_TOKENS = 16000;

    public function __construct(
        private readonly Client $client,
        #[Autowire(env: 'CLAUDE_MODEL')]
        private readonly string $model,
    ) {
    }

    public function reply(string $systemPrompt, string $question): ChatReply
    {
        // TODO laag 1: dit is de enige plek waar de app met Claude praat.
        //
        // 1. Roep $this->client->messages->create(...) aan met:
        //      maxTokens: self::MAX_TOKENS
        //      messages:  [['role' => 'user', 'content' => $question]]
        //      model:     $this->model
        //      system:    $systemPrompt
        // 2. Het antwoord ($message->content) is een lijst blokken. Plak de tekst van alle
        //    Anthropic\Messages\TextBlock-blokken aan elkaar.
        // 3. Geef een ChatReply terug met de tekst, $message->model, $message->stopReason
        //    en de tokens uit $message->usage (inputTokens / outputTokens).
        //
        // Extra: als $message->stopReason 'refusal' is, zit de uitleg in $message->stopDetails->explanation.
        throw new \LogicException(\sprintf('Laag 1: implementeer de call naar %s in %s', $this->model, self::class));
    }
}
