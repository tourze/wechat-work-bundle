<?php

namespace WechatWorkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
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
#[AsCronTask('*/10 * * * *')]
#[AsCommand(name: 'wechat-work:sync-agent-info', description: '同步应用信息')]
class SyncAgentInfoCommand extends Command
{
    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly WorkService $workService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->agentRepository->findAll() as $agent) {
            $request = new GetAgentInfoRequest();
            $request->setAgent($agent);
            $res = $this->workService->request($request);

            $agent->setSquareLogoUrl($res['square_logo_url']);
            $agent->setDescription($res['description']);
            $agent->setAllowUsers($res['allow_userinfos']);
            $agent->setAllowParties($res['allow_partys']);
            $agent->setAllowTags($res['allow_tags']);
            $agent->setRedirectDomain($res['redirect_domain']);
            $agent->setReportLocationFlag((bool) $res['report_location_flag']);
            $agent->setReportEnter((bool) $res['isreportenter']);
            $agent->setHomeUrl($res['home_url']);
            $agent->setCustomizedPublishStatus($res['customized_publish_status']);
            $this->entityManager->persist($agent);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
