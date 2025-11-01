<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatWorkBundle\Constant\ApiPath;
use WechatWorkBundle\Request\GetTokenRequest;

/**
 * @internal
 */
#[CoversClass(GetTokenRequest::class)]
final class GetTokenRequestTest extends RequestTestCase
{
    private GetTokenRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new GetTokenRequest();
    }

    public function testGetRequestPath(): void
    {
        $this->assertSame(ApiPath::GET_TOKEN, $this->request->getRequestPath());
    }

    public function testGetRequestOptions(): void
    {
        $corpId = 'wx12345';
        $corpSecret = 'secret12345';

        $this->request->setCorpId($corpId);
        $this->request->setCorpSecret($corpSecret);

        $expected = [
            'query' => [
                'corpid' => $corpId,
                'corpsecret' => $corpSecret,
            ],
        ];

        $this->assertEquals($expected, $this->request->getRequestOptions());
    }

    public function testSetAndGetCorpId(): void
    {
        $corpId = 'wx12345';

        $this->request->setCorpId($corpId);

        $this->assertSame($corpId, $this->request->getCorpId());
    }

    public function testSetAndGetCorpSecret(): void
    {
        $corpSecret = 'secret12345';

        $this->request->setCorpSecret($corpSecret);

        $this->assertSame($corpSecret, $this->request->getCorpSecret());
    }

    public function testRequestWithUninitialized(): void
    {
        // 在PHP 8中，访问未初始化的属性会抛出异常
        $this->expectException(\Error::class);
        $this->request->getRequestOptions();
    }
}
