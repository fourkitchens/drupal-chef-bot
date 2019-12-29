<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191229203910 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, team_id VARCHAR(16) NOT NULL, name VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, user_id VARCHAR(16) NOT NULL, name VARCHAR(255) NOT NULL, real_name VARCHAR(255) NOT NULL, is_deleted TINYINT(1) NOT NULL, is_admin TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649A76ED395 (user_id), INDEX IDX_8D93D649296CD8AE (team_id), INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_channel (user_id INT NOT NULL, channel_id INT NOT NULL, INDEX IDX_FAF4904DA76ED395 (user_id), INDEX IDX_FAF4904D72F5A1AA (channel_id), PRIMARY KEY(user_id, channel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE channel (id INT AUTO_INCREMENT NOT NULL, channel_id VARCHAR(16) NOT NULL, name VARCHAR(255) NOT NULL, topic VARCHAR(1024) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, user_id INT NOT NULL, parent_id INT DEFAULT NULL, text VARCHAR(4096) NOT NULL, ts VARCHAR(32) NOT NULL, INDEX IDX_B6BD307F72F5A1AA (channel_id), INDEX IDX_B6BD307FA76ED395 (user_id), INDEX IDX_B6BD307F727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE user_channel ADD CONSTRAINT FK_FAF4904DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_channel ADD CONSTRAINT FK_FAF4904D72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F727ACA70 FOREIGN KEY (parent_id) REFERENCES message (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649296CD8AE');
        $this->addSql('ALTER TABLE user_channel DROP FOREIGN KEY FK_FAF4904DA76ED395');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA76ED395');
        $this->addSql('ALTER TABLE user_channel DROP FOREIGN KEY FK_FAF4904D72F5A1AA');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F72F5A1AA');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F727ACA70');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_channel');
        $this->addSql('DROP TABLE channel');
        $this->addSql('DROP TABLE message');
    }
}
