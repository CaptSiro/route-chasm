<?php

use components\pages\AiGeneratedPage\AiPageTemplate;
use components\pages\TextPage\TextPageTemplate;
use core\pages\Pages;

Pages::register(new TextPageTemplate());
Pages::register(new AiPageTemplate());
