<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108180144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE credited_person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(100) NOT NULL, organization_id INTEGER NOT NULL, sheet_id INTEGER NOT NULL, person_id INTEGER NOT NULL, CONSTRAINT FK_71F46BD132C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD18B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD1217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_71F46BD132C8A3DE ON credited_person (organization_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD18B1206A5 ON credited_person (sheet_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD1217BBB47 ON credited_person (person_id)');
        $this->addSql('CREATE TABLE sheet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, genre VARCHAR(100) DEFAULT NULL, difficulty VARCHAR(20) DEFAULT NULL, duration VARCHAR(50) DEFAULT NULL, key_signature VARCHAR(50) DEFAULT NULL, notes CLOB DEFAULT NULL, refs CLOB NOT NULL, file VARCHAR(255) DEFAULT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_873C91E232C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_873C91E232C8A3DE ON sheet (organization_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person AS SELECT id, name, created_at, updated_at, organization_id, created_by, updated_by FROM person');
        $this->addSql('DROP TABLE person');
        $this->addSql('CREATE TABLE person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, organization_id INTEGER NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_34DCD17632C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO person (id, name, created_at, updated_at, organization_id, created_by, updated_by) SELECT id, name, created_at, updated_at, organization_id, created_by, updated_by FROM __temp__person');
        $this->addSql('DROP TABLE __temp__person');
        $this->addSql('CREATE INDEX IDX_34DCD17632C8A3DE ON person (organization_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE credited_person');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('ALTER TABLE person ADD COLUMN type VARCHAR(20) NOT NULL');
    }
}
