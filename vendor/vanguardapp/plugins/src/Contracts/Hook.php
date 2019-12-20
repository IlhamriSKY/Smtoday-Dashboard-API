<?php

namespace Vanguard\Plugins\Contracts;

interface Hook
{
    /**
     * Execute the hook action.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function handle();
}
