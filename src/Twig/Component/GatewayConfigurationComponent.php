<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Twig\Component;

use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Resource\Model\ResourceInterface;
use Sylius\TwigHooks\LiveComponent\HookableLiveComponentTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class GatewayConfigurationComponent
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;
    use HookableLiveComponentTrait;

    #[LiveProp(hydrateWith: 'hydratePaymentMethod', dehydrateWith: 'dehydratePaymentMethod')]
    public PaymentMethodInterface $paymentMethod;

    #[LiveProp]
    public string $connectionTestResult = '';

    public function __construct(
        private readonly string $formClass,
        private readonly string $gatewayName,
        private readonly FormFactoryInterface $formFactory,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly PaymentMethodFactoryInterface $paymentMethodFactory,
    ) {
    }

    public function hydratePaymentMethod(mixed $value): ?ResourceInterface
    {
        if ($value === null) {
            return $this->paymentMethodFactory->createWithGateway($this->gatewayName);
        }

        return $this->paymentMethodRepository->find($value);
    }

    public function dehydratePaymentMethod(?PaymentMethodInterface $paymentMethod): ?int
    {
        return $paymentMethod?->getId();
    }

    #[LiveAction]
    public function testConnection(): void
    {
        $this->connectionTestResult = '';

        $clientId = $this->formValues['gatewayConfig']['config']['client_id'] ?? '';
        $clientSecret = $this->formValues['gatewayConfig']['config']['client_secret'] ?? '';
        $productionMode = (bool) $this->formValues['gatewayConfig']['config']['production_mode'] ?? false;

        if (empty($clientId) || empty($clientSecret)) {
            $this->connectionTestResult = 'failure';
            $this->dispatchBrowserEvent('cw_tpay:gateway_configuration:connection_tested', ['result' => 'failure']);

            return;
        }

        try {
            $tpayApi = new TpayApi($clientId, $clientSecret, $productionMode);
            $tpayApi->transactions()->getChannels();

            $this->connectionTestResult = 'success';
            $this->dispatchBrowserEvent('cw_tpay:gateway_configuration:connection_tested', ['result' => 'success']);
        } catch (\Exception $e) {
            $this->connectionTestResult = 'failure';
            $this->dispatchBrowserEvent('cw_tpay:gateway_configuration:connection_tested', ['result' => 'failure']);
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create($this->formClass, $this->paymentMethod);
    }
}
