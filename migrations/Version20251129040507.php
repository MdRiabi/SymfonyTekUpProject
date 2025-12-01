<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129040507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_attachment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, tache_id INTEGER NOT NULL, uploaded_by_id INTEGER NOT NULL, filename VARCHAR(255) NOT NULL, filepath VARCHAR(500) NOT NULL, filesize INTEGER NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, uploaded_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_654C9214D2235D39 FOREIGN KEY (tache_id) REFERENCES tache (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_654C9214A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_654C9214D2235D39 ON task_attachment (tache_id)');
        $this->addSql('CREATE INDEX IDX_654C9214A2B28FE8 ON task_attachment (uploaded_by_id)');
        $this->addSql('CREATE TABLE task_comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, tache_id INTEGER NOT NULL, auteur_id INTEGER NOT NULL, contenu CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_8B957886D2235D39 FOREIGN KEY (tache_id) REFERENCES tache (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8B95788660BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8B957886D2235D39 ON task_comment (tache_id)');
        $this->addSql('CREATE INDEX IDX_8B95788660BB6FE6 ON task_comment (auteur_id)');
        $this->addSql('CREATE TABLE time_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, tache_id INTEGER NOT NULL, utilisateur_id INTEGER NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, duree INTEGER NOT NULL, description CLOB DEFAULT NULL, type VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_6E537C0CD2235D39 FOREIGN KEY (tache_id) REFERENCES tache (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6E537C0CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6E537C0CD2235D39 ON time_entry (tache_id)');
        $this->addSql('CREATE INDEX IDX_6E537C0CFB88E14F ON time_entry (utilisateur_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__tache AS SELECT id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress FROM tache');
        $this->addSql('DROP TABLE tache');
        $this->addSql('CREATE TABLE tache (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, createur_id INTEGER NOT NULL, assigne_id INTEGER DEFAULT NULL, projet_id INTEGER DEFAULT NULL, phase_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , date_fin DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , statut VARCHAR(50) DEFAULT NULL, priorite VARCHAR(20) DEFAULT NULL, deadline DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , estimated_hours INTEGER DEFAULT NULL, progress INTEGER DEFAULT 0 NOT NULL, CONSTRAINT FK_9387207573A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_938720758E7B8AB0 FOREIGN KEY (assigne_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_93872075C18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9387207599091188 FOREIGN KEY (phase_id) REFERENCES phase (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO tache (id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress) SELECT id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress FROM __temp__tache');
        $this->addSql('DROP TABLE __temp__tache');
        $this->addSql('CREATE INDEX IDX_9387207599091188 ON tache (phase_id)');
        $this->addSql('CREATE INDEX IDX_9387207573A201E5 ON tache (createur_id)');
        $this->addSql('CREATE INDEX IDX_938720758E7B8AB0 ON tache (assigne_id)');
        $this->addSql('CREATE INDEX IDX_93872075C18272 ON tache (projet_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE task_attachment');
        $this->addSql('DROP TABLE task_comment');
        $this->addSql('DROP TABLE time_entry');
        $this->addSql('CREATE TEMPORARY TABLE __temp__tache AS SELECT id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress FROM tache');
        $this->addSql('DROP TABLE tache');
        $this->addSql('CREATE TABLE tache (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, createur_id INTEGER NOT NULL, assigne_id INTEGER DEFAULT NULL, projet_id INTEGER DEFAULT NULL, phase_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , date_fin DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , statut VARCHAR(50) DEFAULT NULL, priorite VARCHAR(20) DEFAULT NULL, deadline DATETIME DEFAULT NULL, estimated_hours INTEGER DEFAULT NULL, progress INTEGER DEFAULT 0 NOT NULL, CONSTRAINT FK_9387207573A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_938720758E7B8AB0 FOREIGN KEY (assigne_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_93872075C18272 FOREIGN KEY (projet_id) REFERENCES projet (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9387207599091188 FOREIGN KEY (phase_id) REFERENCES phase (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO tache (id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress) SELECT id, createur_id, assigne_id, projet_id, phase_id, nom, description, date_debut, date_fin, statut, priorite, deadline, estimated_hours, progress FROM __temp__tache');
        $this->addSql('DROP TABLE __temp__tache');
        $this->addSql('CREATE INDEX IDX_9387207573A201E5 ON tache (createur_id)');
        $this->addSql('CREATE INDEX IDX_938720758E7B8AB0 ON tache (assigne_id)');
        $this->addSql('CREATE INDEX IDX_93872075C18272 ON tache (projet_id)');
        $this->addSql('CREATE INDEX IDX_9387207599091188 ON tache (phase_id)');
    }
}
