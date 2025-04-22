<?php

namespace WechatWorkBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 特殊的应用，要特殊处理
 */
enum SpecialAgent: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case MESSAGE_ARCHIVE = 'message-archive';

    public function getLabel(): string
    {
        return match ($this) {
            self::MESSAGE_ARCHIVE => '消息归档',
        };
    }
}
