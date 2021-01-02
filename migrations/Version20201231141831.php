<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201231141831 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_list (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, name VARCHAR(64) NOT NULL, description VARCHAR(255) DEFAULT NULL, date DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_377B6C6361220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_list_user (task_list_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B777C4F1224F3C61 (task_list_id), INDEX IDX_B777C4F1A76ED395 (user_id), PRIMARY KEY(task_list_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_list ADD CONSTRAINT FK_377B6C6361220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task_list_user ADD CONSTRAINT FK_B777C4F1224F3C61 FOREIGN KEY (task_list_id) REFERENCES task_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_list_user ADD CONSTRAINT FK_B777C4F1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_list_user DROP FOREIGN KEY FK_B777C4F1224F3C61');
        $this->addSql('DROP TABLE task_list');
        $this->addSql('DROP TABLE task_list_user');
    }
}
