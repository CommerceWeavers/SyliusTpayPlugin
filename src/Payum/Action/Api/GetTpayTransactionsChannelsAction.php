<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\GetTpayTransactionsChannels;

class GetTpayTransactionsChannelsAction extends BaseApiAwareAction
{
    /**
     * @param GetTpayTransactionsChannels $request
     */
    public function execute($request): void
    {
        $result = $this->api->transactions()->getChannels();

        $request->setResult($result);
    }

    public function supports($request): bool
    {
        return $request instanceof GetTpayTransactionsChannels;
    }
}
