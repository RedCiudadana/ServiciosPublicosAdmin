<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230511160705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE public_service_sub_category (public_service_id INT NOT NULL, sub_category_id INT NOT NULL, PRIMARY KEY(public_service_id, sub_category_id))');
        $this->addSql('CREATE INDEX IDX_F632847177CE3D8B ON public_service_sub_category (public_service_id)');
        $this->addSql('CREATE INDEX IDX_F6328471F7BFE87C ON public_service_sub_category (sub_category_id)');
        $this->addSql('ALTER TABLE public_service_sub_category ADD CONSTRAINT FK_F632847177CE3D8B FOREIGN KEY (public_service_id) REFERENCES public_service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public_service_sub_category ADD CONSTRAINT FK_F6328471F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Synchronize data from previous column
        $this->addSql('insert into public_service_sub_category (public_service_id, sub_category_id) select id, subcategory_id from public_service;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE public_service_sub_category');
    }
}
