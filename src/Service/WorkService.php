<?php

namespace WechatWorkBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Client\ClientTrait;
use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Request\AgentAware;
use WechatWorkBundle\Request\GetTokenRequest;
use WechatWorkBundle\Request\RawResponseInterface;
use Yiisoft\Json\Json;

/**
 * 企业微信服务发起
 */
#[Autoconfigure(public: true)]
class WorkService extends ApiClient
{
    use ClientTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getBaseUrl(): string
    {
        return 'https://qyapi.weixin.qq.com';
    }

    public function refreshAgentAccessToken(Agent $agent): void
    {
        $now = CarbonImmutable::now()->subMinutes(5);

        if (empty($agent->getSecret())) {
            return;
        }
        if ($agent->getAccessTokenExpireTime() === null) {
            $agent->setAccessTokenExpireTime(CarbonImmutable::now()->lastOfYear()->toDateTimeImmutable());
        }
        if ($agent->getAccessToken() !== null && $agent->getAccessToken() !== '' && $now->greaterThan($agent->getAccessTokenExpireTime())) {
            $agent->setAccessToken('');
        }

        if ($agent->getAccessToken() === null || $agent->getAccessToken() === '') {
            $request = new GetTokenRequest();
            $request->setCorpId($agent->getCorp()->getCorpId());
            $request->setCorpSecret($agent->getSecret());
            $tokenResponse = $this->request($request);

            if (!isset($tokenResponse['access_token'])) {
                $this->apiClientLogger?->error('获取企业微信应用AccessToken失败', [
                    'agent' => $agent,
                    'tokenResponse' => $tokenResponse,
                ]);
            } else {
                $agent->setAccessToken($tokenResponse['access_token']);
                $agent->setAccessTokenExpireTime(CarbonImmutable::now()->addSeconds($tokenResponse['expires_in'])->toDateTimeImmutable());
                $this->entityManager->persist($agent);
                $this->entityManager->flush();
            }
        }
    }

    protected function getRequestMethod(RequestInterface $request): string
    {
        $method = $request->getRequestMethod();

        return $method ?? 'POST';
    }

    protected function getRequestOptions(RequestInterface $request): ?array
    {
        $options = $request->getRequestOptions();
        if (!isset($options['query'])) {
            $options['query'] = [];
        }

        // 补充 AccessToken
        if (in_array(AgentAware::class, class_uses($request)) && !isset($options['query']['access_token'])) {
            /** @var RequestInterface&AgentAware $request */
            $agent = $request->getAgent();
            if ($agent) {
                $this->refreshAgentAccessToken($agent);
                $token = $agent->getAccessToken();
                $options['query']['access_token'] = $token;
            }
        }

        // 如果我们当前是在开发模式的话，默认加个调试参数，方便我们事后去排查接口问题
        // see https://developer.work.weixin.qq.com/document/path/90487#debug%E6%A8%A1%E5%BC%8F%E8%B0%83%E7%94%A8%E6%8E%A5%E5%8F%A3
        // NOTICE 注意: debug模式有使用频率限制，同一个api每分钟不能超过5次，所以在完成调试之后，请记得要去掉debug=1参数
        //        if ($_ENV['APP_ENV'] !== 'prod') {
        //            $options['query']['debug'] = 1;
        //        }

        return $options;
    }

    protected function formatResponse(RequestInterface $request, ResponseInterface $response): mixed
    {
        if ($request instanceof RawResponseInterface) {
            return $response->getContent();
        }
        $json = $response->getContent();
        $json = Json::decode($json);
        $errCode = $json['errcode'] ?? null;
        $errMsg = $json['errmsg'] ?? null;
        if (0 !== $errCode) {
            throw new HttpClientException($request, $response, $errMsg, $errCode);
        }

        return $json;
    }
}
