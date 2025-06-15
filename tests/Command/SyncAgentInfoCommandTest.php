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
        $agentRepository = $this->createMock(AgentRepository::class);
        $workService = $this->createMock(WorkService::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $agent = $this->createMock(\WechatWorkBundle\Entity\Agent::class);
        $agentRepository->method('findAll')->willReturn([$agent]);
        
        // 模拟API返回数据
        $apiResponse = [
            'square_logo_url' => 'http://example.com/logo.png',
            'description' => 'Test Agent',
            'allow_userinfos' => ['user1', 'user2'],
            'allow_partys' => [1, 2],
            'allow_tags' => [1, 2],
            'redirect_domain' => 'example.com',
            'report_location_flag' => 1,
            'isreportenter' => 1,
            'home_url' => 'http://example.com',
            'customized_publish_status' => 1,
        ];
        
        $workService->method('request')->willReturn($apiResponse);
        
        // 验证agent的setter方法被调用
        $agent->expects($this->once())->method('setSquareLogoUrl')->with('http://example.com/logo.png');
        $agent->expects($this->once())->method('setDescription')->with('Test Agent');
        $agent->expects($this->once())->method('setAllowUsers')->with(['user1', 'user2']);
        $agent->expects($this->once())->method('setAllowParties')->with([1, 2]);
        $agent->expects($this->once())->method('setAllowTags')->with([1, 2]);
        $agent->expects($this->once())->method('setRedirectDomain')->with('example.com');
        $agent->expects($this->once())->method('setReportLocationFlag')->with(true);
        $agent->expects($this->once())->method('setReportEnter')->with(true);
        $agent->expects($this->once())->method('setHomeUrl')->with('http://example.com');
        $agent->expects($this->once())->method('setCustomizedPublishStatus')->with(1);
        
        $entityManager->expects($this->once())->method('persist')->with($agent);
        $entityManager->expects($this->once())->method('flush');
        
        $command = new SyncAgentInfoCommand($agentRepository, $workService, $entityManager);
        
        $application = new Application();
        $application->add($command);
        
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
    
    public function testExecute_WithApiError(): void
    {
        $agentRepository = $this->createMock(AgentRepository::class);
        $workService = $this->createMock(WorkService::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $agent = $this->createMock(\WechatWorkBundle\Entity\Agent::class);
        $agentRepository->method('findAll')->willReturn([$agent]);
        
        // 模拟API调用抛出异常
        $workService->method('request')
            ->willThrowException(new \Exception('API调用失败'));
        
        $command = new SyncAgentInfoCommand($agentRepository, $workService, $entityManager);
        
        $application = new Application();
        $application->add($command);
        
        $commandTester = new CommandTester($command);
        
        // 由于命令没有异常处理，应该抛出异常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API调用失败');
        
        $commandTester->execute(['command' => $command->getName()]);
    }
} 