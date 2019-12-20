<?php

namespace Vanguard\Events\Smtoday\Iklantext;

use Vanguard\Iklantext;

class UpdatedByAdmin
{
    /**
     * @var Iklantext
     */
    protected $updatedIklantext;

    public function __construct(Iklantext $updatedIklantext)
    {
        $this->updatedIklantext = $updatedIklantext;
    }

    /**
     * @return Iklantext
     */
    public function getUpdatedIklantext()
    {
        return $this->updatedIklantext;
    }
}
