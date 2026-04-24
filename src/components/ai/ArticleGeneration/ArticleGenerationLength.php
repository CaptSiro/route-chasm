<?php

namespace components\ai\ArticleGeneration;

use core\EnumOptions;

enum ArticleGenerationLength: string {
    use EnumOptions;

    case SHORT = 'Short (300–600 words)';

    case MEDIUM = 'Medium (700–1200 words)';

    case LONG = 'Long (1300–2500+ words)';
}