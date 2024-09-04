<?php

namespace core;

interface Module {
    public function load(App $app): void;
}
