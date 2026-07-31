<?php

namespace core\view_old;

use JsonSerializable;

interface FormatAble extends View, JsonSerializable {
    public function toText(): string;

    public function toHtml(): string;

    public function toJson(): string;

    public function toXml(): string;
}