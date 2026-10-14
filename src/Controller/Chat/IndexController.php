<?php

declare(strict_types=1);

namespace App\Controller\Chat;

use App\Form\ChatInput;
use App\Form\ChatInputType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'chat', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('chat/index.html.twig', [
            'form' => $this->createForm(ChatInputType::class, new ChatInput()),
        ]);
    }
}
