<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Ai\ChatModel;
use App\Ai\Role;
use App\Tests\Fake\FakeChatModel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ChatControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private FakeChatModel $model;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->model = new FakeChatModel('Hallo, ik ben de fake.');
        static::getContainer()->set(ChatModel::class, $this->model);
    }

    public function testEmptyChatPage(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.chat__empty', 'Nog geen berichten');
    }

    public function testSendingAMessageShowsQuestionAndAnswer(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Wat is Symfony?']);

        self::assertResponseRedirects('/', 303);
        $this->client->followRedirect();

        self::assertSelectorTextContains('.message--user', 'Wat is Symfony?');
        self::assertSelectorTextContains('.message--assistant', 'Hallo, ik ben de fake.');
        self::assertSelectorTextContains('.message__meta', 'in: 12 tokens');
    }

    public function testFullHistoryIsSentOnEveryCall(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Eerste vraag']);
        $this->client->followRedirect();
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Tweede vraag']);

        self::assertCount(2, $this->model->receivedHistories);

        $secondCall = $this->model->receivedHistories[1];
        self::assertCount(3, $secondCall, 'vraag 1, antwoord 1 en vraag 2 gaan allemaal mee');
        self::assertSame(Role::User, $secondCall[0]->role);
        self::assertSame('Eerste vraag', $secondCall[0]->content);
        self::assertSame(Role::Assistant, $secondCall[1]->role);
        self::assertSame('Tweede vraag', $secondCall[2]->content);
    }

    public function testBlankMessageIsRejected(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => '   ']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.chat__form', 'Typ eerst een vraag.');
        self::assertCount(0, $this->model->receivedHistories);
    }

    public function testResetClearsTheConversation(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Vraag']);
        $this->client->followRedirect();
        $this->client->submitForm('Nieuw gesprek');

        self::assertResponseRedirects('/', 303);
        $this->client->followRedirect();

        self::assertSelectorExists('.chat__empty');
    }
}
