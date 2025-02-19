<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\Bundle\CoreBundle\EventListener\ImageUploadListener;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    /** new */

    $services->set('commerce_weavers_sylius_tpay.listener.payment_method.image_upload', ImageUploadListener::class)
        ->args([service('sylius.image_uploader')])
        ->tag('kernel.event_listener', ['event' => 'sylius.payment_method.pre_create', 'method' => 'uploadImage'])
        ->tag('kernel.event_listener', ['event' => 'sylius.payment_method.pre_update', 'method' => 'uploadImage'])
    ;
};
