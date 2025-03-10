<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/public/index.php',
    ])
    ->withSkipPath(__DIR__ . '/vendor')
    ->withSkipPath(__DIR__ . '/app/cache')
    ->withSkipPath(__DIR__ . '/app/config/config.php')
    ->withSkipPath(__DIR__ . '/app/views')
    ->withPhpSets(php82: true)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withCodingStyleLevel(0)
    ->withTypeCoverageLevel(0);
