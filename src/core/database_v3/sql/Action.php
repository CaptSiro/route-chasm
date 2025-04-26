<?php

namespace core\database_v3\sql;

enum Action {
    case NONE;
    case INSERT;
    case UPDATE;
    case DELETE;
}