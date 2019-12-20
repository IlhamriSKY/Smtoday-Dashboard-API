<?php

namespace Vanguard\Events\Smtoday\Iklantext;

use Vanguard\Iklantext;

abstract class IklantextEvent
{
    /**
     * @var Iklantext
     */
    protected $iklantext;

    public function __construct(Iklantext $iklantext)
    {
        $this->iklantext = $iklantext;
    }

    /**
     * @return Iklantext
     */
    public function getIklantext()
    {
        return $this->iklantext;
    }
}
