<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\Form\FormEvent;

final class RemoveUnnecessaryPaymentDetailsFieldsListener
{
    public function __invoke(FormEvent $event): void
    {
        $payment = $event->getForm()->getParent()?->getData();

        if ($payment->getState() === OrderInterface::STATE_NEW) {
            return;
        }

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
