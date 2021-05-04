<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210425192853 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_favourite (user INT NOT NULL, favourites INT NOT NULL, INDEX IDX_624247FD8D93D649 (user), INDEX IDX_624247FD7F07C501 (favourites), PRIMARY KEY(user, favourites)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_banned (user INT NOT NULL, banned INT NOT NULL, INDEX IDX_FE1B168B8D93D649 (user), INDEX IDX_FE1B168B9B490DB6 (banned), PRIMARY KEY(user, banned)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_favourite ADD CONSTRAINT FK_624247FD8D93D649 FOREIGN KEY (user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_favourite ADD CONSTRAINT FK_624247FD7F07C501 FOREIGN KEY (favourites) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_banned ADD CONSTRAINT FK_FE1B168B8D93D649 FOREIGN KEY (user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_banned ADD CONSTRAINT FK_FE1B168B9B490DB6 FOREIGN KEY (banned) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_favourite');
        $this->addSql('DROP TABLE user_banned');
    }
}
