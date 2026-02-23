<?php

namespace modules\locales;

use core\locale\Locale;
use core\utils\Regex;

class Czech extends Locale {
    public const RULE_SINGULAR = Regex::LOCALE_RULE_ONE;
    public const RULE_FEW = Regex::LOCALE_RULE_TWO_TO_FOUR;
    public const RULE_PLURAL = Regex::LOCALE_RULE_FIVE_AND_UP;



    public function getIdentifier(): string {
        return 'cs-CZ';
    }

    public function getName(): string {
        return 'Čeština';
    }

    public function formatNumber(int|float $number): string {
        // Czech uses space as a thousand separator and comma as decimal separator
        return number_format($number, 0, ',', ' ');
    }

    /**
     * @param string $number Continuous string of numbers or characters
     * @return string
     */
    public function formatPhone(string $number): string {
        // Basic Czech formatting: +420 XXX XXX XXX
        $digits = preg_replace('/\D/', '', $number);

        if (strlen($digits) === 9) {
            return sprintf('%s %s %s',
                substr($digits, 0, 3),
                substr($digits, 3, 3),
                substr($digits, 6, 3)
            );
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '420')) {
            return sprintf('+420 %s %s %s',
                substr($digits, 3, 3),
                substr($digits, 6, 3),
                substr($digits, 9, 3)
            );
        }

        return $number;
    }

    /**
     * @param string $code Continuous string of numbers or characters
     * @return string
     */
    public function formatPostal(string $code): string {
        // Czech postal code formatting: 123 45
        $digits = preg_replace('/\D/', '', $code);

        if (strlen($digits) === 5) {
            return substr($digits, 0, 3) . ' ' . substr($digits, 3);
        }

        return $code;
    }

    public function getCurrencyCode(): string {
        return 'CZK';
    }

    public function formatPrice(int $number): string {
        // Czech format: 1 234,00 Kč
        return number_format($number, 2, ',', ' ') . ' Kč';
    }

    public function formatDate(int $timestamp): string {
        // Czech format: 1. 9. 2025
        return date('j. n. Y', $timestamp);
    }

    public function formatDateTime(int $timestamp): string {
        // Czech format: 1. 9. 2025 14:05
        return date('j. n. Y H:i', $timestamp);
    }

    public function formatDateRelative(int $timestamp): string {
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'právě teď';
        }

        if ($diff < 3600) {
            return 'před '. intval($diff / 60) .' minuty';
        }

        if ($diff < 86400) {
            return 'před '. intval($diff / 3600) .' hodinami';
        }

        if ($diff < 604800) {
            return 'před '. intval($diff / 86400) .' dny';
        }

        if ($diff < 2592000) {
            return 'před '. intval($diff / 604800) .' týdny';
        }

        if ($diff < 31556926) {
            return 'před '. intval($diff / 2592000) .' měsíci';
        }

        return 'před '. intval($diff / 31556926) .' lety';
    }
}