<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as Twig;

final class DisplayThankYouPageAction
{
    public function __construct(
        private readonly Twig $twig,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(Request $request, string $orderToken): Response
    {
        $order = $this->findOrderOr404($orderToken);

        return new Response($this->twig->render('@SyliusShop/order/thank_you.html.twig', [
            'order' => $order,
        ]));
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
}
