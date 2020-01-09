<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200101034104 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61F296CD8AE ON team (team_id)');
        $this->addSql('DROP INDEX user_id ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A2F98E4772F5A1AA ON channel (channel_id)');
        $this->addSql('CREATE INDEX message_id ON message (ts, channel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_A2F98E4772F5A1AA ON channel');
        $this->addSql('DROP INDEX message_id ON message');
        $this->addSql('DROP INDEX UNIQ_C4E0A61F296CD8AE ON team');
        $this->addSql('CREATE INDEX user_id ON user (user_id)');
    }
}
