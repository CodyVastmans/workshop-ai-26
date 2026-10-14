<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Ai\ChatModel;
use App\Tests\Fake\FakeChatModel;
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

    public function testAskingAQuestionShowsQuestionAndAnswer(): void
    {
        $this->client->request('GET', '/');
        $this->client->submitForm('Verstuur', ['chat_input[message]' => 'Wat is Symfony?']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.message--user', 'Wat is Symfony?');
        self::assertSelectorTextContains('.message--assistant', 'Hallo, ik ben de fake.');
        self::assertSelectorTextContains('.message__meta', 'in: 12 tokens');
        self::assertSame(['Wat is Symfony?'], $this->model->receivedQuestions);
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
