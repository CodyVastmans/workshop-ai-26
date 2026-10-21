<?php

declare(strict_types=1);

namespace App\Controller\Chat;

use App\Chat\ChatHistory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewConversationController extends AbstractController
{
    #[Route('/chat/new', name: 'chat_new', methods: ['POST'])]
    public function __invoke(Request $request, ChatHistory $history): Response
    {
        if (!$this->isCsrfTokenValid('chat_new', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Ongeldig formulier-token.');
        }

        $conversation = $history->createConversation();

        return $this->redirectToRoute('chat', ['conversation' => $conversation->getId()]);
    }
}
