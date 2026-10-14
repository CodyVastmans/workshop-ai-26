<?php

declare(strict_types=1);

namespace App\Chat;

use App\Ai\ChatMessage;
use Symfony\Component\HttpFoundation\RequestStack;

final class SessionConversationStore implements ConversationStore
{
    private const string KEY = 'chat.history';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function messages(): array
    {
        /** @var list<ChatMessage> $messages */
        $messages = $this->requestStack->getSession()->get(self::KEY, []);

        return $messages;
    }

    public function append(ChatMessage ...$messages): void
    {
        $this->requestStack->getSession()->set(self::KEY, [...$this->messages(), ...$messages]);
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove(self::KEY);
    }
}
