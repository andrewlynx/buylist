<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210108130116 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_invitation (id INT AUTO_INCREMENT NOT NULL, task_list_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, created_date DATETIME NOT NULL, INDEX IDX_5DAE937D224F3C61 (task_list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE email_invitation ADD CONSTRAINT FK_5DAE937D224F3C61 FOREIGN KEY (task_list_id) REFERENCES task_list (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE email_invitation');
    }
}
