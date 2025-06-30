<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Repository\CorpRepository;

final class CorpRepositoryTest extends TestCase
{
    public function testRepositoryClass(): void
    {
        $reflection = new \ReflectionClass(CorpRepository::class);
        $parentClass = $reflection->getParentClass();
        
        self::assertNotFalse($parentClass);
        self::assertSame('ServiceEntityRepository', $parentClass->getShortName());
    }

    public function testConstructorAcceptsManagerRegistry(): void
    {
        $reflection = new \ReflectionClass(CorpRepository::class);
        $constructor = $reflection->getConstructor();
        
        self::assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        
        self::assertCount(1, $parameters);
        self::assertSame('registry', $parameters[0]->getName());
    }
}