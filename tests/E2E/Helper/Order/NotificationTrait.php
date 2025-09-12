<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order;

use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;

/** @mixin E2ETestCase */
trait NotificationTrait
{
    protected function sendNotificationForLastPayment(array $params): void
    {
        $token = $this->createNotifyTokenForLastPayment('tpay_card', 'en_US');

        $parts = parse_url($token->getTargetUrl());
        $pathWithQuery = ($parts['path'] ?? '') . (!empty($parts['query']) ? '?' . $parts['query'] : '');

        $body = http_build_query($params);
        $bodyJs = json_encode($body);
        $urlJs = json_encode($pathWithQuery);

        $this->client->executeScript(
            sprintf(
                "var xhr = new XMLHttpRequest();xhr.open('POST', %s, false);xhr.setRequestHeader('x-jws-signature','dummy');xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');xhr.send(%s);return xhr.status + '|' + xhr.responseText;",
                $urlJs,
                $bodyJs,
            ),
        );
    }

    protected function createNotifyTokenForLastPayment(string $gatewayName, string $locale): TokenInterface
    {
        $order = $this->getLastOrder();

        /** @var SyliusPaymentInterface $payment */
        $payment = $order->getLastPayment();

        /** @var NotifyTokenFactoryInterface $notifyTokenFactory */
        $notifyTokenFactory = static::getContainer()->get('commerce_weavers_sylius_tpay.payum.factory.token.notify');

        return $notifyTokenFactory->create($payment, $gatewayName, $locale);
    }

    protected function getLastOrder(): OrderInterface
    {
        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = static::getContainer()->get('sylius.repository.order');
        $qb = $orderRepository->createQueryBuilder('o')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(1);

        /** @var OrderInterface $order */
        $order = $qb->getQuery()->getOneOrNullResult();

        return $order;
    }
}
