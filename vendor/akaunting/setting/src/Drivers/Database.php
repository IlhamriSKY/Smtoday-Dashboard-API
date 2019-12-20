<?php

namespace Akaunting\Setting\Drivers;

use Akaunting\Setting\Contracts\Driver;
use Akaunting\Setting\Support\Arr;
use Illuminate\Support\Arr as LaravelArr;
use Closure;
use Illuminate\Database\Connection;

class Database extends Driver
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * The table to query from.
     *
     * @var string
     */
    protected $table;

    /**
     * The key column name to query from.
     *
     * @var string
     */
    protected $key;

    /**
     * The value column name to query from.
     *
     * @var string
     */
    protected $value;

    /**
     * Any query constraints that should be applied.
     *
     * @var Closure|null
     */
    protected $query_constraint;

    /**
     * Any extra columns that should be added to the rows.
     *
     * @var array
     */
    protected $extra_columns = [];

    /**
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     */
    public function __construct(Connection $connection, $table = null, $key = null, $value = null)
    {
        $this->connection = $connection;
        $this->table = $table ?: 'settings';
        $this->key = $key ?: 'key';
        $this->value = $value ?: 'value';
    }

    /**
     * Set the table to query from.
     *
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Set the key column name to query from.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Set the value column name to query from.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Set the query constraint.
     *
     * @param Closure $callback
     */
    public function setConstraint(Closure $callback)
    {
        $this->data = [];
        $this->loaded = false;
        $this->query_constraint = $callback;
    }

    /**
     * Set extra columns to be added to the rows.
     *
     * @param array $columns
     */
    public function setExtraColumns(array $columns)
    {
        $this->extra_columns = $columns;
    }

    /**
     * Get extra columns added to the rows.
     *
     * @return array
     */
    public function getExtraColumns()
    {
        return $this->extra_columns;
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key)
    {
        parent::forget($key);

        // because the database driver cannot store empty arrays, remove empty
        // arrays to keep data consistent before and after saving
        $segments = explode('.', $key);
        array_pop($segments);

        while ($segments) {
            $segment = implode('.', $segments);

            // non-empty array - exit out of the loop
            if ($this->get($segment)) {
                break;
            }

            // remove the empty array and move on to the next segment
            $this->forget($segment);
            array_pop($segments);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $data)
    {
        $keys = $this->newQuery()->pluck($this->key);

        $insert_data = LaravelArr::dot($data);
        $update_data = [];
        $delete_keys = [];

        foreach ($keys as $key) {
            if (isset($insert_data[$key])) {
                $update_data[$key] = $insert_data[$key];
            } else {
                $delete_keys[] = $key;
            }

            unset($insert_data[$key]);
        }

        foreach ($update_data as $key => $value) {
            $this->newQuery()
                ->where($this->key, '=', $key)
                ->update([$this->value => $value]);
        }

        if ($insert_data) {
            $this->newQuery(true)
                ->insert($this->prepareInsertData($insert_data));
        }

        if ($delete_keys) {
            $this->newQuery()
                ->whereIn($this->key, $delete_keys)
                ->delete();
        }
    }

    /**
     * Transforms settings data into an array ready to be insterted into the
     * database. Call array_dot on a multidimensional array before passing it
     * into this method!
     *
     * @param array $data Call array_dot on a multidimensional array before passing it into this method!
     *
     * @return array
     */
    protected function prepareInsertData(array $data)
    {
        $db_data = [];

        if ($this->getExtraColumns()) {
            foreach ($data as $key => $value) {
                $db_data[] = array_merge(
                    $this->getExtraColumns(),
                    [$this->key => $key, $this->value => $value]
                );
            }
        } else {
            foreach ($data as $key => $value) {
                $db_data[] = [$this->key => $key, $this->value => $value];
            }
        }

        return $db_data;
    }

    /**
     * {@inheritdoc}
     */
    protected function read()
    {
        return $this->parseReadData($this->newQuery()->get());
    }

    /**
     * Parse data coming from the database.
     *
     * @param array $data
     *
     * @return array
     */
    public function parseReadData($data)
    {
        $results = [];

        foreach ($data as $row) {
            if (is_array($row)) {
                $key = $row[$this->key];
                $value = $row[$this->value];
            } elseif (is_object($row)) {
                $key = $row->{$this->key};
                $value = $row->{$this->value};
            } else {
                $msg = 'Expected array or object, got ' . gettype($row);
                throw new \UnexpectedValueException($msg);
            }

            Arr::set($results, $key, $value);
        }

        return $results;
    }

    /**
     * Create a new query builder instance.
     *
     * @param bool $insert
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newQuery($insert = false)
    {
        $query = $this->connection->table($this->table);

        if (!$insert) {
            foreach ($this->getExtraColumns() as $key => $value) {
                $query->where($key, '=', $value);
            }
        }

        if ($this->query_constraint !== null) {
            $callback = $this->query_constraint;
            $callback($query, $insert);
        }

        return $query;
    }
}
