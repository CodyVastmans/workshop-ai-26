<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Ai\ChatModel;
use App\Ai\Role;
use App\Ai\ToolCall;
use App\Chat\Tool\SearchGamesTool;
use App\Entity\Game;
use App\Tests\Fake\FakeChatModel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ChatTest extends WebTestCase
{
    private KernelBrowser $client;
    private FakeChatModel $model;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->model = new FakeChatModel('Hallo, ik ben de fake.');
        static::getContainer()->set(ChatModel::class, $this->model);
    }

    public function testEmptyChatPage(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.chat__empty');
        self::assertSelectorNotExists('.message');
    }

    public function testOpeningChatSendUrlRedirectsToChatPage(): void
    {
        $this->client->request('GET', '/chat');

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.chat__form');
    }

    public function testAskingAQuestionShowsQuestionAndAnswer(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Wat is Symfony?']);
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.message--user', 'Wat is Symfony?');
        self::assertSelectorTextContains('.message--assistant', 'Hallo, ik ben de fake.');
        self::assertSelectorTextContains('.message__meta', 'in: 12 tokens');
        self::assertSame(['Wat is Symfony?'], $this->model->receivedQuestions);
        self::assertSame([], $this->model->receivedHistories[0]);
    }

    public function testToolCallsAreSavedAndShownWithTheAssistantReply(): void
    {
        $this->model->toolCalls = [
            new ToolCall('search_games', ['genre' => 'horror'], '[{"title":"Phasmophobia"}]'),
        ];

        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Zoek een horror game']);
        $this->client->followRedirect();

        self::assertSelectorTextContains('.toolcall summary', 'Gamecatalogus doorzocht');
        self::assertSelectorTextContains('.toolcall', 'Phasmophobia');
    }

    public function testGameSearchToolFiltersTheCatalog(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist(new Game('Phasmophobia', 'horror', 1, 4, 19.99, 'PC, PS, VR', true, 'Spookjagen met vrienden.'));
        $entityManager->persist(new Game('Hades', 'roguelike', 1, 1, 24.99, 'PC, Switch', false, 'Solo roguelike.'));
        $entityManager->flush();

        $games = static::getContainer()->get(SearchGamesTool::class)->search([
            'genre' => 'horror',
            'players' => 4,
            'max_price' => 20,
            'coop' => true,
        ]);

        self::assertCount(1, $games);
        self::assertSame('Phasmophobia', $games[0]['title']);
    }

    public function testGameSearchToolRejectsInvalidFilters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        static::getContainer()->get(SearchGamesTool::class)->search(['players' => 'four']);
    }

    public function testFollowUpQuestionReceivesEarlierTurnsAsContext(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Wat is Symfony?']);
        static::getContainer()->get(EntityManagerInterface::class)->clear();
        $this->client->followRedirect();

        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'En hoe werkt routing?']);
        $this->client->followRedirect();

        self::assertSelectorCount(4, '.message');
        self::assertCount(2, $this->model->receivedHistories[1]);
        self::assertSame(Role::User, $this->model->receivedHistories[1][0]->role);
        self::assertSame('Wat is Symfony?', $this->model->receivedHistories[1][0]->content);
        self::assertSame(Role::Assistant, $this->model->receivedHistories[1][1]->role);
        self::assertSame('Hallo, ik ben de fake.', $this->model->receivedHistories[1][1]->content);
    }

    public function testNewConversationStartsWithoutPreviousContext(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Eerste gesprek']);
        $this->client->followRedirect();

        $this->client->submitForm('Nieuw gesprek');
        $this->client->followRedirect();

        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Ander onderwerp']);
        $this->client->followRedirect();

        self::assertSame([], $this->model->receivedHistories[1]);
        self::assertSelectorCount(2, '.message');
    }

    public function testConversationCannotBeOpenedFromAnotherBrowserSession(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Privégesprek']);

        $location = $this->client->getResponse()->headers->get('Location');
        if (!is_string($location)) {
            self::fail('De chat hoort na het versturen door te verwijzen.');
        }

        $query = parse_url($location, PHP_URL_QUERY);
        if (!is_string($query)) {
            self::fail('De redirect hoort het gesprek-id te bevatten.');
        }

        parse_str($query, $parameters);
        $conversationId = $parameters['conversation'] ?? null;
        if (!is_string($conversationId)) {
            self::fail('De redirect hoort een geldig gesprek-id te bevatten.');
        }

        $this->client->getCookieJar()->clear();
        $this->client->request('GET', '/?conversation='.$conversationId);

        self::assertResponseStatusCodeSame(404);
    }

    public function testBlankQuestionIsRejected(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => '   ']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.chat__form', 'Typ eerst een vraag.');
        self::assertSame([], $this->model->receivedQuestions);
    }
}
