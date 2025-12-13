<?php

namespace components\ai;

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

    public function setSchema(Schema $schema): static {
        $this->set('text', $schema->toFormat());
        return $this;
    }
}