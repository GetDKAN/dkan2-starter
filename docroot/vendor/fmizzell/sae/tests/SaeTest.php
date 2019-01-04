<?php

class SaeTest extends \PHPUnit\Framework\TestCase
{
  private $jsonSchema = '
    {
       "$schema": "http://json-schema.org/draft-04/schema#",
       "title": "Product",
       "description": "A product from Acme\'s catalog",
       "type": "object",
      
       "properties": {
      
          "id": {
             "description": "The unique identifier for a product",
             "type": "integer"
          },
        
          "name": {
             "description": "Name of the product",
             "type": "string"
          },
        
          "price": {
             "type": "number",
             "minimum": 0,
             "exclusiveMinimum": true
          }
       },
      
       "required": ["id", "name", "price"]
    }
    ';

  private $engine;

  protected function setUp() {
    $this->engine = new Sae\Sae(new Memory(), $this->jsonSchema);
    $this->engine->setIdGenerator(new Sequential());
  }

  public function test() {

    $engine = $this->engine;

    // Can not retrieve what is not there.
    $this->assertFalse($engine->get(1));

    // Can retriever an empty set.
    $data = $engine->get();
    $decoded = json_decode($data);
    $this->assertEmpty($decoded);


    // Can post valid data.
    $json_object = '
    {
      "id": 1, 
      "name": "friend", 
      "price": 20
    }
    ';
    $this->assertEquals(1, $engine->post($json_object));

    $json_object2 = '
    {
      "id": 2, 
      "name": "foe", 
      "price": 2
    }
    ';
    $this->assertEquals(2, $engine->post($json_object2));

    // Posted data can be retrieved.
    $this->assertEquals($json_object2, $engine->get(2));

    // Objects can be retrived in bulk.
    $this->assertEquals("[". $json_object . "," . $json_object2 ."]", $engine->get());

    // PUT works.
    $json_object = '
    {
      "id": 2, 
      "name": "enemy", 
      "price": 40
    }
    ';

    $this->assertTrue($engine->put(1, $json_object));

    // Confirm that PUT worked by retrieving the new object.
    $this->assertEquals($json_object, $engine->get(1));

    // PATCH works.
    $json_object = '{"id":2,"name":"enemy","price":50}';

    $json_patch = '
    { 
      "price": 50
    }
    ';

    $this->assertTrue($engine->patch(1, $json_patch));

    // Confirm that PATCH worked by retrieving the new object.
    $this->assertEquals($json_object, $engine->get(1));

    // DELETE works
    $this->assertTrue($engine->delete(1));

    // Confirm that DELETE worked by retrieving the object.
    $this->assertFalse($engine->get(1));
  }

  public function testPostException() {
    $engine = $this->engine;

    // Can not post invalid data.
    $this->expectExceptionMessage("{\"valid\":false,\"errors\":[{\"property\":\"id\",\"pointer\":\"\/id\",\"message\":\"The property id is required\",\"constraint\":\"required\",\"context\":1},{\"property\":\"name\",\"pointer\":\"\/name\",\"message\":\"The property name is required\",\"constraint\":\"required\",\"context\":1},{\"property\":\"price\",\"pointer\":\"\/price\",\"message\":\"The property price is required\",\"constraint\":\"required\",\"context\":1}]}");
    $this->assertFalse($engine->post("{}"));
  }
}

class Memory implements \Sae\Contracts\Storage, \Sae\Contracts\BulkRetriever {
  private $storage = [];

  public function retrieve($id)
  {
    if (isset($this->storage[$id])) {
      return $this->storage[$id];
    }
    return FALSE;
  }

  public function retrieveAll()
  {
    return "[" . implode(",", $this->storage) . "]";
  }

  public function store($data, $id = NULL)
  {
    if (!isset($this->storage[$id])) {
      $this->storage[$id] = $data;
      return $id;
    }
    $this->storage[$id] = $data;
    return TRUE;
  }

  public function remove($id)
  {
    if (isset($this->storage[$id])) {
      unset($this->storage[$id]);
      return TRUE;
    }
    return FALSE;
  }
}

class Sequential implements \Sae\Contracts\IdGenerator {
  private $id = 0;
  public function generate() {
    $this->id++;
    return $this->id;
  }
}