<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use Symfony\Component\Form\FormEvent;

final class RemoveUnnecessaryPaymentDetailsFieldsListener
{
    public function __invoke(FormEvent $event): void
    {
        /** @var array{card?: string, blik_token?: string, pay_by_link_channel_id?: string} $data */
        $data = $event->getData() ?? [];
        $form = $event->getForm();

        if (!isset($data['card'])) {
            $form->remove('card');
        }

        if (!isset($data['blik_token'])) {
            $form->remove('blik_token');
        }

        if (!isset($data['pay_by_link_channel_id'])) {
            $form->remove('pay_by_link_channel_id');
        }
    }
}
