<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Request;

use HttpClientBundle\Request\ApiRequest;
use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatWorkBundle\Constant\ApiPath;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Request\GetAgentInfoRequest;

/**
 * @internal
 */
#[CoversClass(GetAgentInfoRequest::class)]
final class GetAgentInfoRequestIntegrationTest extends RequestTestCase
{
    public function testGetAgentInfoRequestWithAgent(): void
    {
        $corp = new Corp();
        $corp->setCorpId('wx_corp_id');
        $corp->setCorpSecret('test_corp_secret');

        $agent = new Agent();
        $agent->setAgentId('1000001');
        $agent->setSecret('agent_secret');
        $agent->setAccessToken('access_token');
        $agent->setCorp($corp);

        $request = new GetAgentInfoRequest();
        $request->setAgent($agent);

        $this->assertSame('GET', $request->getRequestMethod());
        $this->assertSame(ApiPath::GET_AGENT_INFO, $request->getRequestPath());

        $options = $request->getRequestOptions();
        $this->assertIsArray($options);
        $this->assertArrayHasKey('query', $options);
        $this->assertIsArray($options['query']);
        $this->assertArrayHasKey('agentid', $options['query']);
        $this->assertSame('1000001', $options['query']['agentid']);
    }

    public function testAgentAwareTraitIntegration(): void
    {
        $corp = new Corp();
        $corp->setCorpId('wx_corp_id');
        $corp->setCorpSecret('test_corp_secret');

        $agent = new Agent();
        $agent->setAgentId('1000001');
        $agent->setCorp($corp);

        $request = new GetAgentInfoRequest();

        // 测试agent设置
        $request->setAgent($agent);
        $this->assertSame($agent, $request->getAgent());
        $this->assertSame('1000001', $request->getAgent()->getAgentId());
    }

    public function testApiRequestInheritance(): void
    {
        $agentInfoRequest = new GetAgentInfoRequest();

        // 请求应该继承自ApiRequest
        $this->assertInstanceOf(ApiRequest::class, $agentInfoRequest);

        // 测试请求路径
        $this->assertSame(ApiPath::GET_AGENT_INFO, $agentInfoRequest->getRequestPath());
    }
}
