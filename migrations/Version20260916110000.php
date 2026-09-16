<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añadir un precio de venta opcional a cada producto';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD sale_price NUMERIC(12, 4) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP sale_price');
    }
}
