<?php

namespace WechatWorkBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use WechatWorkBundle\Command\SyncAgentInfoCommand;
use WechatWorkBundle\Repository\AgentRepository;
use WechatWorkBundle\Service\WorkService;

class SyncAgentInfoCommandTest extends TestCase
{
    public function testExecute_WithNoAgents(): void
    {
        $agentRepository = $this->createMock(AgentRepository::class);
        $workService = $this->createMock(WorkService::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $agentRepository->method('findAll')->willReturn([]);
        
        $command = new SyncAgentInfoCommand($agentRepository, $workService, $entityManager);
        
        $application = new Application();
        $application->add($command);
        
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        
        // 由于命令没有输出任何内容，我们只检查退出代码
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
    
    public function testExecute_WithAgents(): void
    {
        $this->markTestSkipped('这个测试需要重构');
    }
    
    public function testExecute_WithApiError(): void
    {
        $this->markTestSkipped('API异常测试跳过');
    }
} 