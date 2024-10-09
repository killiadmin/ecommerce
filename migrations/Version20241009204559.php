<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241009204559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE basket_item ADD price_tva NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE price_tva price_tva NUMERIC(10, 0) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE basket_item DROP price_tva');
        $this->addSql('ALTER TABLE product CHANGE price_tva price_tva VARCHAR(255) DEFAULT \'0\' NOT NULL');
    }
}
