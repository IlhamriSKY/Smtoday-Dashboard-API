<?php

namespace Vanguard\Announcements\Http\Controllers\Api;

use Vanguard\Http\Controllers\Api\ApiController;

class ReadAnnouncementsController extends ApiController
{
    /**
     * Update the timestamp when announcements were last read
     * by the currently authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        auth()->user()->forceFill([
            'announcements_last_read_at' => now()
        ])->save();

        return $this->respondWithSuccess();
    }
}
