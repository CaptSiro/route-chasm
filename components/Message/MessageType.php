<?php

namespace components\Message;

enum MessageType: string {
    case INFO = "INFO";

    case CONFIRMATION = "NOTICE";

    case WARNING = "WARNING";

    case ERROR = "ERROR";



    public function toLowerCase(): string {
        return strtolower($this->value);
    }
}