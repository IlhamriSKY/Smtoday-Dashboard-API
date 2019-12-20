<?php

namespace Vanguard\Events\Smtoday\Iklanimage;

use Vanguard\Iklanimage;

class UpdatedByAdmin
{
    /**
     * @var Iklanimage
     */
    protected $updatedIklanimage;

    public function __construct(Iklanimage $updatedIklanimage)
    {
        $this->updatedIklanimage = $updatedIklanimage;
    }

    /**
     * @return Iklanimage
     */
    public function getUpdatedIklanimage()
    {
        return $this->updatedIklanimage;
    }
}
