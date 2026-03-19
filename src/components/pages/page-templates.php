<?php

use components\pages\AiGeneratedPage\AiPageTemplate;
use components\pages\Article\ArticleTemplate;
use components\pages\External\ExternalPageTemplate;
use components\pages\Listing\ListingTemplate;
use components\pages\TextPage\TextPageTemplate;
use core\pages\Pages;

Pages::register(new TextPageTemplate());
Pages::register(new AiPageTemplate());
Pages::register(new ArticleTemplate());
Pages::register(new ListingTemplate());
Pages::register(new ExternalPageTemplate());
