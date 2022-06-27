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
    'wem-personal-data-manager' => [
        'callback' => \WEM\PersonalDataManagerBundle\Controller\PersonalDataManagerController::class,
    ],
]);

$GLOBALS['TL_MODELS'][\WEM\PersonalDataManagerBundle\Model\PersonalData::getTable()] = \WEM\PersonalDataManagerBundle\Model\PersonalData::class;

$GLOBALS['WEM_HOOKS'] = $GLOBALS['WEM_HOOKS'] ?? [];
$GLOBALS['WEM_HOOKS']['renderListButtons'] = $GLOBALS['WEM_HOOKS']['renderListButtons'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItem'] = $GLOBALS['WEM_HOOKS']['renderSingleItem'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemHeader'] = $GLOBALS['WEM_HOOKS']['renderSingleItemHeader'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemTitle'] = $GLOBALS['WEM_HOOKS']['renderSingleItemTitle'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemButtons'] = $GLOBALS['WEM_HOOKS']['renderSingleItemButtons'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBody'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBody'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons'] = $GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons'] ?? [];
$GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail'] = $GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail'] ?? [];
