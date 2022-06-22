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

array_insert($GLOBALS['BE_MOD']['personal-data'], 0, [
    'wem_pdm_dca' => [
        'tables' => ['tl_wem_personal_data'],
    ],
]);

$GLOBALS['TL_MODELS'][\WEM\PersonalDataManagerBundle\Model\PersonalData::getTable()] = \WEM\PersonalDataManagerBundle\Model\PersonalData::class;
