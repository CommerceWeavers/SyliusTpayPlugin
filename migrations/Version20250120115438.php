<?php

declare(strict_types=1);

namespace CommerceWeaversSyliusTpayMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250120115438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate channel based tpay_pbl gateway to tpay_pbl_channel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            UPDATE sylius_gateway_config
            SET
                gateway_name = 'tpay_pbl_channel',
                factory_name = 'tpay_pbl_channel'
            WHERE gateway_name = 'tpay_pbl'
            AND JSON_EXTRACT(config, '$.tpay_channel_id') IS NOT NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            UPDATE sylius_gateway_config
            SET
                gateway_name = 'tpay_pbl',
                factory_name = 'tpay_pbl'
            WHERE gateway_name = 'tpay_pbl_channel'
            AND JSON_EXTRACT(config, '$.tpay_channel_id') IS NOT NULL
        ");
    }
}
