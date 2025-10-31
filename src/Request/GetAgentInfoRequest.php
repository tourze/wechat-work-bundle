<?php

namespace WechatWorkBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use WechatWorkBundle\Constant\ApiPath;

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
        return ApiPath::GET_AGENT_INFO;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestOptions(): ?array
    {
        if (null === $this->getAgent()) {
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
