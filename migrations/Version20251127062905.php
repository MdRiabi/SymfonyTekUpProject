<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127062905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__projet AS SELECT id, responsable_id, client_id, approuve_par_id, nom, description, date_debut, date_fin, type, categorie, objectifs, fonctionnalites, budget, priorite, statut, notes, date_creation, date_modification, date_approbation, raison_refus, fichier_joint, charter_step, business_case_data, scope_definition_data, resource_planning_data, go_no_go_data FROM projet');
        $this->addSql('DROP TABLE projet');
        $this->addSql('CREATE TABLE projet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, responsable_id INTEGER DEFAULT NULL, client_id INTEGER NOT NULL, approuve_par_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , date_fin DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , type VARCHAR(50) NOT NULL, categorie VARCHAR(50) NOT NULL, objectifs CLOB NOT NULL, fonctionnalites CLOB NOT NULL --(DC2Type:json)
        , budget VARCHAR(50) DEFAULT NULL, priorite VARCHAR(20) NOT NULL, statut VARCHAR(20) NOT NULL, notes CLOB DEFAULT NULL, date_creation DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , date_modification DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , date_approbation DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , raison_refus CLOB DEFAULT NULL, fichier_joint VARCHAR(255) DEFAULT NULL, charter_step INTEGER DEFAULT NULL, business_case_data CLOB DEFAULT NULL --(DC2Type:json)
        , scope_definition_data CLOB DEFAULT NULL --(DC2Type:json)
        , resource_planning_data CLOB DEFAULT NULL --(DC2Type:json)
        , go_no_go_data CLOB DEFAULT NULL --(DC2Type:json)
        , CONSTRAINT FK_50159CA953C59D72 FOREIGN KEY (responsable_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_50159CA919EB6921 FOREIGN KEY (client_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_50159CA95ED9CBB3 FOREIGN KEY (approuve_par_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO projet (id, responsable_id, client_id, approuve_par_id, nom, description, date_debut, date_fin, type, categorie, objectifs, fonctionnalites, budget, priorite, statut, notes, date_creation, date_modification, date_approbation, raison_refus, fichier_joint, charter_step, business_case_data, scope_definition_data, resource_planning_data, go_no_go_data) SELECT id, responsable_id, client_id, approuve_par_id, nom, description, date_debut, date_fin, type, categorie, objectifs, fonctionnalites, budget, priorite, statut, notes, date_creation, date_modification, date_approbation, raison_refus, fichier_joint, charter_step, business_case_data, scope_definition_data, resource_planning_data, go_no_go_data FROM __temp__projet');
        $this->addSql('DROP TABLE __temp__projet');
        $this->addSql('CREATE INDEX IDX_50159CA95ED9CBB3 ON projet (approuve_par_id)');
        $this->addSql('CREATE INDEX IDX_50159CA919EB6921 ON projet (client_id)');
        $this->addSql('CREATE INDEX IDX_50159CA953C59D72 ON projet (responsable_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__projet AS SELECT id, responsable_id, client_id, approuve_par_id, nom, description, date_debut, date_fin, type, categorie, objectifs, fonctionnalites, budget, priorite, statut, notes, date_creation, date_modification, date_approbation, raison_refus, charter_step, business_case_data, scope_definition_data, resource_planning_data, go_no_go_data, fichier_joint FROM projet');
        $this->addSql('DROP TABLE projet');
        $this->addSql('CREATE TABLE projet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, responsable_id INTEGER DEFAULT NULL, client_id INTEGER NOT NULL, approuve_par_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , date_fin DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , type VARCHAR(50) NOT NULL, categorie VARCHAR(50) NOT NULL, objectifs CLOB NOT NULL, fonctionnalites CLOB NOT NULL --(DC2Type:json)
        , budget VARCHAR(50) DEFAULT NULL, priorite VARCHAR(20) NOT NULL, statut VARCHAR(20) NOT NULL, notes CLOB DEFAULT NULL, date_creation DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , date_modification DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , date_approbation DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , raison_refus CLOB DEFAULT NULL, charter_step INTEGER DEFAULT NULL, business_case_data CLOB DEFAULT NULL, scope_definition_data CLOB DEFAULT NULL, resource_planning_data CLOB DEFAULT NULL, go_no_go_data CLOB DEFAULT NULL, fichier_joint VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_50159CA953C59D72 FOREIGN KEY (responsable_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_50159CA919EB6921 FOREIGN KEY (client_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_50159CA95ED9CBB3 FOREIGN KEY (approuve_par_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO projet (id, responsable_id, client_id, approuve_par_id, nom, description, date_debut, date_fin, type, categorie, objectifs, fonctionnalites, budget, priorite, statut, notes, date_creation, date_modification, date_approbation, raison_refus, charter_step, business_case_data, scope_definition_data, resource_planning_data, go_no_go_data, fichier_joint) SELECT id, responsable_id, client_id, approuve_par_id, nom, description, date_debut, date_fin, type, categorie, objectifs, fonctionnalites, budget, priorite, statut, notes, date_creation, date_modification, date_approbation, raison_refus, charter_step, business_case_data, scope_definition_data, resource_planning_data, go_no_go_data, fichier_joint FROM __temp__projet');
        $this->addSql('DROP TABLE __temp__projet');
        $this->addSql('CREATE INDEX IDX_50159CA953C59D72 ON projet (responsable_id)');
        $this->addSql('CREATE INDEX IDX_50159CA919EB6921 ON projet (client_id)');
        $this->addSql('CREATE INDEX IDX_50159CA95ED9CBB3 ON projet (approuve_par_id)');
    }
}
