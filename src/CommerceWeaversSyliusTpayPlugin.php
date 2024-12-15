<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin;

use CommerceWeavers\SyliusTpayPlugin\DependencyInjection\CompilerPass\AddSupportedRefundPaymentMethodPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class CommerceWeaversSyliusTpayPlugin extends Bundle
{
    public const VERSION = '2.0.2';

    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddSupportedRefundPaymentMethodPass());
    }

    public function getPath(): string
    {
        if (!isset($this->path)) {
            $reflected = new \ReflectionObject($this);
            $this->path = \dirname($reflected->getFileName(), 2);
        }

        return $this->path;
    }
}
