<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Combined Migration: Représente la fusion des migrations précédentes.
 */
final class Version20241123000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fusionne les migrations de création et modification de la table payment';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is combined from the previous migrations
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, user_payment_id INT NOT NULL, number_payment VARCHAR(255) NOT NULL, masked_number_payment VARCHAR(255) NOT NULL, type_payment VARCHAR(255) NOT NULL, expiration_date_payment VARCHAR(5) NOT NULL, active_payment TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', firstname_payment VARCHAR(255) NOT NULL, lastname_payment VARCHAR(255) NOT NULL, select_payment TINYINT(1) NOT NULL, INDEX IDX_6D28840DA3A46557 (user_payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA3A46557 FOREIGN KEY (user_payment_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is combined from the previous migrations
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DA3A46557');
        $this->addSql('DROP TABLE payment');
    }
}