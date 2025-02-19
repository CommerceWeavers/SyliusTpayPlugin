<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller\Admin;

use CommerceWeavers\SyliusTpayPlugin\Entity\PaymentMethodImage;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class RemovePaymentMethodImageAction
{
    public function __construct(
        private readonly RepositoryInterface $paymentMethodImageRepository,
        private readonly RouterInterface $router,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $paymentMethodId = $request->attributes->get('id', '');
        if (!$this->csrfTokenManager->isTokenValid(
            new CsrfToken($paymentMethodId, (string) $request->query->get('_csrf_token', '')),
        )) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Invalid csrf token.');
        }

        /** @var PaymentMethodImage|null $paymentMethodImage */
        $paymentMethodImage = $this->paymentMethodImageRepository->findOneBy(['owner' => $paymentMethodId]);
        if (null !== $paymentMethodImage) {
            $this->paymentMethodImageRepository->remove($paymentMethodImage);
        }

        return new RedirectResponse($this->router->generate('sylius_admin_payment_method_update', ['id' => $paymentMethodId]));
    }
}
