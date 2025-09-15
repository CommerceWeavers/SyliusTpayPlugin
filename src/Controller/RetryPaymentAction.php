<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller;

use CommerceWeavers\SyliusTpayPlugin\Command\CancelLastPayment;
use CommerceWeavers\SyliusTpayPlugin\Payment\Exception\PaymentCannotBeCancelledException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Webmozart\Assert\Assert;

final class RetryPaymentAction
{
    private const ERROR_FLASH_TYPE = 'error';

    private const INFO_FLASH_TYPE = 'info';

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly MessageBusInterface $messageBus,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(Request $request, string $orderToken): Response
    {
        /** @var string|null $csrfToken */
        $csrfToken = $request->request->get('_csrf_token');

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($orderToken, $csrfToken))) {
            throw new BadRequestException('Invalid CSRF token');
        }

        $order = $this->findOrderOr404($orderToken);
        Assert::notNull($order->getTokenValue());

        if (null !== $order->getLastPayment(PaymentInterface::STATE_NEW)) {
            $this->addFlashMessage(self::INFO_FLASH_TYPE, 'commerce_weavers_sylius_tpay.shop.retry_payment.ready_to_retry');

            return new RedirectResponse(
                $this->router->generate('sylius_shop_order_show', ['tokenValue' => $orderToken]),
            );
        }

        try {
            $this->messageBus->dispatch(new CancelLastPayment($order->getTokenValue()));
        } catch (HandlerFailedException $exception) {
            if ($exception->getPrevious() instanceof PaymentCannotBeCancelledException) {
                $this->addFlashMessage(self::ERROR_FLASH_TYPE, 'commerce_weavers_sylius_tpay.shop.retry_payment.cannot_be_retried');

                return new RedirectResponse(
                    $this->router->generate('sylius_shop_homepage'),
                );
            }

            throw $exception;
        }

        $this->addFlashMessage(self::INFO_FLASH_TYPE, 'commerce_weavers_sylius_tpay.shop.retry_payment.previous_payment_cancelled');

        return new RedirectResponse(
            $this->router->generate('sylius_shop_order_show', ['tokenValue' => $orderToken]),
        );
    }

    private function findOrderOr404(string $orderToken): OrderInterface
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneByTokenValue($orderToken);

        if (null === $order) {
            throw new NotFoundHttpException(sprintf('Order with token "%s" does not exist.', $orderToken));
        }

        return $order;
    }

    private function addFlashMessage(string $type, string $message): void
    {
        /** @var Session $session */
        $session = $this->requestStack->getSession();

        $session->getFlashBag()->add($type, $message);
    }
}
