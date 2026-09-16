<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añadir un color a los productos y asignar colores aleatorios a los existentes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD color VARCHAR(7) DEFAULT NULL');
        $this->addSql("UPDATE product SET color = LOWER(CONCAT('#', LPAD(HEX(FLOOR(RAND() * 16777216)), 6, '0'))) WHERE color IS NULL");
        $this->addSql('ALTER TABLE product MODIFY color VARCHAR(7) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP color');
    }
}
