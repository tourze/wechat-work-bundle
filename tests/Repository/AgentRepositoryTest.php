<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Repository\AgentRepository;

/**
 * @internal
 */
#[CoversClass(AgentRepository::class)]
#[RunTestsInSeparateProcesses]
final class AgentRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository 测试环境准备
    }

    protected function createNewEntity(): Agent
    {
        return $this->createValidEntity();
    }

    protected function getRepository(): AgentRepository
    {
        $repository = self::getContainer()->get(AgentRepository::class);
        $this->assertInstanceOf(AgentRepository::class, $repository);

        return $repository;
    }

    private function createValidEntity(): Agent
    {
        $corp = new Corp();
        $corp->setName('Test Corp ' . uniqid());
        $corp->setCorpId('test_corp_id_' . uniqid());
        $corp->setCorpSecret('test_corp_secret');
        self::getEntityManager()->persist($corp);

        $agent = new Agent();
        $agent->setName('Test Agent ' . uniqid());
        $agent->setAgentId(uniqid());
        $agent->setSecret('test_secret');
        $agent->setCorp($corp);

        return $agent;
    }

    public function testFindByWithNullableFieldsShouldSupportIsNullQueries(): void
    {
        $agent1 = $this->createValidEntity();
        $agent1->setToken('test_token');
        $agent2 = $this->createValidEntity();
        $agent2->setAgentId(uniqid());

        self::getEntityManager()->persist($agent1);
        self::getEntityManager()->persist($agent2);
        self::getEntityManager()->flush();

        $agentsWithoutToken = $this->getRepository()->findBy(['token' => null]);
        $agentsWithToken = $this->getRepository()->findBy(['token' => 'test_token']);

        $this->assertCount(1, $agentsWithoutToken);
        $this->assertCount(1, $agentsWithToken);
    }

    public function testCountWithNullableFieldsShouldSupportIsNullQueries(): void
    {
        $agent1 = $this->createValidEntity();
        $agent1->setToken('test_token');
        $agent2 = $this->createValidEntity();
        $agent2->setAgentId(uniqid());

        self::getEntityManager()->persist($agent1);
        self::getEntityManager()->persist($agent2);
        self::getEntityManager()->flush();

        $countWithoutToken = $this->getRepository()->count(['token' => null]);
        $countWithToken = $this->getRepository()->count(['token' => 'test_token']);

        $this->assertEquals(1, $countWithoutToken);
        $this->assertEquals(1, $countWithToken);
    }

    public function testFindByWithAssociationsShouldSupportJoinQueries(): void
    {
        $corp = new Corp();
        $corp->setName('Test Corp');
        $corp->setCorpId('test_corp');
        $corp->setCorpSecret('test_corp_secret');
        self::getEntityManager()->persist($corp);

        $agent = $this->createValidEntity();
        $agent->setCorp($corp);
        self::getEntityManager()->persist($agent);
        self::getEntityManager()->flush();

        $agents = $this->getRepository()->findBy(['corp' => $corp]);

        $this->assertCount(1, $agents);
        $agentCorp = $agents[0]->getCorp();
        $this->assertNotNull($agentCorp);
        $this->assertEquals($corp->getId(), $agentCorp->getId());
    }

    public function testCountWithAssociationsShouldSupportJoinQueries(): void
    {
        $corp = new Corp();
        $corp->setName('Test Corp');
        $corp->setCorpId('test_corp');
        $corp->setCorpSecret('test_corp_secret');
        self::getEntityManager()->persist($corp);

        $agent = $this->createValidEntity();
        $agent->setCorp($corp);
        self::getEntityManager()->persist($agent);
        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['corp' => $corp]);

        $this->assertEquals(1, $count);
    }

    public function testSaveMethodPersistsEntity(): void
    {
        $agent = $this->createValidEntity();

        $repository = $this->getRepository();
        $repository->save($agent);

        // 验证实体已被持久化（ID > 0表示已保存到数据库）
        $this->assertGreaterThan(0, $agent->getId());
    }

    public function testRemoveMethodRemovesEntity(): void
    {
        $agent = $this->createValidEntity();

        $repository = $this->getRepository();
        $repository->save($agent);
        $id = $agent->getId();

        $repository->remove($agent);

        $this->assertNull($this->getRepository()->find($id));
    }

    public function testFindByWithEncodingAESKeyShouldSupportIsNullQueries(): void
    {
        $agent1 = $this->createValidEntity();
        $agent1->setEncodingAESKey('test_encoding_key');
        $agent2 = $this->createValidEntity();
        $agent2->setAgentId(uniqid());

        self::getEntityManager()->persist($agent1);
        self::getEntityManager()->persist($agent2);
        self::getEntityManager()->flush();

        $agentsWithoutKey = $this->getRepository()->findBy(['encodingAESKey' => null]);
        $agentsWithKey = $this->getRepository()->findBy(['encodingAESKey' => 'test_encoding_key']);

        $this->assertCount(1, $agentsWithoutKey);
        $this->assertCount(1, $agentsWithKey);
    }

    public function testCountWithEncodingAESKeyShouldSupportIsNullQueries(): void
    {
        $agent1 = $this->createValidEntity();
        $agent1->setEncodingAESKey('test_encoding_key');
        $agent2 = $this->createValidEntity();
        $agent2->setAgentId(uniqid());

        self::getEntityManager()->persist($agent1);
        self::getEntityManager()->persist($agent2);
        self::getEntityManager()->flush();

        $countWithoutKey = $this->getRepository()->count(['encodingAESKey' => null]);
        $countWithKey = $this->getRepository()->count(['encodingAESKey' => 'test_encoding_key']);

        $this->assertEquals(1, $countWithoutKey);
        $this->assertEquals(1, $countWithKey);
    }
}
