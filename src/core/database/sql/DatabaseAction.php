<?php

namespace core\database\sql;

enum DatabaseAction {
    case NONE;
    case INSERT;
    case UPDATE;
    case DELETE;
}