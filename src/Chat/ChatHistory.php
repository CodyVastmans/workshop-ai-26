<?php

declare(strict_types=1);

namespace App\Chat;

use App\Ai\ChatMessage;
use App\Ai\ChatReply;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class ChatHistory
{
    private const string OWNER_SESSION_KEY = 'chat_owner_token';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ConversationRepository $conversations,
        private readonly EntityManagerInterface $entityManager,
        private readonly ChatService $chat,
    ) {
    }

    /**
     * @return list<Conversation>
     */
    public function conversations(): array
    {
        return $this->conversations->findAllOwned($this->ownerTokenHash());
    }

    public function findConversation(int $id): ?Conversation
    {
        return $this->conversations->findOwned($id, $this->ownerTokenHash());
    }

    public function createConversation(): Conversation
    {
        $conversation = new Conversation($this->ownerTokenHash());
        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $conversation;
    }

    /**
     * @return array{Conversation, ChatReply}
     */
    public function ask(?Conversation $conversation, string $question): array
    {
        $history = array_map(
            static fn (Message $message): ChatMessage => $message->toChatMessage(),
            $conversation?->getMessages() ?? [],
        );
        $reply = $this->chat->ask($question, $history);

        $conversation ??= new Conversation($this->ownerTokenHash());
        $conversation->addMessage(Message::fromChatMessage($conversation, ChatMessage::user($question)));
        $conversation->addMessage(Message::fromChatMessage($conversation, ChatMessage::assistant($reply)));

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return [$conversation, $reply];
    }

    private function ownerTokenHash(): string
    {
        $session = $this->requestStack->getSession();
        $ownerToken = $session->get(self::OWNER_SESSION_KEY);

        if (!is_string($ownerToken) || '' === $ownerToken) {
            $ownerToken = bin2hex(random_bytes(32));
            $session->set(self::OWNER_SESSION_KEY, $ownerToken);
        }

        return hash('sha256', $ownerToken);
    }
}
