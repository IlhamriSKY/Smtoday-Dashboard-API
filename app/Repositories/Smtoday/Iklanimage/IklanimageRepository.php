<?php

namespace Vanguard\Repositories\Smtoday\Iklanimage;

interface IklanimageRepository
{
    /**
     * Get all system iklanimages.
     *
     * @return mixed
     */
    public function all();

    /**
     * Finds the iklanimage by given id.
     *
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * Creates new iklanimage from provided data.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Updates specified iklanimage.
     *
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Remove specified iklanimage from repository.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id);
}
