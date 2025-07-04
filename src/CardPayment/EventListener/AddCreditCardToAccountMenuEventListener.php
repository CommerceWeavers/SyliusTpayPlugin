<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\CardPayment\EventListener;

use CommerceWeavers\SyliusTpayPlugin\Routing;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AddCreditCardToAccountMenuEventListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu
            ->addChild('credit_cards', ['route' => Routing::SHOP_ACCOUNT_CREDIT_CARD_INDEX])
            ->setLabel('commerce_weavers_sylius_tpay.shop.credit_cards')
            ->setLabelAttribute('icon', 'tabler:credit-card')
        ;
    }
}
