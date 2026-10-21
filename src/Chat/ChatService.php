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
        Gebruik eerdere berichten in dit gesprek als context wanneer de gebruiker daarop voortbouwt.
        Gebruik search_games voor vragen over games in de catalogus en baseer game-aanbevelingen op de resultaten.
        PROMPT;

    public function __construct(
        private readonly ChatModel $model,
    ) {
    }

    /**
     * @param list<ChatMessage> $history Previous turns in chronological order
     */
    public function ask(string $question, array $history = []): ChatReply
    {
        return $this->model->reply(self::SYSTEM_PROMPT, $question, $history);
    }
}
