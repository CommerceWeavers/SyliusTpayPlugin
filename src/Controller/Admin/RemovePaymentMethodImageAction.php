<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller\Admin;

use CommerceWeavers\SyliusTpayPlugin\Entity\PaymentMethodImage;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final readonly class RemovePaymentMethodImageAction
{
    public function __construct(
        private RepositoryInterface $paymentMethodImageRepository,
        private RouterInterface $router,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var string $paymentMethodId */
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

        $session = $request->getSession();
        $this->addFlashMessage($session, 'success', 'commerce_weavers_sylius_tpay.admin.payment_method.image_has_been_removed');

        return new RedirectResponse($this->router->generate('sylius_admin_payment_method_update', ['id' => $paymentMethodId]));
    }

    private function addFlashMessage(FlashBagAwareSessionInterface $session, string $type, string $message): void
    {
        $session->getFlashBag()->add($type, $message);
    }
}
