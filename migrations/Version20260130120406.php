<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130120406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE member_organization');
        $this->addSql('DROP TABLE organization');
        $this->addSql('CREATE TEMPORARY TABLE __temp__credited_person AS SELECT id, type, sheet_id, person_id, created_at, updated_at, created_by, updated_by FROM credited_person');
        $this->addSql('DROP TABLE credited_person');
        $this->addSql('CREATE TABLE credited_person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(100) NOT NULL, sheet_id INTEGER NOT NULL, person_id INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_71F46BD18B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD1217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO credited_person (id, type, sheet_id, person_id, created_at, updated_at, created_by, updated_by) SELECT id, type, sheet_id, person_id, created_at, updated_at, created_by, updated_by FROM __temp__credited_person');
        $this->addSql('DROP TABLE __temp__credited_person');
        $this->addSql('CREATE INDEX IDX_71F46BD1217BBB47 ON credited_person (person_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD18B1206A5 ON credited_person (sheet_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person AS SELECT id, name, created_at, updated_at, created_by, updated_by FROM person');
        $this->addSql('DROP TABLE person');
        $this->addSql('CREATE TABLE person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO person (id, name, created_at, updated_at, created_by, updated_by) SELECT id, name, created_at, updated_at, created_by, updated_by FROM __temp__person');
        $this->addSql('DROP TABLE __temp__person');
        $this->addSql('CREATE TEMPORARY TABLE __temp__set_list_item AS SELECT id, position, name, notes, created_at, updated_at, created_by, updated_by, setlist_id, sheet_id FROM set_list_item');
        $this->addSql('DROP TABLE set_list_item');
        $this->addSql('CREATE TABLE set_list_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, position INTEGER NOT NULL, name VARCHAR(100) NOT NULL, notes CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, setlist_id INTEGER NOT NULL, sheet_id INTEGER NOT NULL, CONSTRAINT FK_2835F3D60D8C499 FOREIGN KEY (setlist_id) REFERENCES setlist (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2835F3D8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO set_list_item (id, position, name, notes, created_at, updated_at, created_by, updated_by, setlist_id, sheet_id) SELECT id, position, name, notes, created_at, updated_at, created_by, updated_by, setlist_id, sheet_id FROM __temp__set_list_item');
        $this->addSql('DROP TABLE __temp__set_list_item');
        $this->addSql('CREATE UNIQUE INDEX unique_position_per_setlist ON set_list_item (setlist_id, position)');
        $this->addSql('CREATE INDEX IDX_2835F3D8B1206A5 ON set_list_item (sheet_id)');
        $this->addSql('CREATE INDEX IDX_2835F3D60D8C499 ON set_list_item (setlist_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setlist AS SELECT id, title, date, notes, created_at, updated_at, created_by, updated_by FROM setlist');
        $this->addSql('DROP TABLE setlist');
        $this->addSql('CREATE TABLE setlist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATE DEFAULT NULL, notes CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO setlist (id, title, date, notes, created_at, updated_at, created_by, updated_by) SELECT id, title, date, notes, created_at, updated_at, created_by, updated_by FROM __temp__setlist');
        $this->addSql('DROP TABLE __temp__setlist');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sheet AS SELECT id, title, genre, difficulty, duration, key_signature, notes, refs, file, created_at, updated_at, created_by, updated_by, full_path FROM sheet');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('CREATE TABLE sheet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, genre VARCHAR(100) DEFAULT NULL, difficulty VARCHAR(20) DEFAULT NULL, duration VARCHAR(50) DEFAULT NULL, key_signature VARCHAR(50) DEFAULT NULL, notes CLOB DEFAULT NULL, refs CLOB NOT NULL, file VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, full_path VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO sheet (id, title, genre, difficulty, duration, key_signature, notes, refs, file, created_at, updated_at, created_by, updated_by, full_path) SELECT id, title, genre, difficulty, duration, key_signature, notes, refs, file, created_at, updated_at, created_by, updated_by, full_path FROM __temp__sheet');
        $this->addSql('DROP TABLE __temp__sheet');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE member_organization (member_id INTEGER NOT NULL, organization_id INTEGER NOT NULL, PRIMARY KEY (member_id, organization_id), CONSTRAINT FK_B45DFDF47597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B45DFDF432C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B45DFDF432C8A3DE ON member_organization (organization_id)');
        $this->addSql('CREATE INDEX IDX_B45DFDF47597D3FE ON member_organization (member_id)');
        $this->addSql('CREATE TABLE organization (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE "BINARY", type VARCHAR(50) DEFAULT NULL COLLATE "BINARY", logo VARCHAR(255) DEFAULT NULL COLLATE "BINARY", created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL COLLATE "BINARY", updated_by VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->addSql('CREATE TEMPORARY TABLE __temp__credited_person AS SELECT id, type, created_at, updated_at, created_by, updated_by, sheet_id, person_id FROM credited_person');
        $this->addSql('DROP TABLE credited_person');
        $this->addSql('CREATE TABLE credited_person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, sheet_id INTEGER NOT NULL, person_id INTEGER NOT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_71F46BD18B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD1217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD132C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO credited_person (id, type, created_at, updated_at, created_by, updated_by, sheet_id, person_id) SELECT id, type, created_at, updated_at, created_by, updated_by, sheet_id, person_id FROM __temp__credited_person');
        $this->addSql('DROP TABLE __temp__credited_person');
        $this->addSql('CREATE INDEX IDX_71F46BD18B1206A5 ON credited_person (sheet_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD1217BBB47 ON credited_person (person_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD132C8A3DE ON credited_person (organization_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person AS SELECT id, name, created_at, updated_at, created_by, updated_by FROM person');
        $this->addSql('DROP TABLE person');
        $this->addSql('CREATE TABLE person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_34DCD17632C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO person (id, name, created_at, updated_at, created_by, updated_by) SELECT id, name, created_at, updated_at, created_by, updated_by FROM __temp__person');
        $this->addSql('DROP TABLE __temp__person');
        $this->addSql('CREATE INDEX IDX_34DCD17632C8A3DE ON person (organization_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__set_list_item AS SELECT id, position, name, notes, created_at, updated_at, created_by, updated_by, setlist_id, sheet_id FROM set_list_item');
        $this->addSql('DROP TABLE set_list_item');
        $this->addSql('CREATE TABLE set_list_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, position INTEGER NOT NULL, name VARCHAR(100) NOT NULL, notes CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, setlist_id INTEGER NOT NULL, sheet_id INTEGER NOT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_2835F3D60D8C499 FOREIGN KEY (setlist_id) REFERENCES setlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2835F3D8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2835F3D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO set_list_item (id, position, name, notes, created_at, updated_at, created_by, updated_by, setlist_id, sheet_id) SELECT id, position, name, notes, created_at, updated_at, created_by, updated_by, setlist_id, sheet_id FROM __temp__set_list_item');
        $this->addSql('DROP TABLE __temp__set_list_item');
        $this->addSql('CREATE INDEX IDX_2835F3D60D8C499 ON set_list_item (setlist_id)');
        $this->addSql('CREATE INDEX IDX_2835F3D8B1206A5 ON set_list_item (sheet_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_position_per_setlist ON set_list_item (setlist_id, position)');
        $this->addSql('CREATE INDEX IDX_2835F3D32C8A3DE ON set_list_item (organization_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setlist AS SELECT id, title, date, notes, created_at, updated_at, created_by, updated_by FROM setlist');
        $this->addSql('DROP TABLE setlist');
        $this->addSql('CREATE TABLE setlist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATE DEFAULT NULL, notes CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_710BEA2A32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO setlist (id, title, date, notes, created_at, updated_at, created_by, updated_by) SELECT id, title, date, notes, created_at, updated_at, created_by, updated_by FROM __temp__setlist');
        $this->addSql('DROP TABLE __temp__setlist');
        $this->addSql('CREATE INDEX IDX_710BEA2A32C8A3DE ON setlist (organization_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sheet AS SELECT id, title, genre, difficulty, duration, key_signature, notes, refs, file, full_path, created_at, updated_at, created_by, updated_by FROM sheet');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('CREATE TABLE sheet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, genre VARCHAR(100) DEFAULT NULL, difficulty VARCHAR(20) DEFAULT NULL, duration VARCHAR(50) DEFAULT NULL, key_signature VARCHAR(50) DEFAULT NULL, notes CLOB DEFAULT NULL, refs CLOB NOT NULL, file VARCHAR(255) DEFAULT NULL, full_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_873C91E232C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sheet (id, title, genre, difficulty, duration, key_signature, notes, refs, file, full_path, created_at, updated_at, created_by, updated_by) SELECT id, title, genre, difficulty, duration, key_signature, notes, refs, file, full_path, created_at, updated_at, created_by, updated_by FROM __temp__sheet');
        $this->addSql('DROP TABLE __temp__sheet');
        $this->addSql('CREATE INDEX IDX_873C91E232C8A3DE ON sheet (organization_id)');
    }
}
