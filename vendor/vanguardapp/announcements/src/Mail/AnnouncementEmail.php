<?php

namespace Vanguard\Announcements\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Vanguard\Announcements\Announcement;

class AnnouncementEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var Announcement
     */
    public $announcement;

    /**
     * Create a new message instance.
     *
     * @param Announcement $announcement
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = sprintf("[%s] %s", __('Announcement'), $this->announcement->title);

        return $this->subject($subject)
            ->markdown('announcements::mail.notification');
    }
}
