<?php

namespace Sae;

use Sae\Contracts\BulkRetriever;
use Sae\Contracts\Storage;
use Sae\Contracts\IdGenerator;

/**
 * Class Sae.
 *
 * The Services API Egine coordinates the interactions
 * between data validation and manipulating the
 * data appropriately.
 *
 * It supports this interactions for the http verbs:
 * GET, POST, PUT, PATCH and DELETE.
 *
 * @package Sae
 */
class Sae
{
  /**
   * @var Storage
   */
  private $storage;
  private $jsonSchema;

  /**
   * @var IdGenerator
   */
  private $idGenerator;

  public function __construct(Storage $storage, $json_schema) {
    $this->storage = $storage;
    $this->jsonSchema = $json_schema;
  }

  public function setIdGenerator(IdGenerator $id_generator) {
    $this->idGenerator = $id_generator;
  }

  /**
   * Get.
   *
   * @param string $id
   *   The identifier for the data we are getting.
   *
   * @return string
   *   The data.
   *
   * @throws \Exception
   *   No data with the identifier was found, or the storage
   *   does not support bulk retrieval of data.
   */
  public function  get($id = null) {
    if (isset($id)) {
      return $this->storage->retrieve($id);
    }
    else {
      if ($this->storage instanceof BulkRetriever) {
        return $this->storage->retrieveAll();
      }

    }
  }

  /**
   * Post.
   *
   * @param string $json_data
   *   The data as a json string.
   *
   * @return string
   *   The identifier for the data.
   *
   * @throws \Exception
   *   If the data is invalid, or could not be stored.
   */
  public function post($json_data) {

    $validation_info = $this->validate($json_data);
    if (!$validation_info['valid']) {
      throw new \Exception(json_encode((object) $validation_info));
    }

    $id = Null;
    if ($this->idGenerator) {
      $id = $this->idGenerator->generate();
    }
    return $this->storage->store($json_data, $id);
  }

  public function  put($id, $json_data) {
    if (!$this->validate($json_data)) {
      return FALSE;
    }

    return $this->storage->store($json_data, $id);
  }

  public function  patch($id, $json_data) {
    $json_data_original = $this->storage->retrieve($id);
    $data_original = (array) json_decode($json_data_original);
    $data = (array) json_decode($json_data);

    $new = json_encode((object) array_merge($data_original, $data));

    if (!$this->validate($new)) {
      return FALSE;
    }

    return $this->storage->store($new, $id);

  }

  public function  delete($id) {
    return $this->storage->remove($id);
  }

  public function validate($json_data) {
    $data = json_decode($json_data);

    $validator = new \JsonSchema\Validator;
    $validator->validate($data, json_decode($this->jsonSchema));

    $is_valid = $validator->isValid();

    return ['valid' => $is_valid, 'errors' => $validator->getErrors()];
  }
}