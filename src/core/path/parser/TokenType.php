<?php

namespace core\path\parser;

enum TokenType: string {

    case IDENT = "ident";

    case ANY = "any";

    case ANY_TERMINATOR = "any_terminator";

    case BRACKET_L = "bracket_l";

    case BRACKET_R = "bracket_r";

    case SLASH = "slash";

    case EOF = "eof";

    case ILLEGAL = "illegal";
}
