<?php

namespace Vanguard\Http\Controllers\Api\Smtoday\Iklan;

use Illuminate\Http\Request;

use Vanguard\Events\Smtoday\Iklantext\Banned;
use Vanguard\Events\Smtoday\Iklantext\Deleted;
use Vanguard\Events\Smtoday\Iklantext\UpdatedByAdmin;

use Vanguard\Http\Controllers\Api\ApiController;

use Vanguard\Http\Requests\Smtoday\Iklantext\CreateIklantextRequest;
use Vanguard\Http\Requests\Smtoday\Iklantext\UpdateIklantextRequest;

use Vanguard\Repositories\Smtoday\Iklantext\IklantextRepository;

use Vanguard\Support\Enum\IklantextStatus;
use Vanguard\Transformers\IklantextTransformer;
use Vanguard\Iklantext;

/**
 * Class IklantextsController
 * @package Vanguard\Http\Controllers\Api\Smtoday\Iklan\Iklantexts
 */
class IklantextsController extends ApiController
{
    /**
     * @var IklantextRepository
     */
    private $iklantexts;

    public function __construct(IklantextRepository $iklantexts)
    {
        $this->middleware('permission:smtoday.iklan.text');

        $this->iklantexts = $iklantexts;
    }

    /**
     * Paginate all iklantexts.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $iklantexts = $this->iklantexts->paginate(
            $request->per_page ?: 20,
            $request->search,
            $request->status
        );

        return $this->respondWithPagination(
            $iklantexts,
            new IklantextTransformer
        );
    }

    /**
     * Create new iklantexts record.
     * @param CreateIklantextRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateIklantextRequest $request)
    {
        $data = $request->only([
            'judul', 'text'
        ]);

        $data += [
            'status' => IklantextStatus::UNSEND
        ];

        $iklantext = $this->iklantexts->create($data);

        return $this->setStatusCode(201)
            ->respondWithItem($iklantext, new IklantextTransformer);
    }

    /**
     * Show the info about requested iklantexts.
     * @param Iklantext $iklantexts
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Iklantext $iklantext)
    {
        return $this->respondWithItem($iklantext, new IklantextTransformer);
    }

    /**
     * @param Iklantext $iklantext
     * @param UpdateIklantextRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Iklantext $iklantext, UpdateIklantextRequest $request)
    {
        $data = collect($request->all());

        $data = $data->only([
            'judul', 'text', 'status'
        ])->toArray();

        $iklantext = $this->iklantexts->update($iklantext->id, $data);

        event(new UpdatedByAdmin($iklantext));

        // If iklantext status was updated to "Banned",
        // fire the appropriate event.
        if ($this->iklantextIsBanned($iklantext, $request)) {
            event(new Banned($iklantext));
        }

        return $this->respondWithItem($iklantext, new IklantextTransformer);
    }

    /**
     * Check if iklantext is banned during last update.
     *
     * @param Iklantext $iklantext
     * @param Request $request
     * @return bool
     */
    private function iklantextIsBanned(Iklantext $iklantext, Request $request)
    {
        return $iklantext->status != $request->status && $request->status == IklantextStatus::BANNED;
    }

    /**
     * Remove specified iklantext from storage.
     * @param Iklantext $iklantext
     * @return \Illuminate\Http\Response
     */
    public function destroy(Iklantext $iklantext)
    {
        event(new Deleted($iklantext));

        $this->iklantexts->delete($iklantext->id);

        return $this->respondWithSuccess();
    }
}
