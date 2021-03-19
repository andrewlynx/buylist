<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210317185428 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE FULLTEXT INDEX IDX_6CA8B1655E237E06C8F8AE1F ON task_item (name, qty)');
        $this->addSql('CREATE FULLTEXT INDEX IDX_377B6C635E237E066DE44026 ON task_list (name, description)');
        $this->addSql('CREATE FULLTEXT INDEX IDX_8D93D649E7927C74A045A5E9 ON user (email, nick_name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_6CA8B1655E237E06C8F8AE1F ON task_item');
        $this->addSql('DROP INDEX IDX_377B6C635E237E066DE44026 ON task_list');
        $this->addSql('DROP INDEX IDX_8D93D649E7927C74A045A5E9 ON user');
    }
}
