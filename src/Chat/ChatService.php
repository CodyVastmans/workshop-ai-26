<?php

declare(strict_types=1);

namespace App\Chat;

use App\Ai\ChatModel;
use App\Ai\ChatReply;

final class ChatService
{
    private const string SYSTEM_PROMPT = <<<'PROMPT'
        Je bent de assistent van een workshop over AI-integratie voor hogeschoolstudenten.
        Antwoord kort en helder, in het Nederlands tenzij de gebruiker een andere taal gebruikt.
        PROMPT;

    public function __construct(
        private readonly ChatModel $model,
    ) {
    }

    public function ask(string $question): ChatReply
    {
        // Laag 1: geen geheugen. Elke vraag staat op zichzelf.
        return $this->model->reply(self::SYSTEM_PROMPT, $question);
    }
}
