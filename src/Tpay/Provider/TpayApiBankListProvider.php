<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Provider;

use CommerceWeavers\SyliusTpayPlugin\Payum\Exception\UnableToGetBankListException;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\GetTpayTransactionsChannels;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Payum;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class TpayApiBankListProvider implements TpayApiBankListProviderInterface
{
    public function __construct(
        private readonly Payum $payum,
        private readonly CacheInterface $cache,
    ) {
    }

    public function provide(): array
    {
        $gateway = $this->payum->getGateway('tpay');

        /** @var GetTpayTransactionsChannels $value */
        $value = $this->cache->get('tpay_bank_list', function (ItemInterface $item) use ($gateway): GetTpayTransactionsChannels {
            $item->expiresAfter(new \DateInterval('P1D'));
            $gateway->execute($value = new GetTpayTransactionsChannels(new ArrayObject()), true);

            return $value;
        });

        $result = $value->getResult();

        if (!isset($result['result']) || 'success' !== $result['result']) {
            throw new UnableToGetBankListException('Unable to get banks list. Response: ' . json_encode($result));
        }

        return array_filter($result['channels'], static function ($channel) {
            return $channel['instantRedirection'] === true &&
                $channel['onlinePayment'] === true;
        });
    }
}
