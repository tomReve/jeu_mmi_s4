<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190315080236 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE partie ADD perdant_id INT DEFAULT NULL, CHANGE gagnant gagnant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3D2F942B8 FOREIGN KEY (gagnant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3DA4CE924 FOREIGN KEY (perdant_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_59B1F3D2F942B8 ON partie (gagnant_id)');
        $this->addSql('CREATE INDEX IDX_59B1F3DA4CE924 ON partie (perdant_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE partie DROP FOREIGN KEY FK_59B1F3D2F942B8');
        $this->addSql('ALTER TABLE partie DROP FOREIGN KEY FK_59B1F3DA4CE924');
        $this->addSql('DROP INDEX IDX_59B1F3D2F942B8 ON partie');
        $this->addSql('DROP INDEX IDX_59B1F3DA4CE924 ON partie');
        $this->addSql('ALTER TABLE partie ADD gagnant INT DEFAULT NULL, DROP gagnant_id, DROP perdant_id');
    }
}
