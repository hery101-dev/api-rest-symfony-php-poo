<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240225220012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recommandation (id INT AUTO_INCREMENT NOT NULL, postulant_id INT DEFAULT NULL, recommend_job_id INT DEFAULT NULL, INDEX IDX_C7782A281CD30E78 (postulant_id), INDEX IDX_C7782A28A9377810 (recommend_job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recommandation ADD CONSTRAINT FK_C7782A281CD30E78 FOREIGN KEY (postulant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE recommandation ADD CONSTRAINT FK_C7782A28A9377810 FOREIGN KEY (recommend_job_id) REFERENCES job_offer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recommandation DROP FOREIGN KEY FK_C7782A281CD30E78');
        $this->addSql('ALTER TABLE recommandation DROP FOREIGN KEY FK_C7782A28A9377810');
        $this->addSql('DROP TABLE recommandation');
    }
}
