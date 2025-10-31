<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatWorkBundle\Constant\ApiPath;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Request\GetAgentInfoRequest;

/**
 * @internal
 */
#[CoversClass(GetAgentInfoRequest::class)]
final class GetAgentInfoRequestTest extends RequestTestCase
{
    private GetAgentInfoRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new GetAgentInfoRequest();
    }

    public function testGetRequestPath(): void
    {
        $this->assertSame(ApiPath::GET_AGENT_INFO, $this->request->getRequestPath());
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
        $corp->setCorpSecret('test_corp_secret');
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
        // 当没有设置agent时，应该返回空数组
        $this->assertEquals([], $this->request->getRequestOptions());
    }
}
