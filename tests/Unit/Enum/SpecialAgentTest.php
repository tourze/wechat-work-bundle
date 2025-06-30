<?php

namespace WechatWorkBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use WechatWorkBundle\Enum\SpecialAgent;

class SpecialAgentTest extends TestCase
{
    public function testEnumCases(): void
    {
        $cases = SpecialAgent::cases();
        
        self::assertCount(1, $cases);
        self::assertContainsOnlyInstancesOf(SpecialAgent::class, $cases);
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
    
    public function testToSelectItem(): void
    {
        $item = SpecialAgent::MESSAGE_ARCHIVE->toSelectItem();
        
        self::assertArrayHasKey('value', $item);
        self::assertArrayHasKey('label', $item);
        self::assertArrayHasKey('text', $item);
        self::assertArrayHasKey('name', $item);
        self::assertSame('message-archive', $item['value']);
        self::assertSame('消息归档', $item['label']);
        self::assertSame('消息归档', $item['text']);
        self::assertSame('消息归档', $item['name']);
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
}