<?php

declare(strict_types=1);

namespace App\Ai;

use Anthropic\Client;
use Anthropic\Core\Contracts\BaseModel;
use Anthropic\Messages\TextBlock;
use Anthropic\Messages\ToolUseBlock;
use App\Chat\Tool\SearchGamesTool;
use App\Chat\Tool\ToolLoopLimitException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @phpstan-import-type ContentBlockVariants from \Anthropic\Messages\ContentBlock
 */
final class ClaudeChatModel implements ChatModel
{
    private const int MAX_TOKENS = 16000;
    private const int MAX_TOOL_ROUNDS = 5;

    public function __construct(
        private readonly Client $client,
        private readonly SearchGamesTool $searchGames,
        #[Autowire(env: 'CLAUDE_MODEL')]
        private readonly string $model,
    ) {
    }

    /**
     * @param list<ChatMessage> $history Previous turns in chronological order
     */
    public function reply(string $systemPrompt, string $question, array $history = []): ChatReply
    {
        $messages = array_map(
            static fn (ChatMessage $message): array => $message->toApi(),
            $history,
        );
        $messages[] = ['role' => 'user', 'content' => $question];

        $toolCalls = [];
        $inputTokens = 0;
        $outputTokens = 0;
        $cacheReadTokens = 0;
        $cacheCreationTokens = 0;

        for ($round = 0; ; ++$round) {
            $message = $this->client->messages->create(
                maxTokens: self::MAX_TOKENS,
                messages: $messages,
                model: $this->model,
                system: $systemPrompt,
                tools: [$this->searchGames->definition()],
            );

            $inputTokens += $message->usage->inputTokens;
            $outputTokens += $message->usage->outputTokens;
            $cacheReadTokens += $message->usage->cacheReadInputTokens ?? 0;
            $cacheCreationTokens += $message->usage->cacheCreationInputTokens ?? 0;

            if ('tool_use' !== $message->stopReason) {
                return new ChatReply(
                    text: $this->responseText($message->content, $message->stopReason, $message->stopDetails?->explanation),
                    model: $message->model,
                    stopReason: $message->stopReason,
                    inputTokens: $inputTokens,
                    outputTokens: $outputTokens,
                    toolCalls: $toolCalls,
                    cacheReadTokens: $cacheReadTokens,
                    cacheCreationTokens: $cacheCreationTokens,
                );
            }

            if ($round >= self::MAX_TOOL_ROUNDS) {
                throw new ToolLoopLimitException('Claude heeft het maximum van vijf toolrondes bereikt.');
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => array_map(
                    static fn (BaseModel $block): array => $block->toProperties(),
                    $message->content,
                ),
            ];

            $results = [];
            foreach ($message->content as $block) {
                if (!$block instanceof ToolUseBlock) {
                    continue;
                }

                $isError = false;
                if ('search_games' !== $block->name) {
                    $result = json_encode(
                        ['error' => \sprintf('Onbekende tool: %s.', $block->name)],
                        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
                    );
                    $isError = true;
                } else {
                    try {
                        $result = json_encode(
                            $this->searchGames->search($block->input),
                            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
                        );
                    } catch (\InvalidArgumentException $exception) {
                        $result = json_encode(
                            ['error' => $exception->getMessage()],
                            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
                        );
                        $isError = true;
                    }
                }

                $toolCalls[] = new ToolCall($block->name, $block->input, $result);
                $results[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block->id,
                    'content' => $result,
                    'is_error' => $isError,
                ];
            }

            if ([] === $results) {
                throw new \LogicException('Claude gaf stopreden tool_use terug zonder tool-aanroep.');
            }

            $messages[] = ['role' => 'user', 'content' => $results];
        }
    }

    /**
     * @param list<ContentBlockVariants> $content
     */
    private function responseText(array $content, ?string $stopReason, ?string $refusalExplanation): string
    {
        $text = '';
        foreach ($content as $block) {
            if ($block instanceof TextBlock) {
                $text .= $block->text;
            }
        }

        if ('' === $text && 'refusal' === $stopReason) {
            return $refusalExplanation ?? '';
        }

        return $text;
    }
}
