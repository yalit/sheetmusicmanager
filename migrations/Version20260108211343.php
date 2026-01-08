<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108211343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE credited_person ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE credited_person ADD COLUMN updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE credited_person ADD COLUMN created_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE credited_person ADD COLUMN updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE sheet ADD COLUMN updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE sheet ADD COLUMN created_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sheet ADD COLUMN updated_by VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__credited_person AS SELECT id, type, organization_id, sheet_id, person_id FROM credited_person');
        $this->addSql('DROP TABLE credited_person');
        $this->addSql('CREATE TABLE credited_person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(100) NOT NULL, organization_id INTEGER NOT NULL, sheet_id INTEGER NOT NULL, person_id INTEGER NOT NULL, CONSTRAINT FK_71F46BD132C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD18B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD1217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO credited_person (id, type, organization_id, sheet_id, person_id) SELECT id, type, organization_id, sheet_id, person_id FROM __temp__credited_person');
        $this->addSql('DROP TABLE __temp__credited_person');
        $this->addSql('CREATE INDEX IDX_71F46BD132C8A3DE ON credited_person (organization_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD18B1206A5 ON credited_person (sheet_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD1217BBB47 ON credited_person (person_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sheet AS SELECT id, title, genre, difficulty, duration, key_signature, notes, refs, file, organization_id FROM sheet');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('CREATE TABLE sheet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, genre VARCHAR(100) DEFAULT NULL, difficulty VARCHAR(20) DEFAULT NULL, duration VARCHAR(50) DEFAULT NULL, key_signature VARCHAR(50) DEFAULT NULL, notes CLOB DEFAULT NULL, refs CLOB NOT NULL, file VARCHAR(255) DEFAULT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_873C91E232C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sheet (id, title, genre, difficulty, duration, key_signature, notes, refs, file, organization_id) SELECT id, title, genre, difficulty, duration, key_signature, notes, refs, file, organization_id FROM __temp__sheet');
        $this->addSql('DROP TABLE __temp__sheet');
        $this->addSql('CREATE INDEX IDX_873C91E232C8A3DE ON sheet (organization_id)');
    }
}
