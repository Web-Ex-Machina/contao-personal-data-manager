<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withConfiguredRule(HeaderCommentFixer::class, [
        'header' =>
"Personal Data Manager for Contao Open Source CMS
@author     Web Ex Machina

@see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
@license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt"])
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/contao',
    ])
    ->withSkip([
        ArrayOpenerAndCloserNewlineFixer::class,
        __DIR__ . '/vendor',
        __DIR__ . '/contao/templates',
    ])
    ->withPreparedSets(
        symplify: true,
        psr12: true,
        common: true,
    );
