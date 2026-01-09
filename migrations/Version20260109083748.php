<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109083748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__member AS SELECT id, email, name, password, roles, created_at, updated_at FROM member');
        $this->addSql('DROP TABLE member');
        $this->addSql('CREATE TABLE member (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO member (id, email, name, password, roles, created_at, updated_at) SELECT id, email, name, password, roles, created_at, updated_at FROM __temp__member');
        $this->addSql('DROP TABLE __temp__member');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70E4FA78E7927C74 ON member (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__member AS SELECT id, email, name, password, roles, created_at, updated_at FROM member');
        $this->addSql('DROP TABLE member');
        $this->addSql('CREATE TABLE member (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO member (id, email, name, password, roles, created_at, updated_at) SELECT id, email, name, password, roles, created_at, updated_at FROM __temp__member');
        $this->addSql('DROP TABLE __temp__member');
    }
}
