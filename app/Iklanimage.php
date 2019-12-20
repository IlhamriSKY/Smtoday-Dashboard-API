<?php

namespace Vanguard;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Support\Authorization\AuthorizationRoleTrait;
use Vanguard\Presenters\Traits\Presentable;

use Vanguard\Presenters\IklanimagePresenter;

class Iklanimage extends Model
{
    use AuthorizationRoleTrait,Presentable;

    protected $presenter = IklanimagePresenter::class;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'smtoday_iklan_image';

    protected $casts = [
        'removable' => 'boolean'
    ];

    protected $fillable = ['judul', 'image', 'status', 'created_at', 'updated_at'];

    public $sortable = ['judul', 'image', 'status', 'created_at', 'updated_at'];
}
