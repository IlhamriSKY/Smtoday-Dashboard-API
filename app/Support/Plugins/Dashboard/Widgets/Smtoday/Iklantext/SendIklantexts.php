<?php

namespace Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklantext;

use Vanguard\Plugins\Widget;
use Vanguard\Repositories\Smtoday\Iklantext\IklantextRepository;
use Vanguard\Support\Enum\IklantextStatus;

class SendIklantexts extends Widget
{
    /**
     * {@inheritdoc}
     */
    public $width = '3';

    /**
     * {@inheritdoc}
     */
    protected $permissions = 'smtoday.iklan.text';

    /**
     * @var IklantextRepository
     */
    private $iklantexts;

    /**
     * TotalIklantexts constructor.
     * @param IklantextRepository $iklantexts
     */
    public function __construct(IklantextRepository $iklantexts)
    {
        $this->iklantexts = $iklantexts;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return view('plugins.dashboard.widgets.Smtoday.Iklantext.send-iklantexts', [
            'count' => $this->iklantexts->countByStatus(IklantextStatus::SEND)
        ]);
    }
}
