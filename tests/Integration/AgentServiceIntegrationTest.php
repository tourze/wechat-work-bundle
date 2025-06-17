<?php

namespace WechatWorkBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;

class AgentServiceIntegrationTest extends TestCase
{
    
    public function testAgentWithCorpRelationship(): void
    {
        $corp = new Corp();
        $corp->setName('测试企业');
        $corp->setCorpId('test_corp_id');

        $agent = new Agent();
        $agent->setName('测试应用');
        $agent->setAgentId('1000001');
        $agent->setSecret('test_secret');
        $agent->setCorp($corp);

        // 测试关系设置
        $this->assertSame($corp, $agent->getCorp());
        $this->assertSame('测试应用', $agent->getName());
        $this->assertSame('1000001', $agent->getAgentId());
        $this->assertSame('test_secret', $agent->getSecret());
    }
    
    public function testAgentToSelectItem(): void
    {
        $corp = new Corp();
        $corp->setName('测试企业');

        $agent = new Agent();
        $agent->setName('测试应用');
        $agent->setCorp($corp);

        // 使用反射设置ID
        $reflection = new \ReflectionClass(Agent::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($agent, 123);

        $selectItem = $agent->toSelectItem();

        $expected = [
            'label' => '测试企业 - 测试应用',
            'text' => '测试企业 - 测试应用',
            'value' => 123,
        ];

        $this->assertSame($expected, $selectItem);
    }
    
    public function testAgentPrePersistWithNullValues(): void
    {
        $agent = new Agent();

        // 测试在agentId和secret为null时不会抛出异常
        $agent->prePersist();
        $this->assertNull($agent->getAgentId());
        $this->assertNull($agent->getSecret());
    }
    
    public function testAgentPrePersistWithWhitespace(): void
    {
        $agent = new Agent();
        $agent->setAgentId('  1000001  ');
        $agent->setSecret('  test_secret  ');

        $agent->prePersist();

        $this->assertSame('1000001', $agent->getAgentId());
        $this->assertSame('test_secret', $agent->getSecret());
    }
    
    public function testAgentAccessTokenManagement(): void
    {
        $agent = new Agent();
        $expireTime = new \DateTime('+3600 seconds');

        $agent->setAccessToken('test_token');
        $agent->setAccessTokenExpireTime($expireTime);

        $this->assertSame('test_token', $agent->getAccessToken());
        $this->assertSame($expireTime, $agent->getAccessTokenExpireTime());
    }
    
    public function testAgentBooleanProperties(): void
    {
        $agent = new Agent();

        $agent->setReportLocationFlag(true);
        $agent->setReportEnter(false);

        $this->assertTrue($agent->isReportLocationFlag());
        $this->assertFalse($agent->isReportEnter());
    }
    
    public function testAgentArrayProperties(): void
    {
        $agent = new Agent();

        $users = ['user1', 'user2', 'user3'];
        $parties = [1, 2, 3];
        $tags = [100, 200];

        $agent->setAllowUsers($users);
        $agent->setAllowParties($parties);
        $agent->setAllowTags($tags);

        $this->assertSame($users, $agent->getAllowUsers());
        $this->assertSame($parties, $agent->getAllowParties());
        $this->assertSame($tags, $agent->getAllowTags());
    }
    
}