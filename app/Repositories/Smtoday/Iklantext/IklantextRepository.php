<?php

namespace Vanguard\Repositories\Smtoday\Iklantext;

interface IklantextRepository
{
    /**
     * Get all system iklantexts.
     *
     * @return mixed
     */
    public function all();

    /**
     * Finds the iklantext by given id.
     *
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * Creates new iklantext from provided data.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Updates specified iklantext.
     *
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Remove specified iklantext from repository.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id);
}
