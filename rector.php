<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoLevelSetList;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/contao',
    ])
    ->withSkip([
        CombineIfRector::class,
        __DIR__ . '/contao/templates',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        ContaoLevelSetList::UP_TO_CONTAO_57,
    ]);
