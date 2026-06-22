<?php

namespace core\storage;

enum DataWritePolicy {
    case WRITE_THROUGH;
    case WRITE_BACK;
}