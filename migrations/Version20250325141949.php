<?php

declare(strict_types=1);

namespace CommerceWeaversSyliusTpayMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250325141949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce PaymentMethod::defaultImageUrl column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_payment_method ADD default_image_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_payment_method DROP default_image_url');
    }
}
