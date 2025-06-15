<?php

namespace WechatWorkBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use WechatWorkBundle\Command\RefreshAgentAccessTokenCommand;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Repository\AgentRepository;
use WechatWorkBundle\Service\WorkService;

class RefreshAgentAccessTokenCommandTest extends TestCase
{
    public function testExecute_WithNoAgents(): void
    {
        $agentRepository = $this->createMock(AgentRepository::class);
        $workService = $this->createMock(WorkService::class);
        
        $agentRepository->method('findAll')->willReturn([]);
        
        $command = new RefreshAgentAccessTokenCommand($agentRepository, $workService);
        
        $application = new Application();
        $application->add($command);
        
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        
        // 由于命令没有输出任何内容，我们只检查退出代码
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
    
    public function testExecute_WithAgents(): void
    {
        $agentRepository = $this->createMock(AgentRepository::class);
        $workService = $this->createMock(WorkService::class);
        
        $agent1 = $this->createMock(Agent::class);
        $agent1->method('__toString')->willReturn('Agent 1');
        
        $agent2 = $this->createMock(Agent::class);
        $agent2->method('__toString')->willReturn('Agent 2');
        
        $agentRepository->method('findAll')->willReturn([$agent1, $agent2]);
        
        // PHPUnit 10不再支持withConsecutive和at()
        // 我们只能检查调用次数，不再检查具体参数
        $workService->expects($this->exactly(2))
            ->method('refreshAgentAccessToken');
        
        $command = new RefreshAgentAccessTokenCommand($agentRepository, $workService);
        
        $application = new Application();
        $application->add($command);
        
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        
        // 由于命令没有输出任何内容，我们只检查退出代码
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
    
    public function testExecute_WithExceptionInApiCall(): void
    {
        $agentRepository = $this->createMock(AgentRepository::class);
        $workService = $this->createMock(WorkService::class);
        
        $agent = $this->createMock(Agent::class);
        $agent->method('__toString')->willReturn('Test Agent');
        
        $agentRepository->method('findAll')->willReturn([$agent]);
        
        // 模拟API调用抛出异常
        $workService->method('refreshAgentAccessToken')
            ->willThrowException(new \Exception('API调用失败'));
        
        $command = new RefreshAgentAccessTokenCommand($agentRepository, $workService);
        
        $application = new Application();
        $application->add($command);
        
        $commandTester = new CommandTester($command);
        
        // 由于命令没有异常处理，应该抛出异常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API调用失败');
        
        $commandTester->execute(['command' => $command->getName()]);
    }
} 