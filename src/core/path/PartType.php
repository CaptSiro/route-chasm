<?php

namespace core\path;

enum PartType {
    case STATIC;
    case DYNAMIC;
    case ANY;
}