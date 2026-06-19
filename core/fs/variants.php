<?php

use core\fs\FileSystem;
use core\fs\variants\ImageVariant;

FileSystem::registerVariant(ImageVariant::getInstance());