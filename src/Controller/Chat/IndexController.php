<?php

declare(strict_types=1);

namespace App\Controller\Chat;

use App\Chat\ChatHistory;
use App\Form\ChatInput;
use App\Form\ChatInputType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'chat', methods: ['GET'])]
    public function __invoke(Request $request, ChatHistory $history): Response
    {
        $conversation = null;
        $conversationId = $request->query->get('conversation');
        if (null !== $conversationId) {
            $id = filter_var($conversationId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (false === $id || null === ($conversation = $history->findConversation($id))) {
                throw $this->createNotFoundException();
            }
        }

        $input = new ChatInput();
        $input->conversationId = null === $conversation?->getId() ? null : (string) $conversation->getId();

        return $this->render('chat/index.html.twig', [
            'form' => $this->createForm(ChatInputType::class, $input),
            'conversation' => $conversation,
            'conversations' => $history->conversations(),
        ]);
    }
}
