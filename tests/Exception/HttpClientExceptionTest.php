<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Exception;

use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatWorkBundle\Exception\HttpClientException;

/**
 * @internal
 */
#[CoversClass(HttpClientException::class)]
class HttpClientExceptionTest extends AbstractExceptionTestCase
{
    public function testConstruct(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $message = 'Test message';
        $code = 500;

        $exception = new HttpClientException($request, $response, $message, $code);

        self::assertSame($request, $exception->getRequest());
        self::assertSame($response, $exception->getResponse());
        self::assertSame($message, $exception->getMessage());
        self::assertSame($code, $exception->getCode());
    }

    public function testGetRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $exception = new HttpClientException($request, $response);

        self::assertSame($request, $exception->getRequest());
    }

    public function testGetResponse(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $exception = new HttpClientException($request, $response);

        self::assertSame($response, $exception->getResponse());
    }
}
