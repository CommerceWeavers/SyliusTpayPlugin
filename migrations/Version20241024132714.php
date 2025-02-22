<?php

declare(strict_types=1);

namespace CommerceWeaversSyliusTpayMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241024132714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce BlikAlias entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cw_sylius_tpay_blik_alias (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, value VARCHAR(255) NOT NULL, expiration_date DATETIME DEFAULT NULL, registered TINYINT(1) DEFAULT false NOT NULL, UNIQUE INDEX UNIQ_72E078BE9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cw_sylius_tpay_blik_alias ADD CONSTRAINT FK_72E078BE9395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cw_sylius_tpay_blik_alias DROP FOREIGN KEY FK_72E078BE9395C3F3');
        $this->addSql('DROP TABLE cw_sylius_tpay_blik_alias');
    }
}
