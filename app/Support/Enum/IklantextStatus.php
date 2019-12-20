<?php

namespace Vanguard\Support\Enum;

class IklantextStatus
{
    const SEND = 'Send';
    const UNSEND = 'Unsend';
    const BANNED = 'Banned';

    public static function lists()
    {
        return [
            self::SEND => trans('app.status.'.self::SEND),
            self::UNSEND => trans('app.status.'. self::UNSEND),
            self::BANNED => trans('app.status.' . self::BANNED)
        ];
    }
}


