<?php

namespace Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklanimage;

use Vanguard\Plugins\Widget;
use Vanguard\Repositories\Smtoday\Iklanimage\IklanimageRepository;
use Vanguard\Support\Enum\IklantextStatus;

class SendIklanimages extends Widget
{
    /**
     * {@inheritdoc}
     */
    public $width = '3';

    /**
     * {@inheritdoc}
     */
    protected $permissions = 'smtoday.iklan.image';

    /**
     * @var IklanimageRepository
     */
    private $iklanimages;

    /**
     * TotalIklanimages constructor.
     * @param IklanimageRepository $iklanimages
     */
    public function __construct(IklanimageRepository $iklanimages)
    {
        $this->iklanimages = $iklanimages;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return view('plugins.dashboard.widgets.Smtoday.Iklanimage.send-iklanimages', [
            'count' => $this->iklanimages->countByStatus(IklantextStatus::SEND)
        ]);
    }
}
