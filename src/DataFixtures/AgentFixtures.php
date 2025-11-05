<?php

declare(strict_types=1);

namespace WechatWorkBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;

class AgentFixtures extends Fixture implements DependentFixtureInterface
{
    public const AGENT_1_REFERENCE = 'agent-1';
    public const AGENT_2_REFERENCE = 'agent-2';
    public const AGENT_3_REFERENCE = 'agent-3';

    public function load(ObjectManager $manager): void
    {
        $corp1 = $this->getReference(CorpFixtures::CORP_1_REFERENCE, Corp::class);
        assert($corp1 instanceof Corp);

        $corp2 = $this->getReference(CorpFixtures::CORP_2_REFERENCE, Corp::class);
        assert($corp2 instanceof Corp);

        // 为企业1创建代理应用
        $agent1 = new Agent();
        $agent1->setName('客服应用');
        $agent1->setAgentId('1000001');
        $agent1->setSecret('test_secret_001');
        $agent1->setToken('test_token_001');
        $agent1->setEncodingAESKey('test_aes_key_001');
        $agent1->setCorp($corp1);
        $agent1->setDescription('企业客服应用');
        $agent1->setSquareLogoUrl('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
        $agent1->setAllowUsers(['user1', 'user2']);
        $agent1->setAllowParties(['1', '2']);
        $agent1->setAllowTags(['1']);
        $agent1->setRedirectDomain('localhost');
        $agent1->setReportLocationFlag(true);
        $agent1->setReportEnter(true);
        $agent1->setHomeUrl('http://localhost:8000/home');
        $agent1->setCustomizedPublishStatus(1);

        $manager->persist($agent1);
        $this->addReference(self::AGENT_1_REFERENCE, $agent1);

        // 为企业1创建另一个代理应用
        $agent2 = new Agent();
        $agent2->setName('通知应用');
        $agent2->setAgentId('1000002');
        $agent2->setSecret('test_secret_002');
        $agent2->setToken('test_token_002');
        $agent2->setEncodingAESKey('test_aes_key_002');
        $agent2->setCorp($corp1);
        $agent2->setDescription('企业通知应用');
        $agent2->setSquareLogoUrl('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
        $agent2->setAllowUsers(['user1', 'user3']);
        $agent2->setAllowParties(['1']);
        $agent2->setAllowTags(['2']);
        $agent2->setRedirectDomain('localhost');
        $agent2->setReportLocationFlag(false);
        $agent2->setReportEnter(false);
        $agent2->setHomeUrl('http://localhost:8000/notifications');
        $agent2->setCustomizedPublishStatus(0);

        $manager->persist($agent2);
        $this->addReference(self::AGENT_2_REFERENCE, $agent2);

        // 为企业2创建代理应用
        $agent3 = new Agent();
        $agent3->setName('审批应用');
        $agent3->setAgentId('2000001');
        $agent3->setSecret('test_secret_003');
        $agent3->setToken('test_token_003');
        $agent3->setEncodingAESKey('test_aes_key_003');
        $agent3->setCorp($corp2);
        $agent3->setDescription('企业审批应用');
        $agent3->setSquareLogoUrl('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
        $agent3->setAllowUsers(['user4', 'user5']);
        $agent3->setAllowParties(['3', '4']);
        $agent3->setAllowTags(['3', '4']);
        $agent3->setRedirectDomain('localhost');
        $agent3->setReportLocationFlag(true);
        $agent3->setReportEnter(true);
        $agent3->setHomeUrl('http://localhost:8000/approval');
        $agent3->setCustomizedPublishStatus(1);

        $manager->persist($agent3);
        $this->addReference(self::AGENT_3_REFERENCE, $agent3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CorpFixtures::class,
        ];
    }
}
