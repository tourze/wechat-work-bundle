<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatWorkBundle\WechatWorkBundle;

/**
 * @internal
 */
#[CoversClass(WechatWorkBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatWorkBundleTest extends AbstractBundleTestCase
{
}
