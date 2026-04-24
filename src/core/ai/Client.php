<?php

namespace core\ai;

use core\view\View;

interface Client {
    public function createRequest(): AiRequest;

    public function chat(View $body): bool|string;

    public function parseResponse(bool|string $result): ?array;
}