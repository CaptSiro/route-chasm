<?php

namespace components\core\Admin\PrivilegeResourceMap;

use core\view\Renderer;
use core\view\View;
use models\core\Privilege\Privilege;
use models\core\UserResource;

class PrivilegeResourceMap implements View {
    use Renderer;



    public static function createPosition(Privilege $privilege, UserResource $resource): string {
        return $privilege->id .'-'. $resource->id;
    }

    public static function createPositionRaw(int $privilegeId, int $resourceId): string {
        return $privilegeId .'-'. $resourceId;
    }

    public static function extractMap(array $body, string $name): array {
        $mappings = [];
        $offset = strlen($name .'_');

        foreach ($body as $key => $value) {
            if (str_starts_with($key, $name .'_')) {
                $mappings[substr($key, $offset)] = json_decode($value);
            }
        }

        return $mappings;
    }



    /**
     * @param array<Privilege> $privileges
     * @param array<UserResource> $resources
     * @param array<string, boolean> $map
     */
    public function __construct(
        protected array $privileges,
        protected array $resources,
        protected array $map,
        protected string $mappingName
    ) {}



    public function isset(Privilege $privilege, UserResource $resource): bool {
        $position = static::createPosition($privilege, $resource);
        return isset($this->map[$position])
            && $this->map[$position];
    }

    public function createColumnTemplate(): string {
        return trim(str_repeat('1fr ', 1 + count($this->privileges)));
    }
}