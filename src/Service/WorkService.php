<?php

namespace WechatWorkBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Exception\GeneralHttpClientException;
use HttpClientBundle\Request\RequestInterface;
use HttpClientBundle\Service\SmartHttpClient;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Exception\HttpClientException;
use WechatWorkBundle\Request\AgentAware;
use WechatWorkBundle\Request\GetTokenRequest;
use WechatWorkBundle\Request\RawResponseInterface;
use Yiisoft\Json\Json;

/**
 * 企业微信服务发起
 *
 * @method void asyncRequest(\HttpClientBundle\Request\ApiRequest $request) 发起异步请求，不关心响应结果
 */
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'wechat_work')]
class WorkService extends ApiClient implements WorkServiceInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly SmartHttpClient $httpClient,
        private readonly LockFactory $lockFactory,
        private readonly CacheInterface $cache,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AsyncInsertService $asyncInsertService,
    ) {
    }

    protected function getLockFactory(): LockFactory
    {
        return $this->lockFactory;
    }

    protected function getHttpClient(): SmartHttpClient
    {
        return $this->httpClient;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * 优先使用Request中定义的地址
     */
    protected function getRequestUrl(RequestInterface $request): string
    {
        $path = ltrim($request->getRequestPath(), '/');
        if (str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'http://')) {
            return $path;
        }

        $domain = trim($this->getBaseUrl());
        if ('' === $domain) {
            throw new \RuntimeException(self::class . '缺少getBaseUrl的定义');
        }

        return "{$domain}/{$path}";
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    protected function getAsyncInsertService(): AsyncInsertService
    {
        return $this->asyncInsertService;
    }

    public function getBaseUrl(): string
    {
        return 'https://qyapi.weixin.qq.com';
    }

    public function refreshAgentAccessToken(Agent $agent): void
    {
        $secret = $agent->getSecret();
        if (null === $secret || '' === $secret) {
            return;
        }

        $this->ensureAccessTokenExpireTime($agent);
        $this->clearExpiredAccessToken($agent);

        if ($this->needsNewAccessToken($agent)) {
            $this->fetchAndSetAccessToken($agent);
        }
    }

    private function ensureAccessTokenExpireTime(Agent $agent): void
    {
        if (null === $agent->getAccessTokenExpireTime()) {
            $agent->setAccessTokenExpireTime(CarbonImmutable::now()->lastOfYear()->toDateTimeImmutable());
        }
    }

    private function clearExpiredAccessToken(Agent $agent): void
    {
        $now = CarbonImmutable::now()->subMinutes(5);
        $expireTime = $agent->getAccessTokenExpireTime();

        if ($this->hasValidToken($agent) && null !== $expireTime && $now->greaterThan($expireTime)) {
            $agent->setAccessToken('');
        }
    }

    private function hasValidToken(Agent $agent): bool
    {
        return null !== $agent->getAccessToken() && '' !== $agent->getAccessToken();
    }

    private function needsNewAccessToken(Agent $agent): bool
    {
        return !$this->hasValidToken($agent);
    }

    private function fetchAndSetAccessToken(Agent $agent): void
    {
        $corp = $agent->getCorp();
        if (null === $corp) {
            return;
        }

        $corpId = $corp->getCorpId();
        $secret = $agent->getSecret();

        if (null === $corpId || null === $secret) {
            return;
        }

        $request = new GetTokenRequest();
        $request->setCorpId($corpId);
        $request->setCorpSecret($secret);
        $tokenResponse = $this->request($request);

        if (!is_array($tokenResponse) || !isset($tokenResponse['access_token'], $tokenResponse['expires_in'])) {
            $this->logger->error('获取企业微信应用AccessToken失败', [
                'agent' => $agent,
                'tokenResponse' => $tokenResponse,
            ]);

            return;
        }

        $accessToken = $tokenResponse['access_token'];
        $expiresIn = $tokenResponse['expires_in'];

        if (!is_string($accessToken) || !is_int($expiresIn)) {
            $this->logger->error('AccessToken响应格式错误', [
                'agent' => $agent,
                'tokenResponse' => $tokenResponse,
            ]);

            return;
        }

        $agent->setAccessToken($accessToken);
        $agent->setAccessTokenExpireTime(CarbonImmutable::now()->addSeconds($expiresIn)->toDateTimeImmutable());
        $this->entityManager->persist($agent);
        $this->entityManager->flush();
    }

    protected function getRequestMethod(RequestInterface $request): string
    {
        $method = $request->getRequestMethod();

        return $method ?? 'POST';
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getRequestOptions(RequestInterface $request): ?array
    {
        $options = $request->getRequestOptions();
        if (!is_array($options)) {
            $options = [];
        }
        if (!isset($options['query']) || !is_array($options['query'])) {
            $options['query'] = [];
        }

        // 补充 AccessToken
        if (in_array(AgentAware::class, class_uses($request), true) && !array_key_exists('access_token', $options['query'])) {
            if (method_exists($request, 'getAgent')) {
                $agent = $request->getAgent();
                if ($agent instanceof Agent) {
                    $this->refreshAgentAccessToken($agent);
                    $token = $agent->getAccessToken();
                    $options['query']['access_token'] = $token;
                }
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
        $content = $response->getContent();
        $json = Json::decode($content);

        if (!is_array($json)) {
            throw new HttpClientException($request, $response, '响应格式错误', 0);
        }

        $errCode = $json['errcode'] ?? 0;
        $errMsg = $json['errmsg'] ?? '未知错误';

        if (is_numeric($errCode)) {
            $errCode = (int) $errCode;
        } else {
            $errCode = 0;
        }

        if (is_scalar($errMsg)) {
            $errMsg = (string) $errMsg;
        } else {
            $errMsg = '未知错误';
        }

        if (0 !== $errCode) {
            throw new GeneralHttpClientException($request, $response, $errMsg, $errCode);
        }

        return $json;
    }
}
