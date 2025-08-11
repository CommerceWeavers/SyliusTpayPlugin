<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay;

use Tpay\OpenApi\Api\ApiAction;
use Tpay\OpenApi\Api\Authorization\AuthorizationApi;
use Tpay\OpenApi\Api\TpayApi as BaseTpayApi;
use Tpay\OpenApi\Model\Objects\Authorization\Token;
use Tpay\OpenApi\Utilities\Cache;
use Tpay\OpenApi\Utilities\TpayException;
use Webmozart\Assert\Assert;

class TpayApi extends BaseTpayApi
{
    private const SCOPE = 'read';

    private ApplePayApi|null $applePayApi = null;

    private Token|null $token = null;

    private string $apiUrl;

    public function __construct(
        Cache $cache,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly bool $productionMode = false,
        private readonly ?string $apiUrlOverride = null,
        private readonly ?string $clientName = null,
        private readonly ?string $notificationSecretCode = null,
    ) {
        parent::__construct($cache, $clientId, $clientSecret, $productionMode, $apiUrlOverride, $clientName);

        $this->apiUrl = $apiUrlOverride ?? (true === $this->productionMode
            ? ApiAction::TPAY_API_URL_PRODUCTION
            : ApiAction::TPAY_API_URL_SANDBOX);
    }

    /**
     * @throws TpayException
     */
    public function applePay(): ApplePayApi
    {
        $this->authorize();
        if (null === $this->applePayApi) {
            Assert::notNull($this->token);

            $this->applePayApi = (new ApplePayApi($this->token, $this->productionMode));

            if ($this->apiUrlOverride) {
                $this->applePayApi->overrideApiUrl($this->apiUrlOverride);
            }

            if ($this->clientName) {
                $this->applePayApi->setClientName($this->clientName);
            }
        }

        return $this->applePayApi;
    }

    /**
     * @throws TpayException
     */
    private function authorize(): void
    {
        if (
            $this->token instanceof Token &&
            time() <= $this->token->issued_at->getValue() + $this->token->expires_in->getValue()
        ) {
            return;
        }

        $authApi = (new AuthorizationApi(new Token(), $this->productionMode))->overrideApiUrl($this->apiUrl);

        if ($this->clientName) {
            $authApi->setClientName($this->clientName);
        }

        $fields = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => self::SCOPE,
        ];
        $authApi->getNewToken($fields);

        if (200 !== $authApi->getHttpResponseCode()) {
            throw new TpayException(
                sprintf(
                    'Authorization error. HTTP code: %d, response: %s',
                    $authApi->getHttpResponseCode(),
                    json_encode($authApi->getRequestResult()),
                ),
            );
        }

        $this->token = new Token();
        $this->token->setObjectValues($this->token, $authApi->getRequestResult());
    }

    public function getNotificationSecretCode(): ?string
    {
        return $this->notificationSecretCode;
    }
}
