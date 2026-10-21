<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Validator\Constraints as Assert;

final class ChatInput
{
    #[Assert\NotBlank(message: 'Typ eerst een vraag.')]
    #[Assert\Length(max: 4000)]
    public string $message = '';

    #[Assert\Regex(pattern: '/^[1-9]\d*$/', message: 'Ongeldig gesprek.')]
    public ?string $conversationId = null;
}
