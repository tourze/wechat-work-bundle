<?php

namespace WechatWorkBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use WechatWorkBundle\Enum\SpecialAgent;

/**
 * @internal
 */
#[CoversClass(SpecialAgent::class)]
final class SpecialAgentTest extends AbstractEnumTestCase
{
    public function testEnumCases(): void
    {
        $cases = SpecialAgent::cases();

        self::assertCount(1, $cases);
        // 验证每个case都有有效的值和标签
        foreach ($cases as $case) {
            self::assertNotEmpty($case->value, 'Enum case must have a non-empty value');
            self::assertNotEmpty($case->getLabel(), 'Enum case must have a non-empty label');
        }

        // 验证具体的case是否存在
        self::assertContains(SpecialAgent::MESSAGE_ARCHIVE, $cases);
    }

    public function testMessageArchiveCase(): void
    {
        $messageArchive = SpecialAgent::MESSAGE_ARCHIVE;

        self::assertSame('message-archive', $messageArchive->value);
        self::assertSame('消息归档', $messageArchive->getLabel());
    }

    public function testGetLabel(): void
    {
        foreach (SpecialAgent::cases() as $case) {
            $label = match ($case) {
                SpecialAgent::MESSAGE_ARCHIVE => '消息归档',
            };

            self::assertSame($label, $case->getLabel());
        }
    }

    public function testToArray(): void
    {
        $array = SpecialAgent::MESSAGE_ARCHIVE->toArray();

        self::assertArrayHasKey('value', $array);
        self::assertArrayHasKey('label', $array);
        self::assertSame('message-archive', $array['value']);
        self::assertSame('消息归档', $array['label']);
    }

    public function testGenOptions(): void
    {
        $options = SpecialAgent::genOptions();

        self::assertCount(1, $options);

        $firstOption = $options[0];
        self::assertArrayHasKey('value', $firstOption);
        self::assertArrayHasKey('label', $firstOption);
        self::assertArrayHasKey('text', $firstOption);
        self::assertArrayHasKey('name', $firstOption);
        self::assertSame('message-archive', $firstOption['value']);
        self::assertSame('消息归档', $firstOption['label']);
    }

    public function testValueAndLabelPairs(): void
    {
        $case = SpecialAgent::from('message-archive');
        self::assertSame('message-archive', $case->value);
        self::assertSame('消息归档', $case->getLabel());
    }

    public function testFromWithValidValue(): void
    {
        $case = SpecialAgent::from('message-archive');
        self::assertSame(SpecialAgent::MESSAGE_ARCHIVE, $case);
    }

    public function testTryFromWithValidValue(): void
    {
        $case = SpecialAgent::tryFrom('message-archive');
        self::assertSame(SpecialAgent::MESSAGE_ARCHIVE, $case);
    }

    public function testTryFromWithNullReturnsNull(): void
    {
        $case = SpecialAgent::tryFrom('invalid-value');
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertNull($case);
    }

    public function testValueUniqueness(): void
    {
        $values = array_map(fn (SpecialAgent $case) => $case->value, SpecialAgent::cases());
        $uniqueValues = array_unique($values);
        self::assertCount(count($values), $uniqueValues, 'All enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (SpecialAgent $case) => $case->getLabel(), SpecialAgent::cases());
        $uniqueLabels = array_unique($labels);
        self::assertCount(count($labels), $uniqueLabels, 'All enum labels must be unique');
    }
}
