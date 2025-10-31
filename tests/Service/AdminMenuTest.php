<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Service;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatWorkBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testInvokeCreatesThirdPartyIntegrationMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = $factory->createItem('root');

        ($this->adminMenu)($rootMenu);

        // 验证第三方集成菜单已创建
        $integrationMenu = $rootMenu->getChild('第三方集成');
        $this->assertNotNull($integrationMenu);

        // 验证企业微信子菜单已创建
        $wechatMenu = $integrationMenu->getChild('企业微信');
        $this->assertNotNull($wechatMenu);
        $this->assertSame('fab fa-weixin', $wechatMenu->getAttribute('icon'));

        // 验证企业管理子菜单存在并配置正确
        $corpMenu = $wechatMenu->getChild('企业管理');
        $this->assertNotNull($corpMenu);
        $corpUri = $corpMenu->getUri();
        $this->assertIsString($corpUri);
        $this->assertStringContainsString('WechatWorkBundle%5CEntity%5CCorp', $corpUri);
        $this->assertSame('fas fa-building', $corpMenu->getAttribute('icon'));

        // 验证应用管理子菜单存在并配置正确
        $agentMenu = $wechatMenu->getChild('应用管理');
        $this->assertNotNull($agentMenu);
        $agentUri = $agentMenu->getUri();
        $this->assertIsString($agentUri);
        $this->assertStringContainsString('WechatWorkBundle%5CEntity%5CAgent', $agentUri);
        $this->assertSame('fas fa-mobile-alt', $agentMenu->getAttribute('icon'));
    }

    public function testInvokeWithExistingThirdPartyIntegrationMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = $factory->createItem('root');
        $rootMenu->addChild('第三方集成');

        ($this->adminMenu)($rootMenu);

        $integrationMenu = $rootMenu->getChild('第三方集成');
        $this->assertNotNull($integrationMenu);

        // 验证企业微信菜单已添加到现有第三方集成菜单中
        $wechatMenu = $integrationMenu->getChild('企业微信');
        $this->assertNotNull($wechatMenu);
    }

    public function testInvokeWithExistingWechatMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = $factory->createItem('root');
        $integrationMenu = $rootMenu->addChild('第三方集成');
        $integrationMenu->addChild('企业微信');

        ($this->adminMenu)($rootMenu);

        $wechatMenu = $integrationMenu->getChild('企业微信');
        $this->assertNotNull($wechatMenu);

        // 验证子菜单已正确添加
        $this->assertNotNull($wechatMenu->getChild('企业管理'));
        $this->assertNotNull($wechatMenu->getChild('应用管理'));
    }

    public function testInvokeHandlesNullThirdPartyIntegrationMenu(): void
    {
        $rootItem = $this->createMock(MenuItem::class);
        $rootItem->method('getChild')
            ->with('第三方集成')
            ->willReturn(null)
        ;

        $rootItem->expects($this->once())
            ->method('addChild')
            ->with('第三方集成')
            ->willReturn($rootItem)
        ;

        // 测试当 getChild 返回 null 时，方法能正常处理而不抛出异常
        // 由于mock会验证expectations，我们需要执行并验证addChild被调用
        ($this->adminMenu)($rootItem);

        // 验证方法执行成功且mock的expectations被满足
        // PHPUnit会自动验证mock的expectations，如果addChild没有被正确调用，测试会失败
        $this->assertTrue(true, 'Method executed without throwing exception and mock expectations were met');
    }
}
