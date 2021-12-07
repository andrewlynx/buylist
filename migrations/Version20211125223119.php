<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211125223119 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favourite_lists (task_list_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_A9531ED0224F3C61 (task_list_id), INDEX IDX_A9531ED0A76ED395 (user_id), PRIMARY KEY(task_list_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE favourite_lists ADD CONSTRAINT FK_A9531ED0224F3C61 FOREIGN KEY (task_list_id) REFERENCES task_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favourite_lists ADD CONSTRAINT FK_A9531ED0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE favourite_lists');
    }
}
