<?php

declare(strict_types=1);

namespace WechatWorkBundle\Service;

use HttpClientBundle\Request\RequestInterface;

/**
 * 企业微信服务接口
 */
interface WorkServiceInterface
{
    /**
     * 发起请求，并获得结果
     */
    public function request(RequestInterface $request): mixed;
}
