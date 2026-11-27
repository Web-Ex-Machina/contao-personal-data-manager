<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/contao',
    ])
    ->withSkip([
        __DIR__ . '/contao/templates',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        // ContaoLevelSetList relies on Symfony sets removed from Rector: use the
        // Contao sets, the Symfony ones are resolved from the installed version
        ContaoSetList::CONTAO_50,
        ContaoSetList::CONTAO_51,
        ContaoSetList::CONTAO_53,
        ContaoSetList::ANNOTATIONS_TO_ATTRIBUTES,
        ContaoSetList::FQCN,
    ])
    ->withComposerBased(symfony: true);
