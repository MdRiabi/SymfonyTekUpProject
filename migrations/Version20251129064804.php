<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129064804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE tache ADD COLUMN date_creation DATETIME NOT NULL DEFAULT '2024-01-01 00:00:00'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__tache AS SELECT id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress FROM tache');
        $this->addSql('DROP TABLE tache');
        $this->addSql('CREATE TABLE tache (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, createur_id INTEGER NOT NULL, assigne_id INTEGER DEFAULT NULL, projet_id INTEGER DEFAULT NULL, phase_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , date_fin DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , statut VARCHAR(50) DEFAULT NULL, priorite VARCHAR(20) DEFAULT NULL, deadline DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , estimated_hours INTEGER DEFAULT NULL, progress INTEGER DEFAULT 0 NOT NULL, CONSTRAINT FK_9387207573A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_938720758E7B8AB0 FOREIGN KEY (assigne_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_93872075C18272 FOREIGN KEY (projet_id) REFERENCES projet (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9387207599091188 FOREIGN KEY (phase_id) REFERENCES phase (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO tache (id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress) SELECT id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress FROM __temp__tache');
        $this->addSql('DROP TABLE __temp__tache');
        $this->addSql('CREATE INDEX IDX_9387207573A201E5 ON tache (createur_id)');
        $this->addSql('CREATE INDEX IDX_938720758E7B8AB0 ON tache (assigne_id)');
        $this->addSql('CREATE INDEX IDX_93872075C18272 ON tache (projet_id)');
        $this->addSql('CREATE INDEX IDX_9387207599091188 ON tache (phase_id)');
    }
}
