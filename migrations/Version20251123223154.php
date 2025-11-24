<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123223154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD COLUMN theme VARCHAR(10) DEFAULT \'system\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__utilisateur AS SELECT id, role_id, manager_id, nom, prenom, email, adresse, titre_poste, departement, equipe, message, password, matricule, est_actif, capacite_hebdo_h, competences, photo_profil, date_creation, updated_at, language FROM utilisateur');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('CREATE TABLE utilisateur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role_id INTEGER NOT NULL, manager_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, titre_poste VARCHAR(255) DEFAULT NULL, departement VARCHAR(255) DEFAULT NULL, equipe VARCHAR(255) DEFAULT NULL, message CLOB DEFAULT NULL, password VARCHAR(255) NOT NULL, matricule VARCHAR(255) DEFAULT NULL, est_actif BOOLEAN NOT NULL, capacite_hebdo_h NUMERIC(5, 2) DEFAULT NULL, competences CLOB DEFAULT NULL --(DC2Type:json)
        , photo_profil VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , language VARCHAR(2) DEFAULT \'fr\' NOT NULL, CONSTRAINT FK_1D1C63B3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1D1C63B3783E3463 FOREIGN KEY (manager_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO utilisateur (id, role_id, manager_id, nom, prenom, email, adresse, titre_poste, departement, equipe, message, password, matricule, est_actif, capacite_hebdo_h, competences, photo_profil, date_creation, updated_at, language) SELECT id, role_id, manager_id, nom, prenom, email, adresse, titre_poste, departement, equipe, message, password, matricule, est_actif, capacite_hebdo_h, competences, photo_profil, date_creation, updated_at, language FROM __temp__utilisateur');
        $this->addSql('DROP TABLE __temp__utilisateur');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3D60322AC ON utilisateur (role_id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3783E3463 ON utilisateur (manager_id)');
    }
}
