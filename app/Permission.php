<?php

namespace Vanguard;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = ['name', 'display_name', 'description'];

    protected $casts = [
        'removable' => 'boolean'
    ];
}
