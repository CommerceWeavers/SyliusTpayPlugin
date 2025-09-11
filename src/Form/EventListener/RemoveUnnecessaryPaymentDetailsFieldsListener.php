<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use Symfony\Component\Form\FormEvent;

final class RemoveUnnecessaryPaymentDetailsFieldsListener
{
    public function __invoke(FormEvent $event): void
    {
        /** @var array{card?: string, blik_token?: string, tpay_channel_id?: string} $data */
        $data = $event->getData() ?? [];
        $form = $event->getForm();

        if (!isset($data['card'])) {
            $form->remove('card');
        }

        if (!isset($data['blik_token'])) {
            $form->remove('blik_token');
        }

        if (!isset($data['google_pay_token'])) {
            $form->remove('google_pay_token');
        }

        if (!isset($data['tpay_channel_id'])) {
            $form->remove('tpay_channel_id');
        }

        if (!isset($data['visa_mobile_phone_number'])) {
            $form->remove('visa_mobile_phone_number');
        }
    }
}
