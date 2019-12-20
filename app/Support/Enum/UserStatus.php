<?php

namespace Vanguard\Support\Enum;

class UserStatus
{
    const UNCONFIRMED = 'Unconfirmed';
    const ACTIVE = 'Active';
    const BANNED = 'Banned';

    public static function lists()
    {
        return [
            self::ACTIVE => trans('app.status.'.self::ACTIVE),
            self::BANNED => trans('app.status.'. self::BANNED),
            self::UNCONFIRMED => trans('app.status.' . self::UNCONFIRMED)
        ];
    }
}
