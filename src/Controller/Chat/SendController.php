<?php

declare(strict_types=1);

namespace App\Controller\Chat;

use Anthropic\Core\Exceptions\APIException;
use App\Chat\ChatHistory;
use App\Chat\Tool\ToolLoopLimitException;
use App\Entity\Conversation;
use App\Form\ChatInput;
use App\Form\ChatInputType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SendController extends AbstractController
{
    #[Route('/chat', name: 'chat_open', methods: ['GET'])]
    public function open(): Response
    {
        return $this->redirectToRoute('chat');
    }

    #[Route('/chat', name: 'chat_send', methods: ['POST'])]
    public function __invoke(Request $request, ChatHistory $history): Response
    {
        $input = new ChatInput();
        $form = $this->createForm(ChatInputType::class, $input);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('chat/index.html.twig', [
                'form' => $form,
                'conversation' => $this->conversationFromInput($input, $history),
                'conversations' => $history->conversations(),
            ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        $conversation = $this->conversationFromInput($input, $history);

        try {
            [$conversation, $reply] = $history->ask($conversation, $input->message);
        } catch (APIException $e) {
            $this->addFlash('error', 'De call naar de API mislukte: '.$e->getMessage());

            return $this->render('chat/index.html.twig', [
                'form' => $this->createForm(ChatInputType::class, $input),
                'conversation' => $conversation,
                'conversations' => $history->conversations(),
                'pendingQuestion' => $input->message,
            ]);
        } catch (ToolLoopLimitException $e) {
            $this->addFlash('error', 'Claude kon het game-advies niet afronden: '.$e->getMessage());

            return $this->render('chat/index.html.twig', [
                'form' => $this->createForm(ChatInputType::class, $input),
                'conversation' => $conversation,
                'conversations' => $history->conversations(),
                'pendingQuestion' => $input->message,
            ]);
        }

        return $this->redirectToRoute('chat', ['conversation' => $conversation->getId()]);
    }

    private function conversationFromInput(ChatInput $input, ChatHistory $history): ?Conversation
    {
        if (null === $input->conversationId) {
            return null;
        }

        $id = filter_var($input->conversationId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $id || null === ($conversation = $history->findConversation($id))) {
            throw $this->createNotFoundException();
        }

        return $conversation;
    }
}
