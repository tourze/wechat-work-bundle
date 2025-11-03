<?php

namespace WechatWorkBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use HttpClientBundle\HttpClientBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIpBundle\DoctrineIpBundle;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\DoctrineTrackBundle\DoctrineTrackBundle;
use Tourze\DoctrineUserBundle\DoctrineUserBundle;
use Tourze\WechatWorkContracts\AgentInterface;
use Tourze\WechatWorkContracts\CorpInterface;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;

class WechatWorkBundle extends Bundle implements BundleDependencyInterface
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new ResolveTargetEntityPass(CorpInterface::class, Corp::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );

        $container->addCompilerPass(
            new ResolveTargetEntityPass(AgentInterface::class, Agent::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );
    }

    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineIpBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            DoctrineTrackBundle::class => ['all' => true],
            DoctrineUserBundle::class => ['all' => true],
            HttpClientBundle::class => ['all' => true],
            EasyAdminMenuBundle::class => ['all' => true],
        ];
    }
}
