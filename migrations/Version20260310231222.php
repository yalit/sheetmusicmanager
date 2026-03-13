<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310231222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__sheet AS SELECT id, title, tags, notes, refs, files, full_path, created_at, updated_at, created_by, updated_by FROM sheet');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('CREATE TABLE sheet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, tags CLOB DEFAULT NULL, notes CLOB DEFAULT NULL, refs CLOB DEFAULT NULL, files value_object_array NOT NULL, full_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO sheet (id, title, tags, notes, refs, files, full_path, created_at, updated_at, created_by, updated_by) SELECT id, title, tags, notes, refs, files, full_path, created_at, updated_at, created_by, updated_by FROM __temp__sheet');
        $this->addSql('DROP TABLE __temp__sheet');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__sheet AS SELECT id, title, tags, notes, refs, files, full_path, created_at, updated_at, created_by, updated_by FROM sheet');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('CREATE TABLE sheet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, tags CLOB DEFAULT NULL, notes CLOB DEFAULT NULL, refs CLOB DEFAULT NULL, files CLOB NOT NULL, full_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO sheet (id, title, tags, notes, refs, files, full_path, created_at, updated_at, created_by, updated_by) SELECT id, title, tags, notes, refs, files, full_path, created_at, updated_at, created_by, updated_by FROM __temp__sheet');
        $this->addSql('DROP TABLE __temp__sheet');
    }
}
