<?php

namespace WechatWorkBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Request\GetAgentInfoRequest;
use WechatWorkBundle\Request\GetTokenRequest;

class RequestIntegrationTest extends TestCase
{
    public function testGetTokenRequestProperties(): void
    {
        $request = new GetTokenRequest();
        
        $request->setCorpId('wx_corp_id');
        $request->setCorpSecret('corp_secret');
        
        $this->assertSame('wx_corp_id', $request->getCorpId());
        $this->assertSame('corp_secret', $request->getCorpSecret());
        $this->assertSame('/cgi-bin/gettoken', $request->getRequestPath());
        
        $options = $request->getRequestOptions();
        $this->assertArrayHasKey('query', $options);
        $this->assertSame('wx_corp_id', $options['query']['corpid']);
        $this->assertSame('corp_secret', $options['query']['corpsecret']);
    }
    
    public function testGetAgentInfoRequestWithAgent(): void
    {
        $corp = new Corp();
        $corp->setCorpId('wx_corp_id');
        
        $agent = new Agent();
        $agent->setAgentId('1000001');
        $agent->setSecret('agent_secret');
        $agent->setAccessToken('access_token');
        $agent->setCorp($corp);
        
        $request = new GetAgentInfoRequest();
        $request->setAgent($agent);
        
        $this->assertSame('GET', $request->getRequestMethod());
        $this->assertSame('/cgi-bin/agent/get', $request->getRequestPath());
        
        $options = $request->getRequestOptions();
        $this->assertArrayHasKey('query', $options);
        $this->assertSame('1000001', $options['query']['agentid']);
    }
    
    public function testAgentAwareTraitIntegration(): void
    {
        $corp = new Corp();
        $corp->setCorpId('wx_corp_id');
        
        $agent = new Agent();
        $agent->setAgentId('1000001');
        $agent->setCorp($corp);
        
        $request = new GetAgentInfoRequest();
        
        // 测试agent设置
        $request->setAgent($agent);
        $this->assertSame($agent, $request->getAgent());
        $this->assertSame('1000001', $request->getAgent()->getAgentId());
    }
    
    public function testGetTokenRequestDefaultBehavior(): void
    {
        $request = new GetTokenRequest();
        
        // 在没有设置属性时，getRequestOptions可能会抛出异常或返回错误的数据
        // 但是getRequestPath应该总是返回正确的路径
        $this->assertSame('/cgi-bin/gettoken', $request->getRequestPath());
    }
    
    public function testApiRequestInheritance(): void
    {
        $tokenRequest = new GetTokenRequest();
        $agentInfoRequest = new GetAgentInfoRequest();
        
        // 两个请求都应该继承自ApiRequest
        $this->assertInstanceOf(\HttpClientBundle\Request\ApiRequest::class, $tokenRequest);
        $this->assertInstanceOf(\HttpClientBundle\Request\ApiRequest::class, $agentInfoRequest);
        
        // 测试请求路径
        $this->assertSame('/cgi-bin/gettoken', $tokenRequest->getRequestPath());
        $this->assertSame('/cgi-bin/agent/get', $agentInfoRequest->getRequestPath());
    }
}