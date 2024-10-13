<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241010100024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute et modifie la colonne price_tva dans les tables basket_item et product';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE basket_item ADD price_tva NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE basket_item CHANGE price_tva price_tva NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD price_tva NUMERIC(10, 0) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE basket_item DROP price_tva');
        $this->addSql('ALTER TABLE product DROP price_tva');
    }
}
