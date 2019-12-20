<?php

namespace Vanguard\Support\Plugins\Smtoday\Berita;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;

class Berita extends Plugin
{
    public function sidebar()
    {
        return Item::create(__('Berita'))
        ->route('beritatext.index')
        ->icon('fas fa-newspaper')
        ->active("smtoday/beritatext*")
        ->permissions('smtoday.berita.text');
    }
}
