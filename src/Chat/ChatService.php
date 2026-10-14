<?php

declare(strict_types=1);

namespace App\Chat;

use App\Ai\ChatMessage;
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
        private readonly ConversationStore $store,
    ) {
    }

    public function send(string $userText): ChatReply
    {
        $question = ChatMessage::user($userText);
        $reply = $this->model->reply(self::SYSTEM_PROMPT, [...$this->store->messages(), $question]);

        // Pas opslaan als de call gelukt is, anders blijft een vraag zonder antwoord achter.
        $this->store->append($question, ChatMessage::assistant($reply));

        return $reply;
    }
}
