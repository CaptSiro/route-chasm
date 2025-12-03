<?php

namespace components\layout\Grid;

use components\layout\Grid\Loader\GridLoader;
use components\layout\Grid\Proxy\Proxy;

interface GridLayoutFactory {
    public function getProxy(): ?Proxy;

    public function setProxy(?Proxy $proxy): static;

    public function getColumns(): array;

    public function setColumns(array $columns): static;

    public function getLoader(): ?GridLoader;

    public function setLoader(?GridLoader $loader): static;

    public function createGrid(Proxy $proxy): ?GridLayout;
}