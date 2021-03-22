<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210322180825 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE cart_products_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cart_products (id INT NOT NULL, cart_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D2515311AD5CDBF ON cart_products (cart_id)');
        $this->addSql('CREATE INDEX IDX_2D2515314584665A ON cart_products (product_id)');
        $this->addSql('ALTER TABLE cart_products ADD CONSTRAINT FK_2D2515311AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cart_products ADD CONSTRAINT FK_2D2515314584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE cart_products_id_seq CASCADE');
        $this->addSql('DROP TABLE cart_products');
    }
}
