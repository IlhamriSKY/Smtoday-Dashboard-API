<?php

namespace Vanguard\Repositories\Smtoday\Beritatext;

interface BeritatextRepository
{
    /**
     * Get all system Beritatexts.
     *
     * @return mixed
     */
    public function all();

    /**
     * Finds the Beritatext by given id.
     *
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * Creates new Beritatext from provided data.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Updates specified Beritatext.
     *
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Remove specified Beritatext from repository.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id);
}
