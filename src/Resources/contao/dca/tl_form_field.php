<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * Copyright (c) 2015-2022 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-smartgear
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/personal-data-manager/
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_form_field']['fields']['containsPersonalData'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "tinyint unsigned NOT NULL DEFAULT '0'",
];

$palettes = [
    'html',
    'text',
    'textdigit',
    'textcustom',
    'password',
    'passwordcustom',
    'textarea',
    'textareacustom',
    'select',
    'radio',
    'checkbox',
    'upload',
    'range',
    'hidden',
    'hiddencustom',
];
$paletteManipulator = PaletteManipulator::create()
    ->addField('containsPersonalData', 'type_legend', PaletteManipulator::POSITION_APPEND)
;
foreach ($palettes as $palette) {
    $paletteManipulator->applyToPalette($palette, 'tl_form_field');
}
