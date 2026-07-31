<?php

namespace core\view_old;


interface ViewTemplate extends View {
    public function getResource(string $path = ''): string;

    public function getResources(string $directory = ''): array;

    public function getClass(): string;



    public function getTemplate(): string;

    public function getTemplateVariant(?string $variant): string;

    public function renderTemplated(?string $template = null): string;

    public function setTemplate(?string $template): static;
}