<?php

namespace Vanguard\Transformers;

use League\Fractal\TransformerAbstract;
use Vanguard\Iklantext;

class IklantextTransformer extends TransformerAbstract
{

    public function transform(Iklantext $iklantext)
    {
        return [
            'id' => $iklantext->id,
            'judul' => $iklantext->judul,
            'text' => $iklantext->text,
            'status' => $iklantext->status,
            'created_at' => (string) $iklantext->created_at,
            'updated_at' => (string) $iklantext->updated_at
        ];
    }
}
