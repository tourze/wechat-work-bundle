<?php

namespace WechatWorkBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Tourze\WechatWorkContracts\CorpInterface;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\WechatWorkBundle;

class WechatWorkBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        
        $container->expects(self::once())
            ->method('addCompilerPass')
            ->with(
                self::callback(function ($pass) {
                    return $pass instanceof ResolveTargetEntityPass;
                }),
                PassConfig::TYPE_BEFORE_OPTIMIZATION,
                1000
            );
        
        $bundle = new WechatWorkBundle();
        $bundle->build($container);
    }
    
    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new WechatWorkBundle();
        
        self::assertInstanceOf(WechatWorkBundle::class, $bundle);
    }
    
    public function testBuildAddsCorrectCompilerPass(): void
    {
        $containerBuilder = new ContainerBuilder();
        $bundle = new WechatWorkBundle();
        
        $bundle->build($containerBuilder);
        
        $passes = $containerBuilder->getCompilerPassConfig()->getBeforeOptimizationPasses();
        
        $resolveTargetEntityPass = null;
        foreach ($passes as $pass) {
            if ($pass instanceof ResolveTargetEntityPass) {
                $resolveTargetEntityPass = $pass;
                break;
            }
        }
        
        self::assertNotNull($resolveTargetEntityPass, 'ResolveTargetEntityPass should be added');
    }
}