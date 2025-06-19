<?php

namespace WechatWorkBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Tourze\WechatWorkContracts\CorpInterface;
use WechatWorkBundle\Entity\Corp;

class WechatWorkBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new ResolveTargetEntityPass(CorpInterface::class, Corp::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );
    }
}
