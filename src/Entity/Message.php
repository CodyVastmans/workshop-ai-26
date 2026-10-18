<?php

declare(strict_types=1);

namespace App\Entity;

use App\Ai\ChatMessage;
use App\Ai\ChatReply;
use App\Ai\Role;
use App\Ai\ToolCall;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Conversation $conversation;

    #[ORM\Column(enumType: Role::class)]
    private Role $role;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(nullable: true)]
    private ?string $model;

    #[ORM\Column(nullable: true)]
    private ?int $inputTokens;

    #[ORM\Column(nullable: true)]
    private ?int $outputTokens;

    #[ORM\Column(nullable: true)]
    private ?string $stopReason;

    #[ORM\Column(nullable: true)]
    private ?int $cacheReadTokens;

    #[ORM\Column(nullable: true)]
    private ?int $cacheCreationTokens;

    /** @var list<array{name: string, input: array<string, mixed>, result: string}>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $toolCalls;

    /**
     * Gecompacteerde berichten zitten in de samenvatting van het gesprek en gaan niet meer naar het model.
     */
    #[ORM\Column(options: ['default' => false])]
    private bool $compacted = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    private function __construct(
        Conversation $conversation,
        Role $role,
        string $content,
        ?ChatReply $reply,
    ) {
        $this->conversation = $conversation;
        $this->role = $role;
        $this->content = $content;
        $this->model = $reply?->model;
        $this->inputTokens = $reply?->inputTokens;
        $this->outputTokens = $reply?->outputTokens;
        $this->stopReason = $reply?->stopReason;
        $this->cacheReadTokens = $reply?->cacheReadTokens;
        $this->cacheCreationTokens = $reply?->cacheCreationTokens;
        $this->toolCalls = null === $reply ? null : array_map(
            static fn (ToolCall $call): array => [
                'name' => $call->name,
                'input' => $call->input,
                'result' => $call->result,
            ],
            $reply->toolCalls,
        );
        $this->createdAt = new \DateTimeImmutable();

        $conversation->addMessage($this);
    }

    public static function fromChatMessage(Conversation $conversation, ChatMessage $message): self
    {
        return new self($conversation, $message->role, $message->content, $message->reply);
    }

    public function toChatMessage(): ChatMessage
    {
        if (Role::User === $this->role) {
            return ChatMessage::user($this->content);
        }

        return ChatMessage::assistant(new ChatReply(
            text: $this->content,
            model: $this->model ?? '',
            stopReason: $this->stopReason,
            inputTokens: $this->inputTokens ?? 0,
            outputTokens: $this->outputTokens ?? 0,
            toolCalls: array_map(
                static fn (array $call): ToolCall => new ToolCall($call['name'], $call['input'], $call['result']),
                $this->toolCalls ?? [],
            ),
            cacheReadTokens: $this->cacheReadTokens ?? 0,
            cacheCreationTokens: $this->cacheCreationTokens ?? 0,
        ));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isCompacted(): bool
    {
        return $this->compacted;
    }

    public function markCompacted(): void
    {
        $this->compacted = true;
    }
}
