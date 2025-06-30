<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use WechatWorkBundle\DependencyInjection\WechatWorkExtension;

final class WechatWorkExtensionTest extends TestCase
{
    private WechatWorkExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->extension = new WechatWorkExtension();
        $this->container = new ContainerBuilder(new ParameterBag());
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);
        
        self::assertTrue($this->container->hasDefinition('WechatWorkBundle\Service\WorkService'));
        self::assertTrue($this->container->hasDefinition('WechatWorkBundle\Repository\AgentRepository'));
        self::assertTrue($this->container->hasDefinition('WechatWorkBundle\Repository\CorpRepository'));
    }

    public function testGetAlias(): void
    {
        self::assertSame('wechat_work', $this->extension->getAlias());
    }
}