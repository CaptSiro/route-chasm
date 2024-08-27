<?php

namespace core\path\parser;

use core\path\Part;
use core\path\PartType;
use core\path\Path;
use core\path\Segment;
use Exception;

class Parser {
    /**
     * @throws Exception
     */
    static function parse(string $path): Path {
        $p = new Path();
        $segment = new Segment();

        /** @var Token[] $tokens */
        $tokens = [...(new Tokenizer($path))->tokenize()];
        $count = count($tokens);

        for ($pos = 0; $pos < $count; $pos++) {
            $literal = $tokens[$pos]->literal;

            switch ($tokens[$pos]->type) {
                case TokenType::BRACKET_L: {
                    if (($pos + 2 > $count)
                        || !($tokens[$pos + 1]->type === TokenType::IDENT && $tokens[$pos + 2]->type === TokenType::BRACKET_R)) {
                        throw new Exception("Illegal token '$literal'");
                    }

                    $ident = $tokens[$pos + 1]->literal;

                    if (!Ident::validate($ident)) {
                        throw new Exception("'$ident' is not valid parameter name");
                    }

                    $segment->addPart(new Part(PartType::DYNAMIC, $ident));
                    $pos += 2;
                    break;
                }

                case TokenType::IDENT: {
                    $segment->addPart(new Part(PartType::STATIC, $tokens[$pos]->literal));
                    break;
                }

                case TokenType::SLASH: {
                    if (isset($tokens[$pos - 1]) && $tokens[$pos - 1]->type === TokenType::SLASH) {
                        throw new Exception("Illegal token '$literal'");
                    }

                    if (empty($segment->getParts())) {
                        break;
                    }

                    $p->addSegment($segment);
                    $segment = new Segment();
                    break;
                }

                case TokenType::BRACKET_R: throw new Exception("Unexpected token '$literal'");
                case TokenType::ILLEGAL: throw new Exception("Illegal token '$literal'");
                case TokenType::EOF: {
                    if (empty($segment->getParts())) {
                        break 2;
                    }

                    $p->addSegment($segment);
                    break 2;
                }
            }
        }

        return $p;
    }
}