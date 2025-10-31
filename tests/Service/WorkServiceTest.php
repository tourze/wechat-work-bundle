<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Service;

use Carbon\CarbonImmutable;
use HttpClientBundle\Exception\HttpClientException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Service\WorkService;

/**
 * @internal
 */
#[CoversClass(WorkService::class)]
#[RunTestsInSeparateProcesses]
final class WorkServiceTest extends AbstractIntegrationTestCase
{
    private WorkService $workService;

    protected function onSetUp(): void
    {
        $this->workService = self::getService(WorkService::class);
    }

    public function testGetBaseUrl(): void
    {
        $this->assertSame('https://qyapi.weixin.qq.com', $this->workService->getBaseUrl());
    }

    public function testRefreshAgentAccessTokenWithEmptySecret(): void
    {
        $agent = new Agent();
        $corp = new Corp();
        $corp->setCorpSecret('test_corp_secret');
        $agent->setCorp($corp);

        // 空的secret
        $agent->setSecret('');

        $this->workService->refreshAgentAccessToken($agent);

        // 不会进行任何API调用
        $this->assertNull($agent->getAccessToken());
    }

    public function testRefreshAgentAccessTokenWithValidToken(): void
    {
        $agent = new Agent();
        $corp = new Corp();
        $corp->setCorpSecret('test_corp_secret');
        $agent->setCorp($corp);
        $agent->setSecret('test_secret');

        // 设置有效的token和过期时间
        $agent->setAccessToken('valid_token');
        $agent->setAccessTokenExpireTime(CarbonImmutable::now()->addHour()->toDateTimeImmutable());

        $this->workService->refreshAgentAccessToken($agent);

        // token不会改变
        $this->assertSame('valid_token', $agent->getAccessToken());
    }

    public function testRefreshAgentAccessTokenWithExpiredToken(): void
    {
        $agent = new Agent();
        $corp = new Corp();
        $corp->setCorpSecret('test_corp_secret');
        $corp->setCorpId('wx12345');
        $agent->setCorp($corp);
        $agent->setSecret('test_secret');
        $agent->setAccessToken('expired_token');
        $agent->setAccessTokenExpireTime(CarbonImmutable::now()->subHour()->toDateTimeImmutable());

        try {
            $this->workService->refreshAgentAccessToken($agent);
        } catch (HttpClientException $e) {
            // API调用会因为虚假凭据失败，这是预期的
        }

        // 过期token应该被清除
        $this->assertSame('', $agent->getAccessToken());
    }
}
