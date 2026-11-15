<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Tpay\OpenApi\Utilities\TpayException;

/**
 * @property TpayApi $api
 */
abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = TpayApi::class;
    }

    protected function do(callable $func, callable $onSuccess, callable $onFailure, ?callable $onUnknownOutcome = null): void
    {
        try {
            $response = $func();
        } catch (TpayException $e) {
            if (null !== $onUnknownOutcome && $this->isOutcomeUnknown($e)) {
                $onUnknownOutcome();

                return;
            }

            $response = ['result' => 'failed', 'error' => $e->getMessage()];
        }

        $this->isResponseSuccessful($response) ? $onSuccess($response) : $onFailure($response);
    }

    private function isOutcomeUnknown(TpayException $exception): bool
    {
        return str_starts_with($exception->getMessage(), 'Unknown error response code');
    }

    /**
     * @param array<string, mixed> $response
     */
    private function isResponseSuccessful(array $response): bool
    {
        if (isset($response['result']) && $response['result'] === 'success') {
            return true;
        }

        return false;
    }
}
