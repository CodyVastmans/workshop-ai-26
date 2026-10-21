<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends AbstractType<ChatInput>
 */
final class ChatInputType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('conversationId', HiddenType::class, [
            'required' => false,
            'empty_data' => null,
        ]);
        $builder->add('message', TextareaType::class, [
            'label' => false,
            'empty_data' => '',
            'attr' => ['rows' => 3, 'placeholder' => 'Stel een vraag…', 'autofocus' => true],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChatInput::class,
            'action' => $this->urlGenerator->generate('chat_send'),
        ]);
    }
}
