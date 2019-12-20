<?php

namespace Vanguard\Presenters;

use Vanguard\Support\Enum\IklantextStatus;
use Illuminate\Support\Str;

class IklanimagePresenter extends Presenter
{
    public function image()
    {
        if (! $this->model->image) {
            return url('assets/img/profile.png');
        }

        return Str::contains($this->model->image, ['http', 'gravatar'])
            ? $this->model->image
            : url("upload/smtoday/iklanimage/1080/{$this->model->image}");
    }

    /**
     * Determine css class used for status labels
     * inside the iklanimage table by checking iklanimage status.
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
    public function namaiklanimage()
    {
        return $this->model->judul;
    }
}
