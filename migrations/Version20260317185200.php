<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260317185200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE credited_person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, person_type_id INTEGER NOT NULL, sheet_id INTEGER NOT NULL, person_id INTEGER NOT NULL, CONSTRAINT FK_71F46BD1E7D23F1A FOREIGN KEY (person_type_id) REFERENCES person_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD18B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71F46BD1217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_71F46BD1E7D23F1A ON credited_person (person_type_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD18B1206A5 ON credited_person (sheet_id)');
        $this->addSql('CREATE INDEX IDX_71F46BD1217BBB47 ON credited_person (person_id)');
        $this->addSql('CREATE TABLE member (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70E4FA78E7927C74 ON member (email)');
        $this->addSql('CREATE TABLE person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE person_type (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL)');
        $this->addSql('CREATE TABLE set_list_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, position INTEGER NOT NULL, name VARCHAR(100) NOT NULL, notes CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, setlist_id INTEGER NOT NULL, sheet_id INTEGER NOT NULL, CONSTRAINT FK_2835F3D60D8C499 FOREIGN KEY (setlist_id) REFERENCES setlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2835F3D8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2835F3D60D8C499 ON set_list_item (setlist_id)');
        $this->addSql('CREATE INDEX IDX_2835F3D8B1206A5 ON set_list_item (sheet_id)');
        $this->addSql('CREATE TABLE setlist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATE DEFAULT NULL, notes CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE sheet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, tags CLOB DEFAULT NULL, notes CLOB DEFAULT NULL, refs CLOB DEFAULT NULL, files CLOB NOT NULL, full_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE credited_person');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_type');
        $this->addSql('DROP TABLE set_list_item');
        $this->addSql('DROP TABLE setlist');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
