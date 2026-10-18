<?php

declare(strict_types=1);

namespace App\Ai;

/**
 * Eén uitgevoerde tool-aanroep, zodat we in de UI kunnen tonen wat het model opvroeg en wat het terugkreeg.
 */
final readonly class ToolCall
{
    /**
     * @param array<string, mixed> $input
     */
    public function __construct(
        public string $name,
        public array $input,
        public string $result,
    ) {
    }
}
