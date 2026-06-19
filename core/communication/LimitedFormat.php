<?php

namespace core\communication;

interface LimitedFormat {
    public function getIdentifier(Request $request): string;
}