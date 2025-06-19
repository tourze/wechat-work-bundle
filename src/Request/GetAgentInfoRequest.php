<?php

namespace WechatWorkBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 获取指定的应用详情
 *
 * @see https://developer.work.weixin.qq.com/document/path/96448
 */
class GetAgentInfoRequest extends ApiRequest
{
    use AgentAware;

    public function getRequestPath(): string
    {
        return '/cgi-bin/agent/get';
    }

    public function getRequestOptions(): ?array
    {
        if ($this->getAgent() === null) {
            return [];
        }
        
        return [
            'query' => [
                'agentid' => $this->getAgent()->getAgentId(),
            ],
        ];
    }

    public function getRequestMethod(): ?string
    {
        return 'GET';
    }
}
