<?php

namespace modules\locales;

use core\locale\Locale;
use core\utils\Regex;

class EnglishUS extends Locale {
    public const RULE_SINGULAR = Regex::LOCALE_RULE_ONE;
    public const RULE_PLURAL = Regex::LOCALE_RULE_TWO_AND_UP;

    /**
     * @param string $singular
     * @param string $plural
     * @return array<string>
     */
    public static function pluralize(string $singular, string $plural): array {
        return [
            self::RULE_SINGULAR => $singular,
            self::RULE_PLURAL => $plural
        ];
    }



    public function getIdentifier(): string {
        return 'en-US';
    }

    public function getName(): string {
        return 'English (United States)';
    }

    public function formatNumber(int|float $number): string {
        return number_format($number);
    }

    /**
     * @param string $number Continuous string of numbers or characters
     * @return string
     */
    public function formatPhone(string $number): string {
        // Basic US formatting: (XXX) XXX-XXXX
        $digits = preg_replace('/\D/', '', $number);
        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s',
                substr($digits, 0, 3),
                substr($digits, 3, 3),
                substr($digits, 6, 4)
            );
        }

        return $number;
    }

    /**
     * @param string $code Continuous string of numbers or characters
     * @return string
     */
    public function formatPostal(string $code): string {
        // US ZIP code formatting: 12345 or 12345-6789
        $digits = preg_replace('/\D/', '', $code);
        if (strlen($digits) === 9) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5);
        }

        return $code;
    }

    public function getCurrencyCode(): string {
        return 'USD';
    }

    public function formatPrice(int $number): string {
        return '$' . number_format($number, 2);
    }

    public function formatDate(int $timestamp): string {
        return date('m/d/Y', $timestamp);
    }

    public function formatDateTime(int $timestamp): string {
        // US format: 09/01/2025 02:05 PM
        return date('m/d/Y h:i A', $timestamp);
    }

    public function formatDateRelative(int $timestamp): string {
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'just now';
        }

        if ($diff < 3600) {
            return intval($diff / 60) . ' minutes ago';
        }

        if ($diff < 86400) {
            return intval($diff / 3600) . ' hours ago';
        }

        if ($diff < 604800) {
            return intval($diff / 86400) . ' days ago';
        }

        if ($diff < 31556926) {
            return intval($diff / 604800) . ' months ago';
        }

        return intval($diff / 31556926) . ' years ago';
    }
}