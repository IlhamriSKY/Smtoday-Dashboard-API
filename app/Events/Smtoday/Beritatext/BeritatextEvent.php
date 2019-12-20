<?php

namespace Vanguard\Events\Smtoday\Beritatext;

use Vanguard\Beritatext;

abstract class BeritatextEvent
{
    /**
     * @var Beritatext
     */
    protected $beritatext;

    public function __construct(Beritatext $beritatext)
    {
        $this->beritatext = $beritatext;
    }

    /**
     * @return Beritatext
     */
    public function getBeritatext()
    {
        return $this->beritatext;
    }
}
