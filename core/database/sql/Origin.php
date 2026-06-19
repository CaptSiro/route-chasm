<?php

namespace core\database\sql;

enum Origin {
    case EXTERNAL;
    case APPLICATION;
    case UNKNOWN;
}