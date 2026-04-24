<?php

namespace core\ai;

use components\ai\InputMessage;
use components\ai\Schema\Schema;
use core\ResourceLoader;
use core\view\JsonStructure;

class AiRequest extends JsonStructure {
    use ResourceLoader;



    /** @var array<InputMessage> */
    protected array $messages;

    public function __construct(string $model) {
        $this->messages = [];
        parent::__construct([
            'model' => $model,
        ]);

        $this->setRef('input', $this->messages);
    }



    public function add(InputMessage $message): static {
        $this->messages[] = $message;
        return $this;
    }

    public function addJsonFormat(): static {
        return $this->set('text', ["format" => ["type" => "json_object"]]);
    }

    public function setSchema(Schema $schema): static {
        $this->set('text', $schema->toFormat());
        return $this;
    }
}