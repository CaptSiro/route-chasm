<?php

namespace core\view2;

class DataTransfer implements Payload {
    use PayloadTrait;



    public function __construct(
        protected View $view
    ) {}



    // RendererPayload
    public function getViewReference(): View {
        return $this->view;
    }
}