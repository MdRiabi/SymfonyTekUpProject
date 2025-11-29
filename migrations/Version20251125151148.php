<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125151148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur_id INTEGER NOT NULL, titre VARCHAR(255) NOT NULL, message CLOB NOT NULL, type VARCHAR(50) NOT NULL, related_id INTEGER DEFAULT NULL, lu BOOLEAN NOT NULL, date_creation DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BF5476CAFB88E14F ON notification (utilisateur_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__projet AS SELECT id, responsable_id, nom, description, date_debut, date_fin FROM projet');
        $this->addSql('DROP TABLE projet');
        $this->addSql('CREATE TABLE projet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, responsable_id INTEGER DEFAULT NULL, client_id INTEGER NOT NULL, approuve_par_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , date_fin DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , type VARCHAR(50) NOT NULL, categorie VARCHAR(50) NOT NULL, objectifs CLOB NOT NULL, fonctionnalites CLOB NOT NULL --(DC2Type:json)
        , budget VARCHAR(50) DEFAULT NULL, priorite VARCHAR(20) NOT NULL, statut VARCHAR(20) NOT NULL, notes CLOB DEFAULT NULL, date_creation DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , date_modification DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , date_approbation DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , raison_refus CLOB DEFAULT NULL, CONSTRAINT FK_50159CA953C59D72 FOREIGN KEY (responsable_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_50159CA919EB6921 FOREIGN KEY (client_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_50159CA95ED9CBB3 FOREIGN KEY (approuve_par_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO projet (id, responsable_id, nom, description, date_debut, date_fin) SELECT id, responsable_id, nom, description, date_debut, date_fin FROM __temp__projet');
        $this->addSql('DROP TABLE __temp__projet');
        $this->addSql('CREATE INDEX IDX_50159CA953C59D72 ON projet (responsable_id)');
        $this->addSql('CREATE INDEX IDX_50159CA919EB6921 ON projet (client_id)');
        $this->addSql('CREATE INDEX IDX_50159CA95ED9CBB3 ON projet (approuve_par_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE notification');
        $this->addSql('CREATE TEMPORARY TABLE __temp__projet AS SELECT id, responsable_id, nom, description, date_debut, date_fin FROM projet');
        $this->addSql('DROP TABLE projet');
        $this->addSql('CREATE TABLE projet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, responsable_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , date_fin DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_50159CA953C59D72 FOREIGN KEY (responsable_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO projet (id, responsable_id, nom, description, date_debut, date_fin) SELECT id, responsable_id, nom, description, date_debut, date_fin FROM __temp__projet');
        $this->addSql('DROP TABLE __temp__projet');
        $this->addSql('CREATE INDEX IDX_50159CA953C59D72 ON projet (responsable_id)');
    }
}
