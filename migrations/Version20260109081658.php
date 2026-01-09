<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109081658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE set_list_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, position INTEGER NOT NULL, name VARCHAR(100) NOT NULL, notes CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, setlist_id INTEGER NOT NULL, sheet_id INTEGER NOT NULL, organization_id INTEGER NOT NULL, CONSTRAINT FK_2835F3D60D8C499 FOREIGN KEY (setlist_id) REFERENCES setlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2835F3D8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2835F3D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2835F3D60D8C499 ON set_list_item (setlist_id)');
        $this->addSql('CREATE INDEX IDX_2835F3D8B1206A5 ON set_list_item (sheet_id)');
        $this->addSql('CREATE INDEX IDX_2835F3D32C8A3DE ON set_list_item (organization_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_position_per_setlist ON set_list_item (setlist_id, position)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE set_list_item');
    }
}
