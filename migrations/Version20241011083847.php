<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011083847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE basket ADD discount_code_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE basket ADD CONSTRAINT FK_2246507B91D29306 FOREIGN KEY (discount_code_id) REFERENCES discount_code (id)');
        $this->addSql('CREATE INDEX IDX_2246507B91D29306 ON basket (discount_code_id)');

        // Set default values for existing NULL price_tva values
        $this->addSql('UPDATE basket_item SET price_tva = 0 WHERE price_tva IS NULL');

        // Now we can safely change the column to NOT NULL
        $this->addSql('ALTER TABLE basket_item CHANGE price_tva price_tva NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE basket DROP FOREIGN KEY FK_2246507B91D29306');
        $this->addSql('DROP INDEX IDX_2246507B91D29306 ON basket');
        $this->addSql('ALTER TABLE basket DROP discount_code_id');

        // Reverse the column change
        $this->addSql('ALTER TABLE basket_item CHANGE price_tva price_tva NUMERIC(10, 2) DEFAULT NULL');
    }
}
