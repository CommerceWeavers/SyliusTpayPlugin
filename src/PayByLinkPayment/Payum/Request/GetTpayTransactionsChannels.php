<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Request;

use Payum\Core\Request\Generic;

class GetTpayTransactionsChannels extends Generic
{
    protected array $result;

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): void
    {
        $this->result = $result;
    }
}
