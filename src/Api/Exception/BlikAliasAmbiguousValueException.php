<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Exception;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ErrorResource(
    normalizationContext: ['groups' => ['commerce_weavers_sylius_tpay:shop:error:show']],
)]
class BlikAliasAmbiguousValueException extends \Exception implements ProblemExceptionInterface
{
    /** @var array<array{applicationName: string, applicationCode: string}> */
    private array $applications = [];

    /**
     * @param array<array{applicationName: string, applicationCode: string}> $applications
     */
    public static function create(array $applications): self
    {
        $exception = new self(
            'Too many aliases found for a Blik alias. Specify one of the applications.',
        );

        $exception->applications = $applications;

        return $exception;
    }

    #[Groups(['commerce_weavers_sylius_tpay:shop:error:show'])]
    public function getType(): string
    {
        return '/errors/400';
    }

    #[Groups(['commerce_weavers_sylius_tpay:shop:error:show'])]
    public function getTitle(): string
    {
        return 'An error occurred';
    }

    #[Groups(['commerce_weavers_sylius_tpay:shop:error:show'])]
    public function getStatus(): int
    {
        return 400;
    }

    #[Groups(['commerce_weavers_sylius_tpay:shop:error:show'])]
    public function getDetail(): string
    {
        return $this->getMessage();
    }

    public function getInstance(): null
    {
        return null;
    }

    #[Groups(['commerce_weavers_sylius_tpay:shop:error:show'])]
    #[ApiProperty(description: 'List of all saved applications.', writable: false, initializable: false)]
    public function getApplications(): array
    {
        return $this->applications;
    }
}
