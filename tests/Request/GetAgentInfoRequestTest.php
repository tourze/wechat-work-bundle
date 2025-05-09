<?php

namespace WechatWorkBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Request\GetAgentInfoRequest;

class GetAgentInfoRequestTest extends TestCase
{
    private GetAgentInfoRequest $request;
    
    protected function setUp(): void
    {
        $this->request = new GetAgentInfoRequest();
    }
    
    public function testGetRequestPath(): void
    {
        $this->assertSame('/cgi-bin/agent/get', $this->request->getRequestPath());
    }
    
    public function testGetRequestMethod(): void
    {
        $this->assertSame('GET', $this->request->getRequestMethod());
    }
    
    public function testGetRequestOptions(): void
    {
        $agentId = '1000001';
        
        $agent = new Agent();
        $corp = new Corp();
        $agent->setCorp($corp);
        $agent->setAgentId($agentId);
        
        $this->request->setAgent($agent);
        
        $expected = [
            'query' => [
                'agentid' => $agentId,
            ],
        ];
        
        $this->assertEquals($expected, $this->request->getRequestOptions());
    }
    
    public function testAgentAwareTrait(): void
    {
        $agent = new Agent();
        
        $this->request->setAgent($agent);
        
        $this->assertSame($agent, $this->request->getAgent());
    }
    
    public function testRequestWithEmptyAgent(): void
    {
        $this->expectException(\Error::class);
        $this->request->getRequestOptions();
    }
} 