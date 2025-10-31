<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatWorkBundle\Exception\RuntimeException;

/**
 * @internal
 */
#[CoversClass(RuntimeException::class)]
final class RuntimeExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionInheritance(): void
    {
        $exception = new RuntimeException('Test message');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new RuntimeException('Test message', 123);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new RuntimeException('Test message', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
