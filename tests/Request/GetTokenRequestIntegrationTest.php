<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Request;

use HttpClientBundle\Request\ApiRequest;
use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatWorkBundle\Constant\ApiPath;
use WechatWorkBundle\Request\GetTokenRequest;

/**
 * @internal
 */
#[CoversClass(GetTokenRequest::class)]
final class GetTokenRequestIntegrationTest extends RequestTestCase
{
    public function testGetTokenRequestProperties(): void
    {
        $request = new GetTokenRequest();

        $request->setCorpId('wx_corp_id');
        $request->setCorpSecret('corp_secret');

        $this->assertSame('wx_corp_id', $request->getCorpId());
        $this->assertSame('corp_secret', $request->getCorpSecret());
        $this->assertSame(ApiPath::GET_TOKEN, $request->getRequestPath());

        $options = $request->getRequestOptions();
        $this->assertIsArray($options);
        $this->assertArrayHasKey('query', $options);
        $this->assertIsArray($options['query']);
        $this->assertArrayHasKey('corpid', $options['query']);
        $this->assertArrayHasKey('corpsecret', $options['query']);
        $this->assertSame('wx_corp_id', $options['query']['corpid']);
        $this->assertSame('corp_secret', $options['query']['corpsecret']);
    }

    public function testGetTokenRequestDefaultBehavior(): void
    {
        $request = new GetTokenRequest();

        // 在没有设置属性时，getRequestOptions可能会抛出异常或返回错误的数据
        // 但是getRequestPath应该总是返回正确的路径
        $this->assertSame(ApiPath::GET_TOKEN, $request->getRequestPath());
    }

    public function testApiRequestInheritance(): void
    {
        $tokenRequest = new GetTokenRequest();

        // 请求应该继承自ApiRequest
        $this->assertInstanceOf(ApiRequest::class, $tokenRequest);

        // 测试请求路径
        $this->assertSame(ApiPath::GET_TOKEN, $tokenRequest->getRequestPath());
    }
}
