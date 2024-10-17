<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Model;

class PaymentDetails
{
    public function __construct(
        private ?string $transactionId,
        private ?string $result = null,
        private ?string $status = null,
        #[\SensitiveParameter]
        private ?string $blikToken = null,
        private ?bool $blikSaveAlias = null,
        private ?bool $blikUseAlias = null,
        #[\SensitiveParameter]
        private ?string $googlePayToken = null,
        #[\SensitiveParameter]
        private ?string $encodedCardData = null,
        private ?string $paymentUrl = null,
        private ?string $successUrl = null,
        private ?string $failureUrl = null,
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

    public function getBlikToken(): ?string
    {
        return $this->blikToken;
    }

    public function setBlikToken(?string $blikToken): void
    {
        $this->blikToken = $blikToken;
    }

    public function isBlikSaveAlias(): ?bool
    {
        return $this->blikSaveAlias;
    }

    public function setBlikSaveAlias(bool $saveAlias): void
    {
        $this->blikSaveAlias = $saveAlias;
    }

    public function isBlikUseAlias(): ?bool
    {
        return $this->blikUseAlias;
    }

    public function setBlikUseAlias(bool $useAlias): void
    {
        $this->blikUseAlias = $useAlias;
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

    public function clearSensitiveData(): void
    {
        $this->blikToken = null;
        $this->googlePayToken = null;
        $this->encodedCardData = null;
    }

    public function isBlik(): bool
    {
        return null !== $this->blikToken || true === $this->blikUseAlias;
    }

    public static function fromArray(array $details): self
    {
        return new self(
            $details['tpay']['transaction_id'] ?? null,
            $details['tpay']['result'] ?? null,
            $details['tpay']['status'] ?? null,
            $details['tpay']['blik_token'] ?? null,
            $details['tpay']['blik_save_alias'] ?? null,
            $details['tpay']['blik_use_alias'] ?? null,
            $details['tpay']['google_pay_token'] ?? null,
            $details['tpay']['card'] ?? null,
            $details['tpay']['payment_url'] ?? null,
            $details['tpay']['success_url'] ?? null,
            $details['tpay']['failure_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tpay' => [
                'transaction_id' => $this->transactionId,
                'result' => $this->result,
                'status' => $this->status,
                'blik_token' => $this->blikToken,
                'blik_save_alias' => $this->blikSaveAlias,
                'blik_use_alias' => $this->blikUseAlias,
                'google_pay_token' => $this->googlePayToken,
                'card' => $this->encodedCardData,
                'payment_url' => $this->paymentUrl,
                'success_url' => $this->successUrl,
                'failure_url' => $this->failureUrl,
            ],
        ];
    }
}
