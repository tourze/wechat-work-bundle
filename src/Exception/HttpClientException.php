<?php

declare(strict_types=1);

namespace WechatWorkBundle\Exception;

use HttpClientBundle\Request\RequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * HTTP客户端异常
 */
class HttpClientException extends \RuntimeException
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResponseInterface $response,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
