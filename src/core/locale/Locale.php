<?php

namespace core\locale;

use core\App;

abstract class Locale {
    public static function autoload(): void {
        App::getInstance()
            ->addLocale(new static());
    }

    abstract public function getIdentifier(): string;

    abstract public function getName(): string;

    public function compare(string $a, string $b): int {
        return strcmp($a, $b);
    }

    public function formatNumber(int|float $number): string {
        return (string)$number;
    }

    public function formatPhone(string $number): string {
        return $number;
    }

    public function formatPostal(string $code): string {
        return $code;
    }

    abstract public function getCurrencyCode(): string;

    public function formatPrice(int $number): string {
        return (string)$number;
    }

    public function formatDate(int $timestamp): string {
        return date('Y-m-d', $timestamp);
    }

    public function formatDateRelative(int $timestamp): string {
        return date('Y-m-d H:i:s', $timestamp);
    }
}