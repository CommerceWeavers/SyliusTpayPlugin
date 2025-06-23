<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\Bundle\CoreBundle\SyliusCoreBundle;

if (SyliusCoreBundle::VERSION_ID < 11300) {
    return;
}

return function(ContainerConfigurator $container): void {
    $container->extension('sylius_state_machine_abstraction', [
        'default_adapter' => '%env(STATE_MACHINE_DEFAULT_ADAPTER)%',
    ]);
};
