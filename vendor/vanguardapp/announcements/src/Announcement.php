<?php

namespace Vanguard\Announcements;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Parsedown;
use Vanguard\User;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wasReadBy(User $user)
    {
        return $user->announcements_last_read_at < $this->created_at;
    }

    public function getParsedBodyAttribute()
    {
        return new HtmlString(
            (new Parsedown)->setSafeMode(true)->text($this->attributes['body'])
        );
    }
}
