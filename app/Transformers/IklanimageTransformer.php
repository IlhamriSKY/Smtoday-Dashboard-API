<?php

namespace Vanguard\Transformers;

use League\Fractal\TransformerAbstract;
use Vanguard\Iklanimage;

class IklanimageTransformer extends TransformerAbstract
{

    public function transform(Iklanimage $iklanimage)
    {
        return [
            'id' => $iklanimage->id,
            'judul' => $iklanimage->judul,
            'image' => $iklanimage->present()->image,
            'status' => $iklanimage->status,
            'created_at' => (string) $iklanimage->created_at,
            'updated_at' => (string) $iklanimage->updated_at
        ];
    }
}
