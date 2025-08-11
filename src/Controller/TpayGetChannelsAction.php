<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tpay\OpenApi\Api\TpayApi;
use Tpay\OpenApi\Utilities\Cache;
use Tpay\OpenApi\Utilities\TpayException;

final class TpayGetChannelsAction
{
    public function __construct(
        private readonly LocaleContextInterface $localeContext,
        private readonly Cache $cache,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $tpayApi = new TpayApi(
            $this->cache,
            (string) $request->headers->get('X-Client-Id'),
            (string) $request->headers->get('X-Client-Secret'),
            $request->query->getBoolean('productionMode', true),
        );

        $localeCode = $this->localeContext->getLocaleCode();

        try {
            $tpayResponse = $tpayApi->transactions()->getChannels();
        } catch (TpayException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED, ['Content-Language' => $localeCode]);
        }

        $channels = [];
        foreach ($tpayResponse['channels'] as $channel) {
            $channels[$channel['id']] = $channel['name'];
        }

        return new JsonResponse($channels, headers: [
            'Content-Language' => $localeCode,
        ]);
    }
}
