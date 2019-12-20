<?php

namespace Vanguard\Http\Controllers\Web\Smtoday\Iklan;

use Exception;

use Illuminate\Http\Request;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vanguard\Http\Controllers\Controller;


use Vanguard\Iklantext;
use Vanguard\Http\Requests\Smtoday\Iklantext\CreateIklantextRequest;
use Vanguard\Http\Requests\Smtoday\Iklantext\UpdateIklantextRequest;
use Vanguard\Support\Enum\IklantextStatus;
use Vanguard\Repositories\Smtoday\Iklantext\IklantextRepository;
use Vanguard\Events\Smtoday\Iklantext\Deleted;

/**
 * Class IklantextsController
 * @package Vanguard\Http\Controllers
 */
class IklantextsController extends Controller
{
    /**
     * @var IklantextsRepository
     */
    private $iklantexts;

    /**
     * IklantextsController constructor.
     * @param IklantextsRepository $iklantexts
     */
    public function __construct(IklantextRepository $iklantexts)
    {
        $this->iklantexts = $iklantexts;
    }

    /**
     * Displays the page with all available iklantexts.
     *
     * @return Factory|View
     */
    // public function index()
    // {
    //     return view('smtoday.iklan.index', [
    //         'iklantexts' => $this->iklantexts->all()
    //     ]);
    // }

    public function index(Request $request)
    {
        $iklantexts = $this->iklantexts->paginate($perPage = 20, $request->search, $request->status);

        $statuses = ['' => __('All')] + IklantextStatus::lists();

        return view('smtoday.iklantext.index', compact('iklantexts', 'statuses'));
    }

    /**
     * Displays the form for creating new iklantext.
     *
     * @return Factory|View
     */
    public function create()
    {
        return view('smtoday.iklantext.add', [
            'statuses' => IklantextStatus::lists(),
            'edit' => false
        ]);
    }


    /**
     * Store Iklantext to database.
     *
     * @param CreateIklantextRequest $request
     * @return mixed
     */
    public function store(CreateIklantextRequest $request)
    {
        // When iklantext is created, we will set his
        // status to Unsend by default.
        // database default falue
        $data = $request->all() + [
            'status' => IklantextStatus::UNSEND
        ];

        $this->iklantexts->create($data);

        //test
        $texts = [];
        foreach($request->input('text') as $key => $value) {
            $texts["text.{$key}"] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {


            foreach($request->input('name') as $key => $value) {
                TagList::create(['name'=>$value]);
            }


            return response()->json(['success'=>'done']);
        }


        return response()->json(['error'=>$validator->errors()->all()]);

        return redirect()->route('iklantext.index')
            ->withSuccess(__('Iklan created successfully.'));
    }

    /**
     * Displays the form for editing specific Iklantext.
     *
     * @param Iklantext $iklantext
     * @return Factory|View
     */

    public function edit(Iklantext $iklantext)
    {
        return view('smtoday.iklantext.add', [
            'edit' => true,
            'iklantext' => $iklantext,
            'statuses' => IklantextStatus::lists()
        ]);
    }

    /**
     * Update specified iklantext.
     *
     * @param Iklantext $iklantext
     * @param UpdateIklantextRequest $iklantext
     * @return mixed
     */
    public function update(Iklantext $iklantext, UpdateIklantextRequest $request)
    {
        $this->iklantexts->update($iklantext->id, $request->all());

        return redirect()->route('iklantext.index')
            ->withSuccess(__('Iklan updated successfully.'));
    }

    /**
     * Displays iklan text page.
     *
     * @param Iklantext $iklantext
     * @return Factory|View
     */
    public function show(Iklantext $iklantext)
    {
        return view('smtoday.iklanimage.index', compact('iklanimage'));
    }


    /**
     * Destroy the Iklantext if it is removable.
     *
     * @param Iklantext $iklantext
     * @return mixed
     * @throws Exception
     */
    public function destroy(Iklantext $iklantext)
    {

        $this->iklantexts->delete($iklantext->id);

        return redirect()->route('iklantext.index')
            ->withSuccess(__('Iklan deleted successfully.'));
    }
}
