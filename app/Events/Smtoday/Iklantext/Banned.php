<?php

namespace Vanguard\Events\Smtoday\Iklantext;

use Vanguard\Iklantext;

class Banned
{
    /**
     * @var Iklantext
     */
    protected $bannedIklantext;

    public function __construct(Iklantext $bannedIklantext)
    {
        $this->bannedIklantext = $bannedIklantext;
    }

    /**
     * @return Iklantext
     */
    public function getBannedIklantext()
    {
        return $this->bannedIklantext;
    }
}
