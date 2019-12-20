<?php

namespace Vanguard\Http\Controllers\Api\Smtoday\Iklan;

use Illuminate\Http\Request;

use Vanguard\Events\Smtoday\Iklanimage\Banned;
use Vanguard\Events\Smtoday\Iklanimage\Deleted;
use Vanguard\Events\Smtoday\Iklanimage\UpdatedByAdmin;

use Vanguard\Http\Controllers\Api\ApiController;

use Vanguard\Http\Requests\Smtoday\Iklanimage\CreateIklanimageRequest;
use Vanguard\Http\Requests\Smtoday\Iklanimage\UpdateIklanimageRequest;

use Vanguard\Repositories\Smtoday\Iklanimage\IklanimageRepository;

use Vanguard\Support\Enum\IklantextStatus;
use Vanguard\Transformers\IklanimageTransformer;
use Vanguard\Iklanimage;

/**
 * Class IklanimagesController
 * @package Vanguard\Http\Controllers\Api\Smtoday\Iklan\Iklanimages
 */
class IklanimagesController extends ApiController
{
    /**
     * @var IklanimageRepository
     */
    private $iklanimages;

    public function __construct(IklanimageRepository $iklanimages)
    {
        $this->middleware('permission:smtoday.iklan.image');

        $this->iklanimages = $iklanimages;
    }

    /**
     * Paginate all Iklanimages.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $iklanimages = $this->iklanimages->paginate(
            $request->per_page ?: 20,
            $request->search,
            $request->status
        );

        return $this->respondWithPagination(
            $iklanimages,
            new IklanimageTransformer
        );
    }

    /**
     * Create new Iklanimages record.
     * @param CreateIklanimageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateIklanimageRequest $request)
    {
        $data = $request->only([
            'judul', 'image'
        ]);

        $data += [
            'status' => IklantextStatus::UNSEND
        ];

        $Iklanimage = $this->iklanimages->create($data);

        return $this->setStatusCode(201)
            ->respondWithItem($iklanimage, new IklanimageTransformer);
    }

    /**
     * Show the info about requested Iklanimages.
     * @param Iklanimage $Iklanimages
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Iklanimage $iklanimage)
    {
        return $this->respondWithItem($iklanimage, new IklanimageTransformer);
    }

    /**
     * @param Iklanimage $Iklanimage
     * @param UpdateIklanimageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Iklanimage $iklanimage, UpdateIklanimageRequest $request)
    {
        $data = collect($request->all());

        $data = $data->only([
            'judul', 'image', 'status'
        ])->toArray();

        $iklanimage = $this->iklanimages->update($iklanimage->id, $data);

        event(new UpdatedByAdmin($iklanimage));

        // If Iklanimage status was updated to "Banned",
        // fire the appropriate event.
        if ($this->IklanimageIsBanned($iklanimage, $request)) {
            event(new Banned($iklanimage));
        }

        return $this->respondWithItem($iklanimage, new IklanimageTransformer);
    }

    /**
     * Check if Iklanimage is banned during last update.
     *
     * @param Iklanimage $Iklanimage
     * @param Request $request
     * @return bool
     */
    private function IklanimageIsBanned(Iklanimage $iklanimage, Request $request)
    {
        return $iklanimage->status != $request->status && $request->status == IklanimageStatus::BANNED;
    }

    /**
     * Remove specified Iklanimage from storage.
     * @param Iklanimage $Iklanimage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Iklanimage $iklanimage)
    {
        event(new Deleted($iklanimage));

        $this->iklanimages->delete($iklanimage->id);

        return $this->respondWithSuccess();
    }
}
