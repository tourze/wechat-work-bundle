<?php

declare(strict_types=1);

namespace WechatWorkBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use WechatWorkBundle\Entity\Corp;

class CorpFixtures extends Fixture
{
    public const CORP_1_REFERENCE = 'corp-1';
    public const CORP_2_REFERENCE = 'corp-2';
    public const PROVIDER_CORP_REFERENCE = 'provider-corp';

    public function load(ObjectManager $manager): void
    {
        // 创建测试企业1
        $corp1 = new Corp();
        $corp1->setName('测试企业1');
        $corp1->setCorpId('test_corp_001');
        $corp1->setFromProvider(false);
        $corp1->setCorpSecret('test_corp_secret_001');

        $manager->persist($corp1);
        $this->addReference(self::CORP_1_REFERENCE, $corp1);

        // 创建测试企业2
        $corp2 = new Corp();
        $corp2->setName('测试企业2');
        $corp2->setCorpId('test_corp_002');
        $corp2->setFromProvider(true);
        $corp2->setCorpSecret('test_corp_secret_002');

        $manager->persist($corp2);
        $this->addReference(self::CORP_2_REFERENCE, $corp2);

        // 创建第三方服务商企业
        $corp3 = new Corp();
        $corp3->setName('第三方服务商');
        $corp3->setCorpId('provider_corp_001');
        $corp3->setFromProvider(true);
        $corp3->setCorpSecret('test_corp_secret_003');

        $manager->persist($corp3);
        $this->addReference(self::PROVIDER_CORP_REFERENCE, $corp3);

        $manager->flush();
    }
}
