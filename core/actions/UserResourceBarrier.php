<?php

namespace core\actions;

use core\communication\Request;
use models\Privilege\Privilege;
use models\User\User;
use models\UserResource;

interface UserResourceBarrier {
    public function setUserResource(?UserResource $userResource = null): static;

    public function getUserResource(): ?UserResource;

    public function hasAccess(User $user, Privilege $privilege): bool;

    public function hasRequestAccess(Privilege $privilege, ?Request $request = null): bool;
}