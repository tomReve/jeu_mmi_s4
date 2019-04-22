<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190314162831 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE partie CHANGE des des LONGTEXT DEFAULT NULL, CHANGE tour tour INT DEFAULT NULL, CHANGE plateau_j1 plateau_j1 LONGTEXT DEFAULT NULL, CHANGE plateau_j2 plateau_j2 LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE partie CHANGE des des LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE tour tour INT NOT NULL, CHANGE plateau_j1 plateau_j1 LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE plateau_j2 plateau_j2 LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
