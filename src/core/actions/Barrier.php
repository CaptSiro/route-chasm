<?php

namespace core\actions;

use core\App;
use core\communication\Request;
use models\core\Privilege\Privilege;
use models\core\User\User;
use models\core\UserResource;

trait Barrier {
    protected ?UserResource $userResource = null;

    public function setUserResource(?UserResource $userResource = null): static {
        $this->userResource = $userResource;
        return $this;
    }

    public function getUserResource(): ?UserResource {
        return $this->userResource;
    }

    public function hasAccess(User $user, Privilege $privilege): bool {
        if (is_null($this->userResource)) {
            return true;
        }

        return $user->hasAccess($this->userResource, $privilege);
    }

    public function hasRequestAccess(Privilege $privilege, ?Request $request = null): bool {
        if (is_null($this->userResource)) {
            return true;
        }

        $request ??= App::getInstance()->getRequest();
        return $this->hasAccess(User::fromRequest($request), $privilege);
    }
}