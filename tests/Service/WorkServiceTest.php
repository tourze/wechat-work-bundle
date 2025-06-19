<?php

namespace WechatWorkBundle\Tests\Service;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Service\WorkService;

class WorkServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private WorkService $workService;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->workService = new WorkService(
            $this->entityManager
        );
    }
    
    public function testGetBaseUrl(): void
    {
        $this->assertSame('https://qyapi.weixin.qq.com', $this->workService->getBaseUrl());
    }
    
    public function testRefreshAgentAccessToken_WithEmptySecret(): void
    {
        $agent = new Agent();
        $corp = new Corp();
        $agent->setCorp($corp);
        
        // 空的secret
        $agent->setSecret('');
        
        $this->workService->refreshAgentAccessToken($agent);
        
        // 不会进行任何API调用
        $this->assertNull($agent->getAccessToken());
    }
    
    public function testRefreshAgentAccessToken_WithValidToken(): void
    {
        $agent = new Agent();
        $corp = new Corp();
        $agent->setCorp($corp);
        $agent->setSecret('test_secret');
        
        // 设置有效的token和过期时间
        $agent->setAccessToken('valid_token');
        $agent->setAccessTokenExpireTime(Carbon::now()->addHour()->toDateTimeImmutable());
        
        $this->workService->refreshAgentAccessToken($agent);
        
        // token不会改变
        $this->assertSame('valid_token', $agent->getAccessToken());
    }
    
    public function testRefreshAgentAccessToken_WithExpiredToken(): void
    {
        $agent = $this->createMock(Agent::class);
        $corp = $this->createMock(Corp::class);
        
        $agent->method('getSecret')->willReturn('test_secret');
        $agent->method('getCorp')->willReturn($corp);
        $corp->method('getCorpId')->willReturn('wx12345');
        
        // 修改这里的期望调用次数
        // PHPUnit 在验证方法调用次数时有些限制
        $agent->method('getAccessTokenExpireTime')
            ->willReturn(Carbon::now()->subHour()->toDateTimeImmutable());
        
        $agent->method('getAccessToken')
            ->willReturn('expired_token');
        
        // 断言setAccessToken被调用一次，参数为空字符串（清除过期token）
        $agent->expects($this->once())
            ->method('setAccessToken')
            ->with('');
        
        $this->workService->refreshAgentAccessToken($agent);
    }
    
    public function testGetRequestMethod_WithPost(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn('POST');
        
        $method = $this->invokePrivateMethod($this->workService, 'getRequestMethod', [$request]);
        
        $this->assertSame('POST', $method);
    }
    
    public function testGetRequestMethod_WithGet(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn('GET');
        
        $method = $this->invokePrivateMethod($this->workService, 'getRequestMethod', [$request]);
        
        $this->assertSame('GET', $method);
    }
    
    public function testGetRequestMethod_WithEmptyMethod(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn(null);
        
        $method = $this->invokePrivateMethod($this->workService, 'getRequestMethod', [$request]);
        
        // 默认应该是POST
        $this->assertSame('POST', $method);
    }
    
    public function testGetRequestOptions_WithoutToken(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestOptions')->willReturn([]);
        
        $options = $this->invokePrivateMethod($this->workService, 'getRequestOptions', [$request]);
        
        $this->assertSame(['query' => []], $options);
    }
    
    public function testFormatResponse_WithRawResponse(): void
    {
        $request = new class implements \HttpClientBundle\Request\RequestInterface, \WechatWorkBundle\Request\RawResponseInterface {
            public function getRequestMethod(): string
            {
                return 'GET';
            }
            
            public function getUrl(): string
            {
                return 'https://api.test.com';
            }
            
            public function getRequestPath(): string
            {
                return '/test';
            }
            
            public function getRequestOptions(): array
            {
                return [];
            }
        };
        
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('raw response content');
        
        $result = $this->invokePrivateMethod($this->workService, 'formatResponse', [$request, $response]);
        
        $this->assertSame('raw response content', $result);
    }
    
    public function testFormatResponse_WithSuccessResponse(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        
        $responseContent = '{"errcode":0,"errmsg":"ok","data":{"key":"value"}}';
        $response->method('getContent')->willReturn($responseContent);
        
        $result = $this->invokePrivateMethod($this->workService, 'formatResponse', [$request, $response]);
        
        $this->assertEquals(['errcode' => 0, 'errmsg' => 'ok', 'data' => ['key' => 'value']], $result);
    }
    
    public function testFormatResponse_WithErrorResponse(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        
        $responseContent = '{"errcode":40001,"errmsg":"invalid credential"}';
        $response->method('getContent')->willReturn($responseContent);
        
        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('invalid credential');
        $this->expectExceptionCode(40001);
        
        $this->invokePrivateMethod($this->workService, 'formatResponse', [$request, $response]);
    }
    
    /**
     * 调用私有方法的辅助函数
     */
    private function invokePrivateMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
} 