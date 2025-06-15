<?php

namespace WechatWorkBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;

class CorpEntityIntegrationTest extends TestCase
{
    public function testCorpWithMultipleAgents(): void
    {
        $corp = new Corp();
        $corp->setName('测试企业');
        $corp->setCorpId('test_corp_id');
        
        $agent1 = new Agent();
        $agent1->setName('应用1');
        $agent1->setAgentId('1000001');
        $agent1->setCorp($corp);
        
        $agent2 = new Agent();
        $agent2->setName('应用2');
        $agent2->setAgentId('1000002');
        $agent2->setCorp($corp);
        
        // 测试双向关系
        $this->assertSame($corp, $agent1->getCorp());
        $this->assertSame($corp, $agent2->getCorp());
    }
    
    public function testCorpStringRepresentation(): void
    {
        $corp = new Corp();
        
        // 测试无ID时返回空字符串
        $this->assertSame('', $corp->__toString());
        
        // 使用反射设置ID
        $reflection = new \ReflectionClass(Corp::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($corp, 1);
        
        $corp->setName('测试企业');
        
        $this->assertSame('测试企业', $corp->__toString());
    }
    
    public function testCorpAddAndRemoveAgents(): void
    {
        $corp = new Corp();
        $corp->setName('测试企业');
        
        $agent1 = new Agent();
        $agent1->setName('应用1');
        
        $agent2 = new Agent();
        $agent2->setName('应用2');
        
        // 测试添加agents
        $corp->addAgent($agent1);
        $corp->addAgent($agent2);
        
        $this->assertCount(2, $corp->getAgents());
        $this->assertTrue($corp->getAgents()->contains($agent1));
        $this->assertTrue($corp->getAgents()->contains($agent2));
        $this->assertSame($corp, $agent1->getCorp());
        $this->assertSame($corp, $agent2->getCorp());
        
        // 测试移除agent
        $corp->removeAgent($agent1);
        $this->assertCount(1, $corp->getAgents());
        $this->assertFalse($corp->getAgents()->contains($agent1));
        $this->assertTrue($corp->getAgents()->contains($agent2));
    }
    
    public function testCorpPropertiesManagement(): void
    {
        $corp = new Corp();
        
        $corp->setName('测试企业');
        $corp->setCorpId('wx_corp_123');
        $corp->setFromProvider(true);
        
        $this->assertSame('测试企业', $corp->getName());
        $this->assertSame('wx_corp_123', $corp->getCorpId());
        $this->assertTrue($corp->isFromProvider());
    }
    
    public function testCorpUserTrackingProperties(): void
    {
        $corp = new Corp();
        $createTime = new \DateTime();
        $updateTime = new \DateTime();
        
        $corp->setCreatedBy('admin');
        $corp->setUpdatedBy('user');
        $corp->setCreatedFromIp('192.168.1.1');
        $corp->setUpdatedFromIp('192.168.1.2');
        $corp->setCreateTime($createTime);
        $corp->setUpdateTime($updateTime);
        
        $this->assertSame('admin', $corp->getCreatedBy());
        $this->assertSame('user', $corp->getUpdatedBy());
        $this->assertSame('192.168.1.1', $corp->getCreatedFromIp());
        $this->assertSame('192.168.1.2', $corp->getUpdatedFromIp());
        $this->assertSame($createTime, $corp->getCreateTime());
        $this->assertSame($updateTime, $corp->getUpdateTime());
    }
}