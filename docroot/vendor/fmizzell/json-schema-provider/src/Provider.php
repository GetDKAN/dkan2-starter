<?php

namespace JsonSchemaProvider;

use Contracts\Retriever;
use JsonSchema\Validator;

class Provider implements Retriever {

    private $schemaStorage;

    public function __construct(Retriever $schema_storage)
    {
        $this->schemaStorage = $schema_storage;
    }

    public function retrieve(string $id): ?string
    {
        try {
            $schema_string = $this->schemaStorage->retrieve($id);
        }
        catch(\Exception $e) {
            throw new \Exception("A schema {$id} does not exist.");
        }

        if ($schema = json_decode($schema_string)) {

            if ($this->schemaIsObjectAndContainsMetaSchema($schema)) {
                $meta_schema_url = $schema->{'$schema'};

                // Validate
                $validator = new Validator();
                $validator->validate($schema, (object)['$ref' => $meta_schema_url]);

                if ($validator->isValid()) {
                    return $schema_string;
                } else {
                    $errors = "";
                    foreach ($validator->getErrors() as $error) {
                        $errors .= sprintf("[%s] %s\n", $error['property'], $error['message']) . PHP_EOL;
                    }
                    throw new \Exception("The requested schema is not valid" . PHP_EOL . $errors);
                }

            }
            else {
                throw new \Exception('The requested schemas is not an object with a reference to a valid meta-schema (ex. { "$schema": "http://json-schema.org/draft-07/schema#" })');
            }
        }
        else {
            throw new \Exception("The requested schema is not valid JSON");
        }

    }

    private function schemaIsObjectAndContainsMetaSchema($schema) {
        return (is_object($schema) && isset($schema->{'$schema'}) && substr_count($schema->{'$schema'}, "http://json-schema.org/draft-") > 0);
    }


}