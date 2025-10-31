<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use WechatWorkBundle\DependencyInjection\WechatWorkExtension;

/**
 * @internal
 */
#[CoversClass(WechatWorkExtension::class)]
final class WechatWorkExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
