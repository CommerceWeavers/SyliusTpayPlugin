<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\Exception;

use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;

#[ErrorResource(
    normalizationContext: ['groups' => ['commerce_weavers_sylius_tpay:shop:error:show']],
)]
class BankNotSelectedException extends \InvalidArgumentException implements ProblemExceptionInterface
{
    public static function create(): self
    {
        return new self('The given payment does not have a bank selected.');
    }

    public function getType(): string
    {
        return '/errors/424';
    }

    public function getTitle(): string
    {
        return 'An error occurred while processing your payment. Please try again or contact store support.';
    }

    public function getStatus(): int
    {
        return 424;
    }

    public function getDetail(): string
    {
        return $this->getMessage();
    }

    public function getInstance(): null
    {
        return null;
    }
}
