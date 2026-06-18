<?php

namespace core\view;

use JsonSerializable;

interface FormatAble extends View, JsonSerializable {
    public function toText(): string;

    public function toHtml(): string;

    public function toJson(): string;
}