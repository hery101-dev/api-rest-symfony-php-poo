<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240109113321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contrat_job_offer (contrat_id INT NOT NULL, job_offer_id INT NOT NULL, INDEX IDX_A38D4DBE1823061F (contrat_id), INDEX IDX_A38D4DBE3481D195 (job_offer_id), PRIMARY KEY(contrat_id, job_offer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contrat_job_offer ADD CONSTRAINT FK_A38D4DBE1823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contrat_job_offer ADD CONSTRAINT FK_A38D4DBE3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer DROP contrat');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contrat_job_offer DROP FOREIGN KEY FK_A38D4DBE1823061F');
        $this->addSql('ALTER TABLE contrat_job_offer DROP FOREIGN KEY FK_A38D4DBE3481D195');
        $this->addSql('DROP TABLE contrat');
        $this->addSql('DROP TABLE contrat_job_offer');
        $this->addSql('ALTER TABLE job_offer ADD contrat VARCHAR(255) DEFAULT NULL');
    }
}
