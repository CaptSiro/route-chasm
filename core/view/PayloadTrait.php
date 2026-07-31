<?php

namespace core\view;

trait PayloadTrait {
    protected array $properties = [];



    // RendererPayload
    public function getViewReference(): View {
        return $this;
    }

    public function getProperty(string $property): mixed {
        return $this->properties[$property] ?? null;
    }

    public function getPropertyBoolean(string $property, bool $default = false): bool {
        if (!is_bool($ret = $this->properties[$property] ?? $default)) {
            throw new InvalidPropertyException($property, 'boolean');
        }

        return $ret;
    }

    public function getPropertyArray(string $property): array {
        if (!is_array($ret = $this->getProperty($property) ?? [])) {
            throw new InvalidPropertyException($property, 'array');
        }

        return $ret;
    }

    public function setProperty(string $property, mixed $value): static {
        $this->properties[$property] = $value;
        return $this;
    }

    public function hasProperty(string $property): bool {
        return isset($this->properties[$property]);
    }

    public function allProperties(): array {
        return $this->properties;
    }

    public function addProperty(string $arrayProperty, mixed $value, ?string $key): static {
        $array = $this->getPropertyArray($arrayProperty);

        if (is_null($key)) {
            $array[] = $value;
        } else {
            $array[$key] = $value;
        }

        $this->setProperty($arrayProperty, $array);
        return $this;
    }

    public function addAllProperties(string $arrayProperty, array $values): static {
        $array = $this->getPropertyArray($arrayProperty);
        $this->setProperty($arrayProperty, array_merge($array, $values));
        return $this;
    }
}