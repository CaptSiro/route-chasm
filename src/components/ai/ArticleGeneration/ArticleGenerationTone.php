<?php

namespace components\ai\ArticleGeneration;

use core\EnumOptions;

enum ArticleGenerationTone: string {
    use EnumOptions;

    case FORMAL = 'Formal (objective, structured, no personal voice)';

    case NEUTRAL = 'Neutral (informative, balanced, minimal stylistic bias)';

    case CASUAL = 'Casual (conversational, approachable, lighter language)';

    case PERSUASIVE = "Persuasive (argument-driven, aims to convince)";

    case TECHNICAL = "Technical (precise, dense, domain-specific terminology)";
}