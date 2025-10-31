<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Repository\CorpRepository;

/**
 * @internal
 */
#[CoversClass(CorpRepository::class)]
#[RunTestsInSeparateProcesses]
final class CorpRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository 测试环境准备
    }

    protected function createNewEntity(): Corp
    {
        return $this->createValidEntity();
    }

    protected function getRepository(): CorpRepository
    {
        $repository = self::getContainer()->get(CorpRepository::class);
        $this->assertInstanceOf(CorpRepository::class, $repository);

        return $repository;
    }

    private function createValidEntity(): Corp
    {
        $corp = new Corp();
        $corp->setName('Test Corp ' . uniqid());
        $corp->setCorpId('test_corp_id_' . uniqid());
        $corp->setCorpSecret('test_corp_secret');

        return $corp;
    }

    public function testSaveMethodPersistsEntity(): void
    {
        $corp = $this->createValidEntity();

        $repository = $this->getRepository();
        $repository->save($corp);

        // 验证实体已被持久化（ID > 0表示已保存到数据库）
        $this->assertGreaterThan(0, $corp->getId());
    }

    public function testRemoveMethodRemovesEntity(): void
    {
        $corp = $this->createValidEntity();

        $repository = $this->getRepository();
        $repository->save($corp);
        $id = $corp->getId();

        $repository->remove($corp);

        $this->assertNull($this->getRepository()->find($id));
    }

    public function testFindByWithProviderInfoShouldSupportIsNullQueries(): void
    {
        // 清理可能存在的旧数据
        foreach ($this->getRepository()->findAll() as $entity) {
            self::getEntityManager()->remove($entity);
        }
        self::getEntityManager()->flush();

        $corp1 = $this->createValidEntity();
        $corp1->setFromProvider(true);
        $corp2 = $this->createValidEntity();
        $corp2->setCorpId('corp_without_provider');
        $corp2->setFromProvider(null); // 明确设置为 null

        self::getEntityManager()->persist($corp1);
        self::getEntityManager()->persist($corp2);
        self::getEntityManager()->flush();

        $corpsFromProvider = $this->getRepository()->findBy(['fromProvider' => true]);
        $corpsNotFromProvider = $this->getRepository()->findBy(['fromProvider' => null]);

        $this->assertCount(1, $corpsFromProvider);
        $this->assertCount(1, $corpsNotFromProvider);
    }

    public function testCountWithProviderInfoShouldSupportIsNullQueries(): void
    {
        // 清理可能存在的旧数据
        foreach ($this->getRepository()->findAll() as $entity) {
            self::getEntityManager()->remove($entity);
        }
        self::getEntityManager()->flush();

        $corp1 = $this->createValidEntity();
        $corp1->setFromProvider(true);
        $corp2 = $this->createValidEntity();
        $corp2->setCorpId('corp_without_provider');
        $corp2->setFromProvider(null); // 明确设置为 null

        self::getEntityManager()->persist($corp1);
        self::getEntityManager()->persist($corp2);
        self::getEntityManager()->flush();

        $countFromProvider = $this->getRepository()->count(['fromProvider' => true]);
        $countNotFromProvider = $this->getRepository()->count(['fromProvider' => null]);

        $this->assertEquals(1, $countFromProvider);
        $this->assertEquals(1, $countNotFromProvider);
    }
}
