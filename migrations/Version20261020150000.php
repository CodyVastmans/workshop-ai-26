<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Koppelt gesprekken aan de browsersessie die ze heeft aangemaakt';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE conversation ADD owner_token_hash VARCHAR(64) DEFAULT '' NOT NULL");
        $this->addSql('CREATE INDEX IDX_CONVERSATION_OWNER ON conversation (owner_token_hash)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_CONVERSATION_OWNER');
        $this->addSql('ALTER TABLE conversation DROP owner_token_hash');
    }
}
