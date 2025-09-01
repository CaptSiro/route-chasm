<?php

namespace core\locale\selectors;

use core\communication\Request;
use core\http\HttpHeader;
use core\locale\LocaleSelector;

class AcceptLanguageSelector implements LocaleSelector {
    public function select(Request $request): ?string {
        $value = $request->getHeader(HttpHeader::ACCEPT_LANGUAGE);
        if (is_null($value)) {
            return null;
        }

        // Split by commas, trim whitespace
        $parts = preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false) {
            return null;
        }

        $candidates = [];
        $order = 0;

        foreach ($parts as $part) {
            // Match: lang-range [";" "q" "=" qvalue]
            // lang-range: "*" or token subtags joined by "-"
            // qvalue: 0..1 with up to 3 decimals (loosely validated)
            if (!preg_match(
                '/^(?<tag>\*|[A-Za-z0-9]{1,8}(?:-[A-Za-z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(?<q>(?:0(?:\.\d{1,3})?|1(?:\.0{1,3})?)))?$/',
                $part,
                $matches
            )) {
                continue; // skip invalid
            }

            $tag = $matches['tag'];
            $q = isset($matches['q']) && $matches['q'] !== ''
                ? (float)$matches['q']
                : 1.0;

            // Normalize tag to BCP-47-ish casing (lang lower, region upper, script Title)
            // Keep "*" as-is.
            if ($tag !== '*') {
                $subs = explode('-', $tag);

                foreach ($subs as $i => $sub) {
                    if ($i === 0) {
                        // language
                        $subs[$i] = strtolower($sub);
                        continue;
                    }

                    if (strlen($sub) === 4 && ctype_alpha($sub)) {
                        // script
                        $subs[$i] = ucfirst(strtolower($sub));
                        continue;
                    }

                    $isRegionCode = (strlen($sub) === 2 && ctype_alpha($sub))
                        || (strlen($sub) === 3 && ctype_digit($sub));
                    if ($isRegionCode) {
                        $subs[$i] = strtoupper($sub);
                        continue;
                    }

                    // variants/extensions
                    $subs[$i] = strtolower($sub);
                }

                $tag = implode('-', $subs);
            }

            // Specificity: number of sub tags (more is better), "*" gets 0
            $specificity = $tag !== '*'
                ? substr_count($tag, '-') + 1
                : 0;

            // Keep the best (highest q, then higher specificity, then earlier order)
            // If the same tag appears multiple times, keep the best scoring one.
            if (!isset($candidates[$tag])) {
                $candidates[$tag] = ['q' => $q, 'spec' => $specificity, 'ord' => $order++];
                continue;
            }

            $previous = $candidates[$tag];
            $isPreferredCandidate = (
                $q > $previous['q']
                || ($q === $previous['q'] && $specificity > $previous['spec'])
                || ($q === $previous['q'] && $specificity === $previous['spec'] && $order < $previous['ord'])
            );

            if ($isPreferredCandidate) {
                $candidates[$tag] = ['q' => $q, 'spec' => $specificity, 'ord' => $order++];
            }
        }

        if (!$candidates) {
            return null;
        }

        // Sort: q desc, specificity desc, order asc
        uasort($candidates, function ($a, $b) {
            return $b['q'] <=> $a['q']
                ?: $b['spec'] <=> $a['spec']
                    ?: $a['ord'] <=> $b['ord'];
        });

        // Return the first non-wildcard tag
        foreach ($candidates as $tag => $_) {
            if ($tag !== '*') {
                return $tag;
            }
        }

        // Only "*" matched
        return null;
    }
}