<?php

declare(strict_types=1);

namespace App\Chat;

use App\Ai\ChatMessage;

/**
 * Waar de gespreksgeschiedenis leeft. Laag 1: de sessie. Laag 2 vervangt dit door de database.
 */
interface ConversationStore
{
    /**
     * @return list<ChatMessage>
     */
    public function messages(): array;

    public function append(ChatMessage ...$messages): void;

    public function clear(): void;
}
