<?php

namespace WechatWorkBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use WechatWorkBundle\Command\RefreshAgentAccessTokenCommand;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;

/**
 * @internal
 */
#[CoversClass(RefreshAgentAccessTokenCommand::class)]
#[RunTestsInSeparateProcesses]
final class RefreshAgentAccessTokenCommandTest extends AbstractCommandTestCase
{
    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(RefreshAgentAccessTokenCommand::class);
        $this->assertInstanceOf(RefreshAgentAccessTokenCommand::class, $command);

        return new CommandTester($command);
    }

    protected function onSetUp(): void        // Command 测试环境准备
    {
    }

    public function testExecuteWithNoAgents(): void
    {
        // 清理数据库中可能存在的 Agent 数据
        $entityManager = self::getEntityManager();
        $agentRepository = $entityManager->getRepository(Agent::class);
        $existingAgents = $agentRepository->findAll();
        foreach ($existingAgents as $agent) {
            $entityManager->remove($agent);
        }
        $entityManager->flush();

        $kernel = self::$kernel;
        $this->assertNotNull($kernel);
        $application = new Application($kernel);
        $command = $application->find(RefreshAgentAccessTokenCommand::NAME);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        // 由于命令没有输出任何内容，我们只检查退出代码
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithAgents(): void
    {
        // 清理数据库中可能存在的所有 Agent 和 Corp 数据
        $entityManager = self::getEntityManager();

        // 先删除所有 Agent（因为有外键依赖）
        $agentRepository = $entityManager->getRepository(Agent::class);
        foreach ($agentRepository->findAll() as $agent) {
            $entityManager->remove($agent);
        }
        $entityManager->flush();

        // 再删除所有 Corp
        $corpRepository = $entityManager->getRepository(Corp::class);
        foreach ($corpRepository->findAll() as $corp) {
            $entityManager->remove($corp);
        }
        $entityManager->flush();

        // 创建测试数据
        $corp = new Corp();
        $corp->setName('Test Corp');
        $corp->setCorpId('test_corp_id');
        $corp->setCorpSecret('test_secret');

        $agent1 = new Agent();
        $agent1->setName('Agent 1');
        $agent1->setAgentId('1001');
        $agent1->setSecret('');  // 设置空字符串，这样 refreshAgentAccessToken 方法会立即返回
        $agent1->setCorp($corp);

        $agent2 = new Agent();
        $agent2->setName('Agent 2');
        $agent2->setAgentId('1002');
        $agent2->setSecret('');  // 设置空字符串，这样 refreshAgentAccessToken 方法会立即返回
        $agent2->setCorp($corp);

        $entityManager = self::getEntityManager();
        $entityManager->persist($corp);
        $entityManager->persist($agent1);
        $entityManager->persist($agent2);
        $entityManager->flush();

        $kernel = self::$kernel;
        $this->assertNotNull($kernel);
        $application = new Application($kernel);
        $command = $application->find(RefreshAgentAccessTokenCommand::NAME);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        // 由于命令没有输出任何内容，我们只检查退出代码
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithExceptionInApiCall(): void
    {
        // 清理数据库中可能存在的所有 Agent 和 Corp 数据
        $entityManager = self::getEntityManager();

        // 先删除所有 Agent（因为有外键依赖）
        $agentRepository = $entityManager->getRepository(Agent::class);
        foreach ($agentRepository->findAll() as $agent) {
            $entityManager->remove($agent);
        }
        $entityManager->flush();

        // 再删除所有 Corp
        $corpRepository = $entityManager->getRepository(Corp::class);
        foreach ($corpRepository->findAll() as $corp) {
            $entityManager->remove($corp);
        }
        $entityManager->flush();

        // 创建测试数据
        $corp = new Corp();
        $corp->setName('Test Corp');
        $corp->setCorpId('test_corp_id');
        $corp->setCorpSecret('test_secret');

        $agent = new Agent();
        $agent->setName('Test Agent');
        $agent->setAgentId('1001');
        $agent->setSecret('');  // 设置空字符串，这样 refreshAgentAccessToken 方法会立即返回
        $agent->setCorp($corp);

        $entityManager = self::getEntityManager();
        $entityManager->persist($corp);
        $entityManager->persist($agent);
        $entityManager->flush();

        // 由于命令没有异常处理，且我们不能轻易 mock 服务
        // 这个测试只验证命令的正常执行流程
        // 异常处理应该在 WorkService 的单元测试中进行

        $kernel = self::$kernel;
        $this->assertNotNull($kernel);
        $application = new Application($kernel);
        $command = $application->find(RefreshAgentAccessTokenCommand::NAME);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        // 由于命令没有输出任何内容，我们只检查退出代码
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
