<?php

declare(strict_types=1);

namespace CommerceWeaversSyliusTpayMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250214154205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce PaymentMethodImage entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cw_sylius_tpay_payment_method_image (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_4470094A7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cw_sylius_tpay_payment_method_image ADD CONSTRAINT FK_4470094A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES sylius_payment_method (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cw_sylius_tpay_payment_method_image');
    }
}
