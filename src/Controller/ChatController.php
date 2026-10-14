<?php

declare(strict_types=1);

namespace App\Controller;

use Anthropic\Core\Exceptions\APIException;
use App\Chat\ChatService;
use App\Chat\ConversationStore;
use App\Form\ChatInput;
use App\Form\ChatInputType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class ChatController extends AbstractController
{
    #[Route('/', name: 'chat', methods: ['GET'])]
    public function index(ConversationStore $store): Response
    {
        return $this->renderChat($store, $this->chatForm());
    }

    #[Route('/chat', name: 'chat_send', methods: ['POST'])]
    public function send(Request $request, ChatService $chat, ConversationStore $store): Response
    {
        $input = new ChatInput();
        $form = $this->chatForm($input);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->renderChat($store, $form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $chat->send($input->message);
        } catch (APIException $e) {
            $this->addFlash('error', 'De call naar de API mislukte: '.$e->getMessage());
        }

        return $this->redirectToRoute('chat', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/chat/reset', name: 'chat_reset', methods: ['POST'])]
    #[IsCsrfTokenValid('chat_reset')]
    public function reset(ConversationStore $store): Response
    {
        $store->clear();

        return $this->redirectToRoute('chat', status: Response::HTTP_SEE_OTHER);
    }

    /**
     * @return FormInterface<ChatInput>
     */
    private function chatForm(?ChatInput $input = null): FormInterface
    {
        return $this->createForm(ChatInputType::class, $input ?? new ChatInput(), [
            'action' => $this->generateUrl('chat_send'),
        ]);
    }

    /**
     * @param FormInterface<ChatInput> $form
     */
    private function renderChat(ConversationStore $store, FormInterface $form, int $status = 200): Response
    {
        return $this->render('chat/index.html.twig', [
            'messages' => $store->messages(),
            'form' => $form,
        ], new Response(status: $status));
    }
}
