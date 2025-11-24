<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124044926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_session ADD COLUMN is_revoked BOOLEAN DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_session AS SELECT id, user_id, session_id, ip_address, user_agent, last_active_at, created_at FROM user_session');
        $this->addSql('DROP TABLE user_session');
        $this->addSql('CREATE TABLE user_session (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, session_id VARCHAR(255) NOT NULL, ip_address VARCHAR(45) NOT NULL, user_agent CLOB DEFAULT NULL, last_active_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_8849CBDEA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_session (id, user_id, session_id, ip_address, user_agent, last_active_at, created_at) SELECT id, user_id, session_id, ip_address, user_agent, last_active_at, created_at FROM __temp__user_session');
        $this->addSql('DROP TABLE __temp__user_session');
        $this->addSql('CREATE INDEX IDX_8849CBDEA76ED395 ON user_session (user_id)');
    }
}
