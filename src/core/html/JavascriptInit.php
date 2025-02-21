<?php

namespace core\html;

trait JavascriptInit {
    protected string $javascriptFunctions = '';

    public function addJavascriptFunction(string $function): static {
        $this->javascriptFunctions .= $function;
        return $this;
    }

    public function getJavascriptFunctions(): string {
        return $this->javascriptFunctions;
    }

    public function getInitAttribute(): string {
        if ($this->javascriptFunctions === '') {
            return '';
        }

        return 'x-init="'. $this->javascriptFunctions .'"';
    }
}