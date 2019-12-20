<?php

namespace Vanguard\Support\Plugins\Smtoday\Iklan;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;

class Iklantextandimage extends Plugin
{
    public function sidebar()
    {
        $texts = Item::create(__('Texts'))
            ->route('iklantext.index')
            ->icon('fas fa-comment-alt')
            ->active("smtoday/iklantext*")
            ->permissions('smtoday.iklan.text');

        $images = Item::create(__('Images'))
            ->route('iklanimage.index')
            ->icon('fas fa-image')
            ->active("smtoday/iklanimage*")
            ->permissions('smtoday.iklan.image');

        return Item::create(__('Iklan Texts & Images'))
            ->href('#iklan-dropdown')
            ->icon('fas fa-ad')
            ->permissions(['smtoday.iklan.text', 'smtoday.iklan.image'])
            ->addChildren([
                $texts,
                $images
            ]);
    }
}
