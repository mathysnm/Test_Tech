<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110004527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application_log (id SERIAL NOT NULL, user_id INT DEFAULT NULL, action VARCHAR(100) NOT NULL, entity_type VARCHAR(50) DEFAULT NULL, entity_id INT DEFAULT NULL, details JSON DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_100BD51A76ED395 ON application_log (user_id)');
        $this->addSql('CREATE INDEX idx_action ON application_log (action)');
        $this->addSql('CREATE INDEX idx_created_at ON application_log (created_at)');
        $this->addSql('COMMENT ON COLUMN application_log.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE application_log ADD CONSTRAINT FK_100BD51A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE application_log DROP CONSTRAINT FK_100BD51A76ED395');
        $this->addSql('DROP TABLE application_log');
    }
}
