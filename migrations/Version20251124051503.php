<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124051503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_notification_setting (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, notify_mentioned BOOLEAN NOT NULL, notify_watcher BOOLEAN NOT NULL, notify_assigned BOOLEAN NOT NULL, notify_responsible BOOLEAN NOT NULL, notify_shared BOOLEAN NOT NULL, notify_start_date BOOLEAN NOT NULL, start_date_delay VARCHAR(20) NOT NULL, notify_end_date BOOLEAN NOT NULL, end_date_delay VARCHAR(20) NOT NULL, notify_overdue BOOLEAN NOT NULL, overdue_frequency VARCHAR(20) NOT NULL, notify_new_work_package BOOLEAN NOT NULL, notify_status_changes BOOLEAN NOT NULL, notify_date_changes BOOLEAN NOT NULL, notify_priority_changes BOOLEAN NOT NULL, notify_new_comments BOOLEAN NOT NULL, CONSTRAINT FK_344BE150A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_344BE150A76ED395 ON user_notification_setting (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_notification_setting');
    }
}
