<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109083033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE member (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE member_organization (member_id INTEGER NOT NULL, organization_id INTEGER NOT NULL, PRIMARY KEY (member_id, organization_id), CONSTRAINT FK_B45DFDF47597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B45DFDF432C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B45DFDF47597D3FE ON member_organization (member_id)');
        $this->addSql('CREATE INDEX IDX_B45DFDF432C8A3DE ON member_organization (organization_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE member_organization');
    }
}
