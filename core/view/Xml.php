<?php

namespace core\view;

class Xml {
    public static function escape(string $xml): string {
        return htmlspecialchars($xml, ENT_XML1);
    }
}