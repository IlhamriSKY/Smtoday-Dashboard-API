<?php

namespace Vanguard\Announcements\Repositories;

use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Events\Created;
use Vanguard\Announcements\Events\Deleted;
use Vanguard\Announcements\Events\Updated;
use Vanguard\User;

class EloquentAnnouncements implements AnnouncementsRepository
{

    /**
     * Get latest announcements.
     *
     * @param int $count
     * @return mixed
     */
    public function latest($count = 5)
    {
        return Announcement::latest()->take($count)->get();
    }

    /**
     * Paginate announcements in descending order.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginate($perPage = 10)
    {
        return Announcement::latest()->paginate($perPage);
    }

    /**
     * Create an announcement for user.
     *
     * @param User $user
     * @param $title
     * @param $body
     * @return mixed
     */
    public function createFor(User $user, $title, $body)
    {
        $announcement = Announcement::create([
            'title' => $title,
            'body' => $body,
            'user_id' => $user->id
        ]);

        Created::dispatch($announcement);

        return $announcement;
    }

    /**
     * Find announcement by ID.
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return Announcement::find($id);
    }

    /**
     * Update announcement.
     *
     * @param Announcement $announcement
     * @param $title
     * @param $body
     * @return mixed
     */
    public function update(Announcement $announcement, $title, $body)
    {
        $announcement->update([
            'title' => $title,
            'body' => $body
        ]);

        Updated::dispatch($announcement);

        return $announcement;
    }

    /**
     * Remove announcement from the system.
     *
     * @param Announcement $announcement
     * @return bool
     * @throws \Exception
     */
    public function delete(Announcement $announcement)
    {
        if ($announcement->delete()) {
            Deleted::dispatch($announcement);
            return true;
        }

        return false;
    }
}
