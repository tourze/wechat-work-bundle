<?php

namespace WechatWorkBundle\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Entity\AccessTokenAware;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;

class AgentTest extends TestCase
{
    private Agent $agent;
    
    protected function setUp(): void
    {
        $this->agent = new Agent();
    }
    
    public function testSetAndGetName(): void
    {
        $name = 'Test Agent';
        
        $this->agent->setName($name);
        
        $this->assertSame($name, $this->agent->getName());
    }
    
    public function testSetAndGetAgentId(): void
    {
        $agentId = '1000001';
        
        $this->agent->setAgentId($agentId);
        
        $this->assertSame($agentId, $this->agent->getAgentId());
    }
    
    public function testSetAndGetSecret(): void
    {
        $secret = 'test_secret_key';
        
        $this->agent->setSecret($secret);
        
        $this->assertSame($secret, $this->agent->getSecret());
    }
    
    public function testSetAndGetToken(): void
    {
        $token = 'test_token';
        
        $this->agent->setToken($token);
        
        $this->assertSame($token, $this->agent->getToken());
    }
    
    public function testSetAndGetEncodingAESKey(): void
    {
        $key = 'test_encoding_aes_key';
        
        $this->agent->setEncodingAESKey($key);
        
        $this->assertSame($key, $this->agent->getEncodingAESKey());
    }
    
    public function testSetAndGetAccessToken(): void
    {
        $token = 'access_token_string';
        
        $this->agent->setAccessToken($token);
        
        $this->assertSame($token, $this->agent->getAccessToken());
        $this->assertInstanceOf(AccessTokenAware::class, $this->agent);
    }
    
    public function testSetAndGetAccessTokenExpireTime(): void
    {
        $expireTime = new DateTime();
        
        $this->agent->setAccessTokenExpireTime($expireTime);
        
        $this->assertSame($expireTime, $this->agent->getAccessTokenExpireTime());
    }
    
    public function testSetAndGetCorp(): void
    {
        $corp = new Corp();
        
        $this->agent->setCorp($corp);
        
        $this->assertSame($corp, $this->agent->getCorp());
    }
    
    public function testPrePersist(): void
    {
        // 当Corp和AgentId为空时
        $this->agent->prePersist();
        // 不会抛出异常
        $this->assertTrue(true);
        
        // 设置Corp和AgentId
        $corp = new Corp();
        $this->agent->setCorp($corp);
        $this->agent->setAgentId('1000001');
        
        // 不会抛出异常
        $this->agent->prePersist();
        $this->assertTrue(true);
    }
    
    public function testToString(): void
    {
        // 无ID时返回空字符串
        $this->assertSame('', $this->agent->__toString());
        
        // 使用反射设置ID
        $reflectionClass = new \ReflectionClass(Agent::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->agent, 1);
        
        $this->agent->setName('Test Agent');
        $this->agent->setAgentId('1000001');
        
        $this->assertSame('Test Agent(1000001)', $this->agent->__toString());
    }
    
    public function testToSelectItem(): void
    {
        $this->agent->setName('Test Agent');
        $this->agent->setAgentId('1000001');
        
        // 设置Corp，因为toSelectItem方法需要使用Corp
        $corp = new Corp();
        $corp->setName('Test Corp');
        $this->agent->setCorp($corp);
        
        // 使用反射设置ID
        $reflectionClass = new \ReflectionClass(Agent::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->agent, 1);
        
        $expected = [
            'label' => 'Test Corp - Test Agent',
            'text' => 'Test Corp - Test Agent',
            'value' => 1,
        ];
        
        $this->assertSame($expected, $this->agent->toSelectItem());
    }
    
    public function testPrivateKeyProperties(): void
    {
        $content = 'private key content';
        $version = 'v1.0';
        
        $this->agent->setPrivateKeyContent($content);
        $this->agent->setPrivateKeyVersion($version);
        
        $this->assertSame($content, $this->agent->getPrivateKeyContent());
        $this->assertSame($version, $this->agent->getPrivateKeyVersion());
    }
    
    public function testWelcomeText(): void
    {
        $welcomeText = 'Welcome to my agent';
        
        $this->agent->setWelcomeText($welcomeText);
        
        $this->assertSame($welcomeText, $this->agent->getWelcomeText());
    }
    
    public function testSetAndGetSquareLogoUrl(): void
    {
        $url = 'http://example.com/logo.png';
        
        $this->agent->setSquareLogoUrl($url);
        
        $this->assertSame($url, $this->agent->getSquareLogoUrl());
    }
    
    public function testSetAndGetDescription(): void
    {
        $description = 'Test agent description';
        
        $this->agent->setDescription($description);
        
        $this->assertSame($description, $this->agent->getDescription());
    }
    
    public function testSetAndGetAllowUsers(): void
    {
        $users = ['user1', 'user2'];
        
        $this->agent->setAllowUsers($users);
        
        $this->assertSame($users, $this->agent->getAllowUsers());
    }
    
    public function testSetAndGetAllowParties(): void
    {
        $parties = [1, 2];
        
        $this->agent->setAllowParties($parties);
        
        $this->assertSame($parties, $this->agent->getAllowParties());
    }
    
    public function testSetAndGetAllowTags(): void
    {
        $tags = [1, 2];
        
        $this->agent->setAllowTags($tags);
        
        $this->assertSame($tags, $this->agent->getAllowTags());
    }
    
    public function testSetAndGetRedirectDomain(): void
    {
        $domain = 'example.com';
        
        $this->agent->setRedirectDomain($domain);
        
        $this->assertSame($domain, $this->agent->getRedirectDomain());
    }
    
    public function testSetAndGetReportLocationFlag(): void
    {
        $this->agent->setReportLocationFlag(true);
        
        $this->assertTrue($this->agent->isReportLocationFlag());
        
        $this->agent->setReportLocationFlag(false);
        
        $this->assertFalse($this->agent->isReportLocationFlag());
    }
    
    public function testSetAndGetReportEnter(): void
    {
        $this->agent->setReportEnter(true);
        
        $this->assertTrue($this->agent->isReportEnter());
        
        $this->agent->setReportEnter(false);
        
        $this->assertFalse($this->agent->isReportEnter());
    }
    
    public function testSetAndGetHomeUrl(): void
    {
        $url = 'http://example.com/home';
        
        $this->agent->setHomeUrl($url);
        
        $this->assertSame($url, $this->agent->getHomeUrl());
    }
    
    public function testSetAndGetCustomizedPublishStatus(): void
    {
        $status = 1;
        
        $this->agent->setCustomizedPublishStatus($status);
        
        $this->assertSame($status, $this->agent->getCustomizedPublishStatus());
    }
} 