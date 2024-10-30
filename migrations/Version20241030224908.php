<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241030224908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE basket_item CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE price_tva price_tva DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE price_tva price_tva DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product CHANGE price price NUMERIC(10, 2) NOT NULL, CHANGE price_tva price_tva NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE basket_item CHANGE price price NUMERIC(10, 2) NOT NULL, CHANGE price_tva price_tva NUMERIC(10, 2) DEFAULT NULL');
    }
}
