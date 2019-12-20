<?php

namespace Vanguard\Http\Controllers\Web\Smtoday\Berita;

use Exception;

use Illuminate\Http\Request;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vanguard\Http\Controllers\Controller;


use Vanguard\Beritatext;
use Vanguard\Http\Requests\Smtoday\Beritatext\CreateBeritatextRequest;
use Vanguard\Http\Requests\Smtoday\Beritatext\UpdateBeritatextRequest;
use Vanguard\Support\Enum\IklantextStatus;
use Vanguard\Repositories\Smtoday\Beritatext\BeritatextRepository;
use Vanguard\Events\Smtoday\Beritatext\Deleted;

/**
 * Class BeritatextsController
 * @package Vanguard\Http\Controllers
 */
class BeritatextsController extends Controller
{
    /**
     * @var BeritatextRepository
     */
    private $beritatexts;

    /**
     * BeritatextsController constructor.
     * @param BeritatextRepository $beritatexts
     */
    public function __construct(BeritatextRepository $beritatexts)
    {
        $this->beritatexts = $beritatexts;
    }

    /**
     * Displays the page with all available beritatext.
     *
     * @return Factory|View
     */

    public function index(Request $request)
    {
        $beritatexts = $this->beritatexts->paginate($perPage = 20, $request->search, $request->status);

        $statuses = ['' => __('All')] + IklantextStatus::lists();

        return view('smtoday.beritatext.index', compact('beritatexts', 'statuses'));
    }

    /**
     * Displays the form for creating new beritatext.
     *
     * @return Factory|View
     */
    public function create()
    {
        return view('smtoday.beritatext.add', [
            'statuses' => IklantextStatus::lists(),
            'edit' => false
        ]);
    }


    /**
     * Store beritatexts to database.
     *
     * @param CreateBeritatextRequest $request
     * @return mixed
     */
    public function store(CreateBeritatextRequest $request)
    {
        // When beritatexts is created, we will set his
        // status to Unsend by default.
        // database default falue
        $data = $request->all() + [
            'status' => IklantextStatus::UNSEND
        ];

        $this->beritatexts->create($data);

        return redirect()->route('beritatext.index')
            ->withSuccess(__('Iklan created successfully.'));
    }

    /**
     * Displays the form for editing specific beritatexts.
     *
     * @param Beritatext $beritatexts
     * @return Factory|View
     */

    public function edit(Beritatext $beritatext)
    {
        return view('smtoday.beritatext.add', [
            'edit' => true,
            'beritatext' => $beritatext,
            'statuses' => IklantextStatus::lists()
        ]);
    }

    /**
     * Update specified beritatext.
     *
     * @param Beritatext $beritatext
     * @param UpdateBeritatextRequest $beritatext
     * @return mixed
     */
    public function update(Beritatext $beritatext, UpdateBeritatextRequest $request)
    {
        $this->beritatexts->update($beritatext->id, $request->all());

        return redirect()->route('beritatext.index')
            ->withSuccess(__('Berita updated successfully.'));
    }

    /**
     * Displays berita text page.
     *
     * @param Beritatext $beritatext
     * @return Factory|View
     */
    public function show(Beritatext $beritatext)
    {
        return view('smtoday.beritatext.index', compact('iklanimage'));
    }


    /**
     * Destroy the beritatext if it is removable.
     *
     * @param Beritatext $beritatext
     * @return mixed
     * @throws Exception
     */
    public function destroy(Beritatext $beritatext)
    {

        $this->beritatexts->delete($beritatext->id);

        return redirect()->route('beritatext.index')
            ->withSuccess(__('Berita deleted successfully.'));
    }
}
