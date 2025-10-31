<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Constant;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Constant\ApiPath;

/**
 * @internal
 */
#[CoversClass(ApiPath::class)]
final class ApiPathTest extends TestCase
{
    public function testGetTokenConstant(): void
    {
        $constant = ApiPath::GET_TOKEN;
        self::assertStringStartsWith('/cgi-bin/', $constant);
    }

    public function testGetAgentInfoConstant(): void
    {
        $constant = ApiPath::GET_AGENT_INFO;
        self::assertStringStartsWith('/cgi-bin/', $constant);
    }

    public function testApiPathsAreStrings(): void
    {
        $reflection = new \ReflectionClass(ApiPath::class);
        $constants = $reflection->getConstants();

        self::assertGreaterThan(0, count($constants));

        foreach ($constants as $constant) {
            self::assertIsString($constant);
            self::assertStringStartsWith('/', $constant);
        }
    }

    public function testAllConstantsAreUnique(): void
    {
        $reflection = new \ReflectionClass(ApiPath::class);
        $constants = $reflection->getConstants();

        // 确保所有常量值都不相同
        $values = array_values($constants);
        self::assertSame(count($values), count(array_unique($values)));
    }
}
