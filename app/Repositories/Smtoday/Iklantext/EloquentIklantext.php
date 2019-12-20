<?php

namespace Vanguard\Repositories\Smtoday\Iklantext;

use Carbon\Carbon;
use Vanguard\Events\Smtoday\Iklantext\Created;
use Vanguard\Events\Smtoday\Iklantext\Deleted;
use Vanguard\Events\Smtoday\Iklantext\Updated;
use Vanguard\Iklantext;
use Cache;

class EloquentIklantext implements IklantextRepository
{
    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return Iklantext::all();
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return Iklantext::find($id);
    }

    /**
     * {@inheritdoc}
     */
    // public function create(array $data)
    // {
    //     return Iklantext::create($data);
    // }

    public function create(array $data)
    {
        $iklantext = Iklantext::create($data);

        event(new Created($iklantext));

        return $iklantext;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        $iklantext = $this->find($id);

        $iklantext->update($data);

        Cache::flush();

        event(new Updated($iklantext));

        return $iklantext;
    }

    /**
     * {@inheritdoc}
     */
    // public function delete($id)
    // {
    //     $iklantext = $this->find($id);

    //     event(new Deleted($iklantext));

    //     $status = $iklantext->delete();

    //     Cache::flush();

    //     return $status;
    // }
    // public function delete($id)
    // {
    //     $iklantext = $this->find($id);

    //     return $iklantext->delete();
    // }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $iklantext = $this->find($id);

        event(new Deleted($iklantext));

        return $iklantext->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage, $search = null, $status = null)
    {
        $query = Iklantext::query();

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
        return Iklantext::where('status', $status)->count();
    }
    public function count()
    {
        return Iklantext::count();
    }
    public function countOfNewIklantextsPerMonth(Carbon $from, Carbon $to)
    {
        $result = Iklantext::whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get(['created_at'])
            ->groupBy(function ($iklantext) {
                return $iklantext->created_at->format("Y_n");
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
