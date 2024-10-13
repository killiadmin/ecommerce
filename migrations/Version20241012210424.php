<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241012202746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_address table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_address (
            id INT AUTO_INCREMENT NOT NULL, 
            user_associated_id INT NOT NULL, 
            number_delivery INT DEFAULT NULL, 
            libelle_delivery VARCHAR(255) DEFAULT NULL, 
            code_delivery INT DEFAULT NULL, 
            city_delivery VARCHAR(255) DEFAULT NULL, 
            additionnal_information VARCHAR(255) DEFAULT NULL, 
            billing TINYINT(1) NOT NULL, 
            number_billing INT DEFAULT NULL, 
            libelle_billing VARCHAR(255) DEFAULT NULL, 
            code_billing INT DEFAULT NULL, 
            city_billing VARCHAR(255) DEFAULT NULL, 
            INDEX IDX_5543718B4DC95A3E (user_associated_id), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718B4DC95A3E FOREIGN KEY (user_associated_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE basket_item CHANGE price_tva price_tva NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_address DROP FOREIGN KEY FK_5543718B4DC95A3E');
        $this->addSql('DROP TABLE user_address');
        $this->addSql('ALTER TABLE basket_item CHANGE price_tva price_tva NUMERIC(10, 2) DEFAULT NULL');
    }
}