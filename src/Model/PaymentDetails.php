<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Model;

use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;

class PaymentDetails
{
    public function __construct(
        private ?string $transactionId,
        private ?string $result = null,
        private ?string $status = null,
        #[\SensitiveParameter]
        private ?string $applePayToken = null,
        #[\SensitiveParameter]
        private ?string $blikToken = null,
        #[\SensitiveParameter]
        private ?string $blikAliasValue = null,
        private ?string $blikAliasApplicationCode = null,
        #[\SensitiveParameter]
        private ?string $googlePayToken = null,
        #[\SensitiveParameter]
        private ?string $encodedCardData = null,
        private bool $saveCreditCardForLater = false,
        #[\SensitiveParameter]
        private ?string $applePaySession = null,
        private ?string $paymentUrl = null,
        private ?string $successUrl = null,
        private ?string $failureUrl = null,
        private ?string $tpayChannelId = null,
        #[\SensitiveParameter]
        private ?string $visaMobilePhoneNumber = null,
        private ?string $errorMessage = null,
    ) {
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(string $result): void
    {
        $this->result = $result;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getApplePayToken(): ?string
    {
        return $this->applePayToken;
    }

    public function setApplePayToken(string $applePayToken): void
    {
        $this->applePayToken = $applePayToken;
    }

    public function getBlikToken(): ?string
    {
        return $this->blikToken;
    }

    public function setBlikToken(?string $blikToken): void
    {
        $this->blikToken = $blikToken;
    }

    public function getBlikAliasValue(): ?string
    {
        return $this->blikAliasValue;
    }

    public function setBlikAliasValue(?string $value): void
    {
        $this->blikAliasValue = $value;
    }

    public function getBlikAliasApplicationCode(): ?string
    {
        return $this->blikAliasApplicationCode;
    }

    public function setBlikAliasApplicationCode(?string $applicationCode): void
    {
        $this->blikAliasApplicationCode = $applicationCode;
    }

    public function getGooglePayToken(): ?string
    {
        return $this->googlePayToken;
    }

    public function setGooglePayToken(string $googlePayToken): void
    {
        $this->googlePayToken = $googlePayToken;
    }

    public function getEncodedCardData(): ?string
    {
        return $this->encodedCardData;
    }

    public function setEncodedCardData(string $encodedCardData): void
    {
        $this->encodedCardData = $encodedCardData;
    }

    public function isSaveCreditCardForLater(): bool
    {
        return $this->saveCreditCardForLater;
    }

    public function setSaveCreditCardForLater(?bool $saveCreditCardForLater): void
    {
        $this->saveCreditCardForLater = $saveCreditCardForLater;
    }

    public function getApplePaySession(): ?string
    {
        return $this->applePaySession;
    }

    public function setApplePaySession(?string $applePaySession): void
    {
        $this->applePaySession = $applePaySession;
    }

    public function getPaymentUrl(): ?string
    {
        return $this->paymentUrl;
    }

    public function setPaymentUrl(?string $paymentUrl): void
    {
        $this->paymentUrl = $paymentUrl;
    }

    public function getSuccessUrl(): ?string
    {
        return $this->successUrl;
    }

    public function setSuccessUrl(?string $successUrl): void
    {
        $this->successUrl = $successUrl;
    }

    public function getFailureUrl(): ?string
    {
        return $this->failureUrl;
    }

    public function setFailureUrl(?string $failureUrl): void
    {
        $this->failureUrl = $failureUrl;
    }

    public function getTpayChannelId(): ?string
    {
        return $this->tpayChannelId;
    }

    public function setTpayChannelId(?string $tpayChannelId): void
    {
        $this->tpayChannelId = $tpayChannelId;
    }

    public function getType(): string
    {
        return match (true) {
            null !== $this->getEncodedCardData() => PaymentType::CARD,
            null !== $this->getBlikToken() => PaymentType::BLIK,
            null !== $this->getTpayChannelId() => PaymentType::PAY_BY_LINK,
            null !== $this->getGooglePayToken() => PaymentType::GOOGLE_PAY,
            null !== $this->getApplePayToken() => PaymentType::APPLE_PAY,
            null !== $this->getVisaMobilePhoneNumber() => PaymentType::VISA_MOBILE,
            default => PaymentType::REDIRECT,
        };
    }

    public function getVisaMobilePhoneNumber(): ?string
    {
        return $this->visaMobilePhoneNumber;
    }

    public function setVisaMobilePhoneNumber(?string $visaMobilePhoneNumber): void
    {
        $this->visaMobilePhoneNumber = $visaMobilePhoneNumber;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function clearSensitiveData(): void
    {
        $this->applePayToken = null;
        $this->blikToken = null;
        $this->googlePayToken = null;
        $this->encodedCardData = null;
    }

    public function isBlik(): bool
    {
        return null !== $this->blikToken || null !== $this->blikAliasValue;
    }

    public static function fromArray(array $details): self
    {
        return new self(
            $details['tpay']['transaction_id'] ?? null,
            $details['tpay']['result'] ?? null,
            $details['tpay']['status'] ?? null,
            $details['tpay']['apple_pay_token'] ?? null,
            $details['tpay']['blik_token'] ?? null,
            $details['tpay']['blik_alias_value'] ?? null,
            $details['tpay']['blik_alias_application_code'] ?? null,
            $details['tpay']['google_pay_token'] ?? null,
            $details['tpay']['card'] ?? null,
                $details['tpay']['saveCreditCardForLater'] ?? false,
            $details['tpay']['apple_pay_session'] ?? null,
            $details['tpay']['payment_url'] ?? null,
            $details['tpay']['success_url'] ?? null,
            $details['tpay']['failure_url'] ?? null,
            $details['tpay']['tpay_channel_id'] ?? null,
            $details['tpay']['visa_mobile_phone_number'] ?? null,
            $details['tpay']['error_message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tpay' => [
                'transaction_id' => $this->transactionId,
                'result' => $this->result,
                'status' => $this->status,
                'apple_pay_token' => $this->applePayToken,
                'blik_token' => $this->blikToken,
                'blik_alias_value' => $this->blikAliasValue,
                'blik_alias_application_code' => $this->blikAliasApplicationCode,
                'google_pay_token' => $this->googlePayToken,
                'card' => $this->encodedCardData,
                'saveCreditCardForLater' => $this->saveCreditCardForLater,
                'apple_pay_session' => $this->applePaySession,
                'payment_url' => $this->paymentUrl,
                'success_url' => $this->successUrl,
                'failure_url' => $this->failureUrl,
                'tpay_channel_id' => $this->tpayChannelId,
                'visa_mobile_phone_number' => $this->visaMobilePhoneNumber,
                'error_message' => $this->errorMessage,
            ],
        ];
    }
}
