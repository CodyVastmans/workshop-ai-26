<?php

declare(strict_types=1);

namespace App\Ai;

/**
 * De enige plek waar de app met een LLM praat. In tests vervangen we dit door een fake.
 */
interface ChatModel
{
    public function reply(string $systemPrompt, string $question): ChatReply;
}
