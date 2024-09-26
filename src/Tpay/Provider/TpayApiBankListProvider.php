<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Provider;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\GetBankGroupList;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Payum;
use Tpay\OpenApi\Utilities\TpayException;

final class TpayApiBankListProvider implements TpayApiBankListProviderInterface
{
    // TODO make it cached (but here or inside of GetBankGroupListAction?)
    public function __construct(
        private Payum $payum,
    ) {
    }

    public function provide(): array
    {
        $gateway = $this->payum->getGateway('tpay');

        $gateway->execute($result = new GetBankGroupList(new ArrayObject()), true);

        $result = $result->getResult();

        if (!isset($result['result']) || 'success' !== $result['result']) {
            throw new TpayException('Unable to get banks list. Response: ' . json_encode($result));
        }

        // TODO if from L28:30 or this ?
        // Assert::keyExists($result, 'result', 'Unable to get banks list. Response: '.json_encode($result));
        // Assert::same($result['result'], 'success', 'Unable to get banks list. Response: '.json_encode($result));

        return array_filter($result['channels'], static function ($channel) {
            return $channel['instantRedirection'] === true &&
                $channel['onlinePayment'] === true;
        });
    }
}
