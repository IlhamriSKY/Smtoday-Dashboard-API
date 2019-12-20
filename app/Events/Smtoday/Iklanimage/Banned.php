<?php

namespace Vanguard\Events\Smtoday\Iklanimage;

use Vanguard\Iklanimage;

class Banned
{
    /**
     * @var Iklanimage
     */
    protected $bannedIklanimage;

    public function __construct(Iklanimage $bannedIklanimage)
    {
        $this->bannedIklanimage = $bannedIklanimage;
    }

    /**
     * @return Iklanimage
     */
    public function getBannedIklanimage()
    {
        return $this->bannedIklanimage;
    }
}
