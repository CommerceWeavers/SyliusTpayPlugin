<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class TpayCardType extends AbstractType
{
    private const PREDICTED_MAX_CARD_VALIDITY_YEARS = 10;

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'number',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'commerce_weavers_sylius_tpay.shop.order_summary.card.number',
                    'required' => false,
                ],
            )
            ->add(
                'cvv',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'commerce_weavers_sylius_tpay.shop.order_summary.card.cvv',
                    'required' => false,
                ],
            )
            ->add(
                'expiration_date_month',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'label' => 'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.label',
                    'placeholder' => 'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month_placeholder',
                    'choices' => [
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.january' => '01',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.february' => '02',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.march' => '03',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.april' => '04',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.may' => '05',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.june' => '06',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.july' => '07',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.august' => '08',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.september' => '09',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.october' => '10',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.november' => '11',
                        'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.month.december' => '12',
                    ],
                    'required' => false,
                ],
            )
            ->add(
                'expiration_date_year',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'label' => 'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.year',
                    'placeholder' => 'commerce_weavers_sylius_tpay.shop.order_summary.card.expiration_date.year_placeholder',
                    'choices' => $this->getCardValidYearsRange(),
                    'required' => false,
                ],
            )
            ->add('card', HiddenType::class)
        ;
    }

    private function getCardValidYearsRange(): array
    {
        $result = [];
        $currentYear = (int) date('Y');

        foreach (range($currentYear, $currentYear + self::PREDICTED_MAX_CARD_VALIDITY_YEARS) as $year) {
            $result[$year] = $year;
        }

        return $result;
    }
}
