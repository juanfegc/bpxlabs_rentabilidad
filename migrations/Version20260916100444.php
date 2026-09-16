<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260916100444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crear productos, tipos de coste y costes unitarios';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cost_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(160) NOT NULL, UNIQUE INDEX UNIQ_D0C64E965E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(160) NOT NULL, UNIQUE INDEX UNIQ_D34A04AD5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_cost (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(12, 4) NOT NULL, product_id INT NOT NULL, cost_type_id INT NOT NULL, INDEX IDX_95CEB65D4584665A (product_id), INDEX IDX_95CEB65D832204AC (cost_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product_cost ADD CONSTRAINT FK_95CEB65D4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_cost ADD CONSTRAINT FK_95CEB65D832204AC FOREIGN KEY (cost_type_id) REFERENCES cost_type (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_cost DROP FOREIGN KEY FK_95CEB65D4584665A');
        $this->addSql('ALTER TABLE product_cost DROP FOREIGN KEY FK_95CEB65D832204AC');
        $this->addSql('DROP TABLE cost_type');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_cost');
    }
}
