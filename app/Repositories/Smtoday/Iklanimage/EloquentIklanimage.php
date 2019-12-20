<?php

namespace Vanguard\Repositories\Smtoday\Iklanimage;

use Carbon\Carbon;
use Vanguard\Events\Smtoday\Iklanimage\Created;
use Vanguard\Events\Smtoday\Iklanimage\Deleted;
use Vanguard\Events\Smtoday\Iklanimage\Updated;
use Vanguard\Iklanimage;
use Cache;

class EloquentIklanimage implements IklanimageRepository
{
    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return Iklanimage::all();
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return Iklanimage::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $iklanimage = Iklanimage::create($data);

        event(new Created($iklanimage));

        return $iklanimage;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        $iklanimage = $this->find($id);

        $iklanimage->update($data);

        Cache::flush();

        event(new Updated($iklanimage));

        return $iklanimage;
    }

    /**
     * {@inheritdoc}
     */
    // public function delete($id)
    public function delete($id)
    {
        $iklanimage = $this->find($id);

        event(new Deleted($iklanimage));

        return $iklanimage->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage, $search = null, $status = null)
    {
        $query = Iklanimage::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', "like", "%{$search}%");
                $q->orWhere('image', 'like', "%{$search}%");
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
     * Parse date from "Y_m" format to "{Month Name} {Year}" format.
     * @param $yearMonth
     * @return string
     */
    private function parseDate($yearMonth)
    {
        list($year, $month) = explode("_", $yearMonth);

        $month = trans("app.months.{$month}");

        return "{$month} {$year}";
    }


    /**
     * {@inheritdoc}
     */
    public function countByStatus($status)
    {
        return Iklanimage::where('status', $status)->count();
    }
    public function count()
    {
        return Iklanimage::count();
    }
    public function countOfNewIklanimagesPerMonth(Carbon $from, Carbon $to)
    {
        $result = Iklanimage::whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get(['created_at'])
            ->groupBy(function ($iklanimage) {
                return $iklanimage->created_at->format("Y_n");
            });

        $counts = [];

        while ($from->lt($to)) {
            $key = $from->format("Y_n");

            $counts[$this->parseDate($key)] = count($result->get($key, []));

            $from->addMonth();
        }
        return $counts;
    }
}
