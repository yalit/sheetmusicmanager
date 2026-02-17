<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260129110155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sheet_tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('ALTER TABLE sheet ADD COLUMN full_path VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sheet_tag (sheet_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY (sheet_id, tag_id), CONSTRAINT FK_CE6A9A718B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CE6A9A71BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CE6A9A71BAD26311 ON sheet_tag (tag_id)');
        $this->addSql('CREATE INDEX IDX_CE6A9A718B1206A5 ON sheet_tag (sheet_id)');
        $this->addSql('CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL COLLATE "BINARY")');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B7835E237E06 ON tag (name)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sheet AS SELECT id, title, genre, difficulty, duration, key_signature, notes, refs, file, created_at, updated_at, created_by, updated_by, organization_id FROM sheet');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('CREATE TABLE sheet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, genre VARCHAR(100) DEFAULT NULL, difficulty VARCHAR(20) DEFAULT NULL, duration VARCHAR(50) DEFAULT NULL, key_signature VARCHAR(50) DEFAULT NULL, notes CLOB DEFAULT NULL, refs CLOB NOT NULL, file VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_873C91E232C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sheet (id, title, genre, difficulty, duration, key_signature, notes, refs, file, created_at, updated_at, created_by, updated_by, organization_id) SELECT id, title, genre, difficulty, duration, key_signature, notes, refs, file, created_at, updated_at, created_by, updated_by, organization_id FROM __temp__sheet');
        $this->addSql('DROP TABLE __temp__sheet');
        $this->addSql('CREATE INDEX IDX_873C91E232C8A3DE ON sheet (organization_id)');
    }
}
