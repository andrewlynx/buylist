<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210102093854 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_item (id INT AUTO_INCREMENT NOT NULL, task_list_id INT NOT NULL, name VARCHAR(64) NOT NULL, qty VARCHAR(64) DEFAULT NULL, completed TINYINT(1) NOT NULL, INDEX IDX_6CA8B165224F3C61 (task_list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_item ADD CONSTRAINT FK_6CA8B165224F3C61 FOREIGN KEY (task_list_id) REFERENCES task_list (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE task_item');
    }
}
