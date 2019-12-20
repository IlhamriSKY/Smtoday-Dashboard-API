<?php

namespace Vanguard\Presenters;

use Vanguard\Support\Enum\IklantextStatus;
use Illuminate\Support\Str;

class IklantextPresenter extends Presenter
{
    /**
     * Determine css class used for status labels
     * inside the iklantext table by checking iklantext status.
     *
     * @return string
     */
    public function labelClass()
    {
        switch ($this->model->status) {
            case IklantextStatus::SEND:
                $class = 'success';
                break;

            case IklantextStatus::UNSEND:
                $class = 'danger';
                break;

            default:
                $class = 'warning';
        }

        return $class;
    }

    public function namaiklantext()
    {
        return $this->model->judul;
    }

}
