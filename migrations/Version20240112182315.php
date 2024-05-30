<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240112182315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC193CB796C');
        $this->addSql('DROP INDEX IDX_A45BDDC193CB796C ON application');
        $this->addSql('ALTER TABLE application ADD application_resume VARCHAR(255) DEFAULT NULL, DROP file_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application ADD file_id INT DEFAULT NULL, DROP application_resume');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC193CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A45BDDC193CB796C ON application (file_id)');
    }
}
