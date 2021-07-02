<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701125532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398AF218402');
        $this->addSql('DROP INDEX UNIQ_F5299398AF218402 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP facturation_adress_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD facturation_adress_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398AF218402 FOREIGN KEY (facturation_adress_id) REFERENCES client (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5299398AF218402 ON `order` (facturation_adress_id)');
    }
}
