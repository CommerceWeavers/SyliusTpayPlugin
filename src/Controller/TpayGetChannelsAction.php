<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tpay\OpenApi\Api\TpayApi;
use Tpay\OpenApi\Utilities\TpayException;

final class TpayGetChannelsAction
{
    public function __construct(
        private readonly LocaleContextInterface $localeContext,
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function __invoke(Request $request): Response
    {
        $tpayApi = new TpayApi(
            (string) $request->headers->get('X-Client-Id'),
            (string) $request->headers->get('X-Client-Secret'),
            $request->query->getBoolean('productionMode', true),
        );

        $localeCode = $this->localeContext->getLocaleCode();

        try {
            $tpayResponse = $tpayApi->transactions()->getChannels();
        } catch (TpayException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => $this->translator->trans('commerce_weavers_sylius_tpay.admin.failed_connection_test', domain: 'flashes', locale: $localeCode),
            ], Response::HTTP_UNAUTHORIZED);
        }

        $channels = [];
        foreach ($tpayResponse['channels'] as $channel) {
            $channels[$channel['id']] = $channel['name'];
        }

        return new JsonResponse($channels, headers: [
            'Accept-Language' => $localeCode
        ]);
    }
}
