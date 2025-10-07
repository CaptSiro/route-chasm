<?php

namespace core\data;

enum DataWritePolicy {
    case WRITE_THROUGH;
    case WRITE_BACK;
}