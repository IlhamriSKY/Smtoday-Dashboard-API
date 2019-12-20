<?php

namespace Vanguard\Events\Smtoday\Iklanimage;

use Vanguard\Iklanimage;

abstract class IklanimageEvent
{
    /**
     * @var Iklanimage
     */
    protected $iklanimage;

    public function __construct(Iklanimage $iklanimage)
    {
        $this->iklanimage = $iklanimage;
    }

    /**
     * @return Iklanimage
     */
    public function getIklanimage()
    {
        return $this->iklanimage;
    }
}
