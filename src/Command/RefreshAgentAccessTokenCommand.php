<?php

namespace WechatWorkBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatWorkBundle\Repository\AgentRepository;
use WechatWorkBundle\Service\WorkService;

#[AsCronTask(expression: '* * * * *')]
#[AsCommand(name: self::NAME, description: '刷新企业微信应用access_token')]
class RefreshAgentAccessTokenCommand extends Command
{
    public const NAME = 'wechat-work:refresh-agent-access-token';

    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly WorkService $workService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->agentRepository->findAll() as $agent) {
            $this->workService->refreshAgentAccessToken($agent);
        }

        return Command::SUCCESS;
    }
}
