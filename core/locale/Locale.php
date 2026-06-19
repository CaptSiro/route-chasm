<?php

namespace core\locale;

use core\App;
use Transliterator;

abstract class Locale {
    public static function autoload(): void {
        App::getInstance()
            ->addLocale(new static());
    }



    public function __toString(): string {
        return $this->getIdentifier();
    }



    abstract public function getIdentifier(): string;

    abstract public function getName(): string;

    public function getLongIdentifier(): string {
        return '['. $this->getIdentifier() .'] '. $this->getName();
    }

    public function compare(string $a, string $b): int {
        return strcmp($a, $b);
    }

    public function formatUrlSegment(string $text): string {
        if ($text === "") {
            return "";
        }

        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC');

        $ascii = $transliterator
            ? $transliterator->transliterate($text)
            : $text;

        $slug = preg_replace('~[^A-Za-z0-9]+~', '-', $ascii);
        $slug = trim($slug, '-');
        $slug = strtolower($slug);

        return $slug !== ''
            ? $slug
            : '';
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

    public function formatDateTime(int $timestamp): string {
        return date('Y-m-d H:i:s', $timestamp);
    }

    public function formatDateRelative(int $timestamp): string {
        return date('Y-m-d H:i:s', $timestamp);
    }
}