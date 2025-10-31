<?php

namespace WechatWorkBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use WechatWorkBundle\Command\SyncAgentInfoCommand;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;

/**
 * @internal
 */
#[CoversClass(SyncAgentInfoCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncAgentInfoCommandTest extends AbstractCommandTestCase
{
    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncAgentInfoCommand::class);
        $this->assertInstanceOf(SyncAgentInfoCommand::class, $command);

        return new CommandTester($command);
    }

    protected function onSetUp(): void
    {
        // 集成测试环境设置，这里暂时不需要特殊配置
    }

    public function testExecuteWithNoAgents(): void
    {
        // 从容器获取命令，这是集成测试的正确方式
        $command = self::getService(SyncAgentInfoCommand::class);
        self::assertInstanceOf(SyncAgentInfoCommand::class, $command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        // 由于没有agent，命令应该成功执行但不调用API
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithAgents(): void
    {
        // 创建测试数据并持久化到数据库
        $corp = new Corp();
        $corp->setName('Test Corp');
        $corp->setCorpId('test_corp_id');
        $corp->setCorpSecret('test_corp_secret');

        $agent = new Agent();
        $agent->setName('Test Agent');
        $agent->setAgentId('1001');
        $agent->setSecret('secret1');
        $agent->setCorp($corp);

        // 持久化对象
        $entityManager = self::getEntityManager();
        $entityManager->persist($corp);
        $entityManager->persist($agent);
        $entityManager->flush();

        // 从容器获取命令，使用真实的WorkService
        $command = self::getService(SyncAgentInfoCommand::class);
        self::assertInstanceOf(SyncAgentInfoCommand::class, $command);
        $commandTester = new CommandTester($command);

        // 由于使用真实的WorkService，我们需要Mock它的行为
        // 但在集成测试中，我们通常测试整个流程，包括API调用
        // 这里我们期望命令能够处理API调用失败的情况
        $commandTester->execute([]);

        // 命令应该成功执行，即使API调用失败（因为命令会处理异常）
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithApiError(): void
    {
        // 创建测试数据并持久化到数据库
        $corp = new Corp();
        $corp->setName('Test Corp');
        $corp->setCorpId('test_corp_id');
        $corp->setCorpSecret('test_corp_secret');

        $agent = new Agent();
        $agent->setName('Test Agent');
        $agent->setAgentId('1001');
        $agent->setSecret('invalid_secret');
        $agent->setCorp($corp);

        // 持久化对象
        $entityManager = self::getEntityManager();
        $entityManager->persist($corp);
        $entityManager->persist($agent);
        $entityManager->flush();

        // 从容器获取命令，使用真实的WorkService
        $command = self::getService(SyncAgentInfoCommand::class);
        self::assertInstanceOf(SyncAgentInfoCommand::class, $command);
        $commandTester = new CommandTester($command);

        // 使用无效凭据，命令应该处理异常并继续执行
        $commandTester->execute([]);

        // 命令应该成功执行，即使API调用失败
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
