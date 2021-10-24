<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018105622 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_list_data (id INT AUTO_INCREMENT NOT NULL, task_list_id INT DEFAULT NULL, type INT DEFAULT NULL, cloned TINYINT(1) DEFAULT NULL, UNIQUE INDEX UNIQ_9717E1DB224F3C61 (task_list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_list_data ADD CONSTRAINT FK_9717E1DB224F3C61 FOREIGN KEY (task_list_id) REFERENCES task_list (id)');
        $this->addSql('ALTER TABLE task_list ADD type INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE helpers helpers TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE task_list_data');
        $this->addSql('ALTER TABLE task_list DROP type');
        $this->addSql('ALTER TABLE user CHANGE helpers helpers TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
