<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Het volledige schema voor alle lagen: gesprekken en berichten (laag 2), games (laag 3),
 * samenvatting en cache-tokens (laag 4). Zo hoef je per laag niets aan de database te doen.
 */
final class Version20261018125323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schema voor alle lagen: conversation, message, game';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE conversation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, summary CLOB DEFAULT NULL)');
        $this->addSql('CREATE TABLE game (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, genre VARCHAR(255) NOT NULL, min_players INTEGER NOT NULL, max_players INTEGER NOT NULL, price DOUBLE PRECISION NOT NULL, platforms VARCHAR(255) NOT NULL, coop BOOLEAN NOT NULL, description CLOB NOT NULL)');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role VARCHAR(255) NOT NULL, content CLOB NOT NULL, model VARCHAR(255) DEFAULT NULL, input_tokens INTEGER DEFAULT NULL, output_tokens INTEGER DEFAULT NULL, stop_reason VARCHAR(255) DEFAULT NULL, cache_read_tokens INTEGER DEFAULT NULL, cache_creation_tokens INTEGER DEFAULT NULL, tool_calls CLOB DEFAULT NULL, compacted BOOLEAN DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, conversation_id INTEGER NOT NULL, CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B6BD307F9AC0396 ON message (conversation_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
