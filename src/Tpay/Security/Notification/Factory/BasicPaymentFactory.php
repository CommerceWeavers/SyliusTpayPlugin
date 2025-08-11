<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Tpay\OpenApi\Model\Fields\Field;
use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;
use Tpay\OpenApi\Utilities\Util;

final class BasicPaymentFactory implements BasicPaymentFactoryInterface
{
    public function createFromArray(array $data): BasicPayment
    {
        $paymentData = new BasicPayment();

        foreach ($data as $key => $value) {
            $this->setPaymentData($paymentData, $key, $value);
        }

        return $paymentData;
    }

    private function setPaymentData(BasicPayment $paymentData, string $key, mixed $value): void
    {
        if (!is_string($value)) {
            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (property_exists($paymentData, $key)) {
            /** @var Field $field */
            $field = $propertyAccessor->getValue($paymentData, $key);

            $propertyAccessor->setValue($paymentData, $key, Util::cast($value, $field->getType()));
        }
    }
}
