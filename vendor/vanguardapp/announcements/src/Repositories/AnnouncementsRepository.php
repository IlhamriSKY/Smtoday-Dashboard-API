<?php

namespace Vanguard\Announcements\Repositories;

use Vanguard\Announcements\Announcement;
use Vanguard\User;

interface AnnouncementsRepository
{
    /**
     * Get latest announcements.
     *
     * @param int $count
     * @return mixed
     */
    public function latest($count = 5);

    /**
     * Paginate announcements in descending order.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginate($perPage = 10);

    /**
     * Create an announcement for user.
     *
     * @param User $user
     * @param $title
     * @param $body
     * @return mixed
     */
    public function createFor(User $user, $title, $body);

    /**
     * Find announcement by ID.
     *
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * Update announcement.
     *
     * @param Announcement $announcement
     * @param $title
     * @param $body
     * @return mixed
     */
    public function update(Announcement $announcement, $title, $body);

    /**
     * Remove announcement from the system.
     *
     * @param Announcement $announcement
     * @return mixed
     */
    public function delete(Announcement $announcement);
}
