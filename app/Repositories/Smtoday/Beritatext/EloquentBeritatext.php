<?php

namespace Vanguard\Repositories\Smtoday\Beritatext;

use Vanguard\Events\Smtoday\Beritatext\Created;
use Vanguard\Events\Smtoday\Beritatext\Deleted;
use Vanguard\Events\Smtoday\Beritatext\Updated;
use Vanguard\Beritatext;
use Cache;

class EloquentBeritatext implements BeritatextRepository
{
    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return Beritatext::all();
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return Beritatext::find($id);
    }

    /**
     * {@inheritdoc}
     */
    // public function create(array $data)
    // {
    //     return Beritatext::create($data);
    // }

    public function create(array $data)
    {
        $beritatext = Beritatext::create($data);

        event(new Created($beritatext));

        return $beritatext;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        $beritatext = $this->find($id);

        $beritatext->update($data);

        Cache::flush();

        event(new Updated($beritatext));

        return $beritatext;
    }

    /**
     * {@inheritdoc}
     */
    // public function delete($id)
    // {
    //     $Beritatext = $this->find($id);

    //     event(new Deleted($Beritatext));

    //     $status = $Beritatext->delete();

    //     Cache::flush();

    //     return $status;
    // }
    // public function delete($id)
    // {
    //     $Beritatext = $this->find($id);

    //     return $Beritatext->delete();
    // }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $beritatext = $this->find($id);

        event(new Deleted($beritatext));

        return $beritatext->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage, $search = null, $status = null)
    {
        $query = Beritatext::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', "like", "%{$search}%");
                $q->orWhere('text', 'like', "%{$search}%");
            });
        }

        $result = $query->orderBy('id', 'desc')
            ->paginate($perPage);

        if ($search) {
            $result->appends(['search' => $search]);
        }

        if ($status) {
            $result->appends(['status' => $status]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function countByStatus($status)
    {
        return Beritatext::where('status', $status)->count();
    }
}
