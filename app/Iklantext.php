<?php

namespace Vanguard;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Support\Authorization\AuthorizationRoleTrait;
use Vanguard\Presenters\Traits\Presentable;

use Vanguard\Presenters\IklantextPresenter;

class Iklantext extends Model
{
    use AuthorizationRoleTrait,Presentable;

    protected $presenter = IklantextPresenter::class;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'smtoday_iklan_text';

    protected $casts = [
        'removable' => 'boolean'
    ];

    protected $fillable = ['judul', 'text', 'status', 'created_at', 'updated_at'];

    public $sortable = ['judul', 'text', 'status', 'created_at', 'updated_at'];
}
