<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añadir PVP con IVA del 10 %, conservando los precios sin IVA existentes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD retail_price NUMERIC(13, 4) DEFAULT NULL');
        $this->addSql('UPDATE product SET retail_price = ROUND(sale_price * 1.10, 4) WHERE sale_price IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP retail_price');
    }
}
