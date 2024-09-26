<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\GetBankGroupList;

// TODO rename to get channels
class GetBankGroupListAction extends BaseApiAwareAction
{
    /**
     * @param GetBankGroupList $request
     */
    public function execute($request): void
    {
        $result = $this->api->transactions()->getChannels();

        $request->setResult($result);
    }

    public function supports($request): bool
    {
        return $request instanceof GetBankGroupList;
    }
}
