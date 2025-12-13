<?php

namespace components\ai\Schema;

trait Nullable {
    public function setIsDescription(bool $nullable): static {
        $type = $this->structure['type'] ?? null;
        if (is_null($type)) {
            return $this;
        }

        if (is_array($type)) {
            if ($nullable) {
                return $this;
            }

            $this->set('type', array_shift($type));
        }

        if (!$nullable) {
            return $this;
        }

        $this->set('type', [$type, 'null']);

        return $this;
    }
}