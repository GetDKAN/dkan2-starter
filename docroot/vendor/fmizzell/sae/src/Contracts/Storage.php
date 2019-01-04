<?php

namespace Sae\Contracts;

interface Storage
{
  /**
   * Retrieve.
   *
   * @param string $id
   *   The identifier for the data.
   *
   * @return string
   *   The data or null if no data could be retrieved.
   *
   * @throws \Exception
   *   No data matched the identifier.
   */
  public function retrieve($id);

  /**
   * Store.
   *
   * @param string $data
   *   The data to be stored.
   * @param string $id
   *   The identifier for the data. If the act of storing generates the
   *   id, there is no need to pass one.
   *
   * @return string
   *   The identifier.
   *
   * @throws \Exception
   *   Issues storing the data.
   */
  public function store($data, $id = null);

  /**
   * Remove.
   *
   * @param string $id
   *   The identifier for the data.
   */
  public function remove($id);
}