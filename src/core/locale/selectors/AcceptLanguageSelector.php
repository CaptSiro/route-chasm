<?php

namespace core\locale\selectors;

use core\communication\Request;
use core\http\HttpHeader;
use core\locale\LanguageSelector;

class AcceptLanguageSelector implements LanguageSelector {
    public function select(Request $request): ?string {
        $value = $request->getHeader(HttpHeader::ACCEPT_LANGUAGE);
        if (is_null($value)) {
            return null;
        }

        $parts = preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false) {
            return null;
        }

        $candidates = [];
        $order = 0;

        foreach ($parts as $part) {
            if (!preg_match(
                '/^(?<tag>\*|[A-Za-z0-9]{1,8}(?:-[A-Za-z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(?<q>0(?:\.\d{1,3})?|1(?:\.0{1,3})?))?$/',
                $part,
                $matches
            )) {
                continue;
            }

            $tag = $matches['tag'];
            $q = isset($matches['q']) && $matches['q'] !== ''
                ? (float)$matches['q']
                : 1.0;

            if ($tag !== '*') {
                $subs = explode('-', $tag);

                foreach ($subs as $i => $sub) {
                    if ($i === 0) {
                        $subs[$i] = strtolower($sub);
                        continue;
                    }

                    if (strlen($sub) === 4 && ctype_alpha($sub)) {
                        $subs[$i] = ucfirst(strtolower($sub));
                        continue;
                    }

                    $isRegionCode = (strlen($sub) === 2 && ctype_alpha($sub))
                        || (strlen($sub) === 3 && ctype_digit($sub));
                    if ($isRegionCode) {
                        $subs[$i] = strtoupper($sub);
                        continue;
                    }

                    $subs[$i] = strtolower($sub);
                }

                $tag = implode('-', $subs);
            }

            $specificity = $tag !== '*'
                ? substr_count($tag, '-') + 1
                : 0;

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

        uasort($candidates, function ($a, $b) {
            return $b['q'] <=> $a['q']
                ?: $b['spec'] <=> $a['spec']
                    ?: $a['ord'] <=> $b['ord'];
        });

        foreach ($candidates as $tag => $_) {
            if ($tag !== '*') {
                return $tag;
            }
        }

        return null;
    }
}