<?php

declare(strict_types=1);

namespace App\Tests\Fake;

use App\Ai\ChatModel;
use App\Ai\ChatReply;

/**
 * Vervangt Claude in tests: geen netwerk, geen key, en we kunnen zien wat er naar het model ging.
 */
final class FakeChatModel implements ChatModel
{
    /** @var list<string> */
    public array $receivedQuestions = [];

    public function __construct(
        private readonly string $answer = 'Dit is een testantwoord.',
    ) {
    }

    public function reply(string $systemPrompt, string $question): ChatReply
    {
        $this->receivedQuestions[] = $question;

        return new ChatReply(
            text: $this->answer,
            model: 'fake-model',
            stopReason: 'end_turn',
            inputTokens: 12,
            outputTokens: 7,
        );
    }
}
