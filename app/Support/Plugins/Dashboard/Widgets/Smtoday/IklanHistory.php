<?php

namespace Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday;

use Carbon\Carbon;
use Vanguard\Plugins\Widget;

use Vanguard\Repositories\Smtoday\Iklantext\IklantextRepository;
use Vanguard\Repositories\Smtoday\Iklanimage\IklanimageRepository;


class IklanHistory extends Widget
{
    /**
     * {@inheritdoc}
     */
    public $width = '8';

    /**
     * @var string
     */
    // protected $permissions = 'smtoday.iklan.text';
    protected $permissions = ['smtoday.iklan.text','smtoday.iklan.image'];

    /**
     * @var IklantextRepository
     * @var IklanimageRepository
     */
    private $iklantexts;
    private $iklanimages;

    /**
     * @var array Count of new iklantext per month.
     * @var array Count of new iklanimage per month.
     */
    protected $iklantextsPerMonth;
    protected $iklanimagesPerMonth;

    /**
     * IklanHistory constructor.
     * @param IklantextRepository $iklantexts
     * @param IklanimageRepository $iklanimages
     */
    public function __construct(IklantextRepository $iklantexts, IklanimageRepository $iklanimages)
    {
        $this->iklantexts = $iklantexts;
        $this->iklanimages = $iklanimages;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return view('plugins.dashboard.widgets.Smtoday.iklan-history', [
            'iklantextsPerMonth' => $this->getIklantextsPerMonth(),
            'iklanimagesPerMonth' => $this->getIklanimagesPerMonth()
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function scripts()
    {
        return view('plugins.dashboard.widgets.Smtoday.iklan-history-scripts', [
            'iklantextsPerMonth' => $this->getIklantextsPerMonth(),
            'iklanimagesPerMonth' => $this->getIklanimagesPerMonth()
        ]);
    }

    private function getIklantextsPerMonth()
    {
        if ($this->iklantextsPerMonth) {
            return $this->iklantextsPerMonth;
        }

        return $this->iklantextsPerMonth = $this->iklantexts->countOfNewIklantextsPerMonth(
            Carbon::now()->subYear()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );
    }
    private function getIklanimagesPerMonth()
    {
        if ($this->iklanimagesPerMonth) {
            return $this->iklanimagesPerMonth;
        }

        return $this->iklanimagesPerMonth = $this->iklanimages->countOfNewIklanimagesPerMonth(
            Carbon::now()->subYear()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );
    }
}
