<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tpay\OpenApi\Api\TpayApi;
use Tpay\OpenApi\Utilities\TpayException;

final class TpayGetChannelsAction
{
    public function __invoke(Request $request): Response
    {
        $tpayApi = new TpayApi(
            (string) $request->headers->get('X-Client-Id'),
            (string) $request->headers->get('X-Client-Secret'),
            $request->query->getBoolean('productionMode', true),
        );

        try {
            $tpayResponse = $tpayApi->transactions()->getChannels();
        } catch (TpayException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        $channels = [];
        foreach ($tpayResponse['channels'] as $channel) {
            $channels[$channel['id']] = $channel['name'];
        }

        return new JsonResponse($channels);
    }
}
