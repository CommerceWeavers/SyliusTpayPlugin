<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller;

use CommerceWeavers\SyliusTpayPlugin\Repository\BlikAliasRepositoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\SignatureVerifierInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TpayNotificationAction
{
    public function __construct(
        private readonly SignatureVerifierInterface $signatureVerifier,
        private readonly BlikAliasRepositoryInterface $blikAliasRepository,
        private readonly ObjectManager $blikAliasManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var string $signature */
        $signature = $request->headers->get('x-jws-signature');
        if (!$this->signatureVerifier->verify($signature, $request->getContent())) {
            return new Response('FALSE - Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        /** @var array{event?: string, msg_value?: array{value?: string, expirationDate?: string}} $content */
        $content = json_decode($request->getContent(), true);

        $event = $content['event'] ?? throw new \InvalidArgumentException('The event is missing.');
        $msgValue = $content['msg_value'] ?? throw new \InvalidArgumentException('The msg_value is missing.');
        $aliasValue = $msgValue['value'] ?? throw new \InvalidArgumentException('The msg_value.value is missing.');
        $aliasExpirationDate = $msgValue['expirationDate'] ?? null;

        $blikAlias = $this->blikAliasRepository->findOneByValue($aliasValue);
        if ($blikAlias === null) {
            return new Response('FALSE - Alias not found', Response::HTTP_BAD_REQUEST);
        }

        match ($event) {
            'ALIAS_REGISTER' => $blikAlias->register(null === $aliasExpirationDate ? null : new \DateTimeImmutable($aliasExpirationDate)),
            'ALIAS_UNREGISTER', 'ALIAS_EXPIRED' => $blikAlias->unregister(),
            default => throw new \InvalidArgumentException('Unsupported event'),
        };

        $this->blikAliasManager->flush();

        return new Response('TRUE');
    }
}
