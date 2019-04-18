<?php

declare(strict_types=1);

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

          "dimensions": {
            "type": "object",
            "properties": {
              "length": {
                "type": "number"
              },
              "width": {
                "type": "number"
              },
              "height": {
                "type": "number"
              }
            },
            "required": [ "length", "width", "height" ]
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
    $this->assertNull($engine->get("1"));

    // Can retrieve an empty set.
    $data = $engine->get();
    $this->assertEmpty($data);


    // Can post valid data.
    $json_object1 = '
    {
      "id": 1, 
      "name": "friend", 
      "price": 20
    }
    ';
    $this->assertEquals(1, $engine->post($json_object1));

    $json_object2 = '
    {
      "id": 2, 
      "name": "foe", 
      "price": 2
    }
    ';
    $this->assertEquals(2, $engine->post($json_object2));

    // Posted data can be retrieved.
    $this->assertEquals($json_object2, $engine->get("2"));

    // Objects can be retrived in bulk.
    $counter = 1;
    foreach ($engine->get() as $object) {
      $object_name = "json_object{$counter}";
      $this->assertEquals(${$object_name}, $object);
      $counter++;
    }

    // PUT works.
    $json_object = '
    {
      "id": 2, 
      "name": "enemy", 
      "price": 40,
      "dimensions": {
        "length": 5,
        "width": 5,
        "height": 8
      }
    }
    ';

    $this->assertEquals(1, $engine->put("1", $json_object));

    // Confirm that PUT worked by retrieving the new object.
    $this->assertEquals($json_object, $engine->get("1"));

    // PATCH works.
    $json_object = '{"id":2,"name":"enemy","price":50,"dimensions":{"length":10,"width":5,"height":8}}';

    $json_patch = '
    { 
      "dimensions": {
        "length": 10
      },
      "price": 50
    }
    ';

    $this->assertEquals("1", $engine->patch("1", $json_patch));

    // Confirm that PATCH worked by retrieving the new object.
    $this->assertEquals($json_object, $engine->get("1"));

    // DELETE works
    $this->assertTrue($engine->delete("1"));

    // Confirm that DELETE worked by retrieving the object.
    $this->assertNull($engine->get("1"));
  }

  public function testPostException() {
    $engine = $this->engine;

    // Can not post invalid data.
    $this->expectExceptionMessage("{\"valid\":false,\"errors\":[{\"property\":\"id\",\"pointer\":\"\/id\",\"message\":\"The property id is required\",\"constraint\":\"required\",\"context\":1},{\"property\":\"name\",\"pointer\":\"\/name\",\"message\":\"The property name is required\",\"constraint\":\"required\",\"context\":1},{\"property\":\"price\",\"pointer\":\"\/price\",\"message\":\"The property price is required\",\"constraint\":\"required\",\"context\":1}]}");
    $this->assertFalse($engine->post("{}"));
  }
}

class Memory implements \Contracts\Storage, \Contracts\BulkRetriever {
  private $storage = [];

  public function retrieve(string $id): ?string
  {
    if (isset($this->storage[$id])) {
      return $this->storage[$id];
    }
    return NULL;
  }

  public function retrieveAll(): array
  {
    return $this->storage;
  }

  public function store(string $data, string $id = NULL): string
  {
    if (!isset($this->storage[$id])) {
      $this->storage[$id] = $data;
      return $id;
    }
    $this->storage[$id] = $data;
    return $id;
  }

  public function remove(string $id)
  {
    if (isset($this->storage[$id])) {
      unset($this->storage[$id]);
      return TRUE;
    }
    return FALSE;
  }
}

class Sequential implements \Contracts\IdGenerator {
  private $id = 0;
  public function generate() {
    return ++$this->id;
  }
}