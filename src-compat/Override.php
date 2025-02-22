<?php

declare(strict_types=1);

if (! class_exists('Override', false)) {
    #[Attribute(\Attribute::TARGET_METHOD)]
    final class Override
    {
    }
}
