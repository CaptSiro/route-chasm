<?php

namespace core\actions;

enum UnexpectedHttpMethodPolicy {
    case IGNORE;

    case TERMINATE;
}
