<?php

use Swaggest\JsonSchema\Schema;

class ProviderTest extends \PHPUnit\Framework\TestCase
{

    public function testBasJsonSchema() {
        $provider = new \JsonSchemaProvider\Provider(new TestSchemaRetriever());

        $this->expectExceptionMessage("The requested schema is not valid JSON");

        $provider->retrieve("badjson");
    }

    public function testNotObjectSchema() {
        $provider = new \JsonSchemaProvider\Provider(new TestSchemaRetriever());

        $this->expectExceptionMessage('The requested schemas is not an object with a reference to a valid meta-schema (ex. { "$schema": "http://json-schema.org/draft-07/schema#" })');

        $provider->retrieve("string");
    }

    public function testNoMetaSchemaSchema() {
        $provider = new \JsonSchemaProvider\Provider(new TestSchemaRetriever());

        $this->expectExceptionMessage('The requested schemas is not an object with a reference to a valid meta-schema (ex. { "$schema": "http://json-schema.org/draft-07/schema#" })');

        $provider->retrieve("nometa");
    }

    public function testBadMetaSchemaSchema() {
        $provider = new \JsonSchemaProvider\Provider(new TestSchemaRetriever());

        $this->expectExceptionMessage('The requested schemas is not an object with a reference to a valid meta-schema (ex. { "$schema": "http://json-schema.org/draft-07/schema#" })');

        $provider->retrieve("badmeta");
    }

    public function testInvalidAgainstMetaSchema() {
        $provider = new \JsonSchemaProvider\Provider(new TestSchemaRetriever());

        $this->expectExceptionMessage('The requested schema is not valid');

        $provider->retrieve("invalidagainstmeta");
    }

    public function testValidSchema() {
        $provider = new \JsonSchemaProvider\Provider(new TestSchemaRetriever());

        $schema = <<<'JSON'
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {}
}
JSON;


        $this->assertEquals($schema, $provider->retrieve("valid"));
    }


}

class TestSchemaRetriever implements \Contracts\Retriever {
    public function retrieve(string $id): ?string {
        $file_path = __DIR__ . "/data/{$id}.json";
        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }
        else {
            throw new \Exception("Requested schema {$id} does not exist.");
        }
    }

}