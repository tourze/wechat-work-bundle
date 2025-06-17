<?php

namespace WechatWorkBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;

class CorpTest extends TestCase
{
    public function testSetAndGetName(): void
    {
        $corp = new Corp();
        $name = 'Test Corp';
        
        $corp->setName($name);
        
        $this->assertSame($name, $corp->getName());
    }
    
    public function testSetAndGetCorpId(): void
    {
        $corp = new Corp();
        $corpId = 'wx12345678';
        
        $corp->setCorpId($corpId);
        
        $this->assertSame($corpId, $corp->getCorpId());
    }
    
    public function testSetAndGetFromProvider(): void
    {
        $corp = new Corp();
        
        $corp->setFromProvider(true);
        
        $this->assertTrue($corp->isFromProvider());
        
        $corp->setFromProvider(false);
        
        $this->assertFalse($corp->isFromProvider());
    }
    
    public function testAddAndRemoveAgent(): void
    {
        $corp = new Corp();
        $agent = new Agent();
        
        $corp->addAgent($agent);
        
        $this->assertCount(1, $corp->getAgents());
        $this->assertSame($corp, $agent->getCorp());
        
        $corp->removeAgent($agent);
        
        $this->assertCount(0, $corp->getAgents());
        $this->assertNull($agent->getCorp());
    }
    
    public function testToString(): void
    {
        $corp = new Corp();
        
        // 无ID时返回空字符串
        $this->assertSame('', $corp->__toString());
        
        // 使用反射设置ID和名称
        $reflectionClass = new \ReflectionClass(Corp::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($corp, 1);
        
        $corp->setName('Test Corp');
        
        $this->assertSame('Test Corp', $corp->__toString());
    }
    
    public function testTimestampFields(): void
    {
        $corp = new Corp();
        $now = new \DateTimeImmutable();
        
        $corp->setCreateTime($now);
        $this->assertSame($now, $corp->getCreateTime());
        
        $updateTime = new \DateTimeImmutable();
        $corp->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $corp->getUpdateTime());
    }
    
    public function testUserTrackingFields(): void
    {
        $corp = new Corp();
        $user = 'testuser';
        
        $corp->setCreatedBy($user);
        $this->assertSame($user, $corp->getCreatedBy());
        
        $corp->setUpdatedBy('updater');
        $this->assertSame('updater', $corp->getUpdatedBy());
    }
    
    public function testIpTrackingFields(): void
    {
        $corp = new Corp();
        $ip = '192.168.1.1';
        
        $corp->setCreatedFromIp($ip);
        $this->assertSame($ip, $corp->getCreatedFromIp());
        
        $corp->setUpdatedFromIp('10.0.0.1');
        $this->assertSame('10.0.0.1', $corp->getUpdatedFromIp());
    }
} 