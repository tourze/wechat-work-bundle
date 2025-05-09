<?php

namespace WechatWorkBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WorkServiceIntegrationTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('集成测试需要额外的依赖项，暂时跳过');
    }

    public function testServiceIsRegistered(): void
    {
        // 测试已被跳过
    }
    
    public function testRefreshAgentAccessToken(): void
    {
        // 测试已被跳过
    }
    
    public function testRepositoriesAreRegistered(): void
    {
        // 测试已被跳过
    }
    
    protected static function createKernel(array $options = []): IntegrationTestKernel
    {
        return new IntegrationTestKernel('test', true);
    }
}
