<?php

namespace WechatWorkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatWorkBundle\Repository\AgentRepository;
use WechatWorkBundle\Request\GetAgentInfoRequest;
use WechatWorkBundle\Service\WorkService;

/**
 * @see https://developer.work.weixin.qq.com/document/path/96448
 */
#[AsCronTask(expression: '*/10 * * * *')]
#[AsCommand(name: self::NAME, description: '同步应用信息')]
#[WithMonologChannel(channel: 'wechat_work')]
class SyncAgentInfoCommand extends Command
{
    public const NAME = 'wechat-work:sync-agent-info';

    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly WorkService $workService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->agentRepository->findAll() as $agent) {
            try {
                $request = new GetAgentInfoRequest();
                $request->setAgent($agent);
                $res = $this->workService->request($request);

                // 确保响应是数组格式
                if (!is_array($res)) {
                    $this->logger->error('获取应用信息响应格式错误', [
                        'agent_id' => $agent->getAgentId(),
                        'response_type' => get_debug_type($res),
                    ]);
                    continue;
                }

                // 安全地访问数组元素并设置到Agent实体
                $agent->setSquareLogoUrl($this->getStringValue($res, 'square_logo_url'));
                $agent->setDescription($this->getStringValue($res, 'description'));
                $agent->setAllowUsers($this->getArrayValue($res, 'allow_userinfos'));
                $agent->setAllowParties($this->getArrayValue($res, 'allow_partys'));
                $agent->setAllowTags($this->getArrayValue($res, 'allow_tags'));
                $agent->setRedirectDomain($this->getStringValue($res, 'redirect_domain'));
                $agent->setReportLocationFlag($this->getBoolValue($res, 'report_location_flag'));
                $agent->setReportEnter($this->getBoolValue($res, 'isreportenter'));
                $agent->setHomeUrl($this->getStringValue($res, 'home_url'));
                $agent->setCustomizedPublishStatus($this->getIntValue($res, 'customized_publish_status'));
                $this->entityManager->persist($agent);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                // 记录错误但继续处理其他agent
                $this->logger->error('同步应用信息失败', [
                    'agent_id' => $agent->getAgentId(),
                    'agent_name' => $agent->getName(),
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * 安全地从数组中获取字符串值
     *
     * @param array<mixed, mixed> $data
     */
    private function getStringValue(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        if (null === $value || '' === $value) {
            return null;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * 安全地从数组中获取数组值
     *
     * @param array<mixed, mixed> $data
     * @return array<string>|null
     */
    private function getArrayValue(array $data, string $key): ?array
    {
        $value = $data[$key] ?? null;
        if (null === $value) {
            return null;
        }
        if (!is_array($value)) {
            return null;
        }

        // 确保数组值是字符串类型
        $result = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $result[] = (string) $item;
            }
        }

        return $result;
    }

    /**
     * 安全地从数组中获取布尔值
     *
     * @param array<mixed, mixed> $data
     */
    private function getBoolValue(array $data, string $key): ?bool
    {
        $value = $data[$key] ?? null;
        if (null === $value) {
            return null;
        }

        return (bool) $value;
    }

    /**
     * 安全地从数组中获取整数值
     *
     * @param array<mixed, mixed> $data
     */
    private function getIntValue(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;
        if (null === $value) {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
