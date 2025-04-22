<?php

namespace WechatWorkBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 获取access_token
 *
 * @see https://developer.work.weixin.qq.com/document/path/91039#15074
 */
class GetTokenRequest extends ApiRequest
{
    /**
     * @var string 企业ID
     */
    private string $corpId;

    /**
     * @var string 应用的凭证密钥，注意应用需要是启用状态
     */
    private string $corpSecret;

    public function getRequestPath(): string
    {
        return '/cgi-bin/gettoken';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'corpid' => $this->getCorpId(),
                'corpsecret' => $this->getCorpSecret(),
            ],
        ];
    }

    public function getCorpId(): string
    {
        return $this->corpId;
    }

    public function setCorpId(string $corpId): void
    {
        $this->corpId = $corpId;
    }

    public function getCorpSecret(): string
    {
        return $this->corpSecret;
    }

    public function setCorpSecret(string $corpSecret): void
    {
        $this->corpSecret = $corpSecret;
    }
}
