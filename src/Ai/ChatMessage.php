<?php

declare(strict_types=1);

namespace App\Ai;

/**
 * Eén beurt in het gesprek. Het model is stateless: bij elke call sturen we de volledige lijst mee.
 */
final readonly class ChatMessage
{
    public function __construct(
        public Role $role,
        public string $content,
        public ?ChatReply $reply = null,
    ) {
    }

    public static function user(string $content): self
    {
        return new self(Role::User, $content);
    }

    public static function assistant(ChatReply $reply): self
    {
        return new self(Role::Assistant, $reply->text, $reply);
    }

    /**
     * @return array{role: 'user'|'assistant', content: string}
     */
    public function toApi(): array
    {
        return ['role' => $this->role->value, 'content' => $this->content];
    }
}
