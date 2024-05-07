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

use WEM\PersonalDataManagerBundle\Controller\PersonalDataManagerController;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use WEM\PersonalDataManagerBundle\Model\PersonalDataAccessToken;
use WEM\PersonalDataManagerBundle\Module\PersonalDataManager;

// Load icon in Contao 4.2 backend

if (defined('TL_MODE') && 'BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/backend.css';
}

Contao\ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['wem_pdm'], 0, [
    'wem-personal-data-manager' => [
        'callback' => PersonalDataManagerController::class,
    ],
]);

$GLOBALS['TL_MODELS'][PersonalData::getTable()] = PersonalData::class;
$GLOBALS['TL_MODELS'][PersonalDataAccessToken::getTable()] = PersonalDataAccessToken::class;

/*
 * Frontend modules
 */
Contao\ArrayUtil::arrayInsert($GLOBALS['FE_MOD'], 2, [
    'wem-personal-data-manager' => [
        'wem_personaldatamanager' => PersonalDataManager::class,
    ],
]);

$GLOBALS['WEM_HOOKS'] = $GLOBALS['WEM_HOOKS'] ?? [];
// UI
$GLOBALS['WEM_HOOKS']['buildListButtons'] = $GLOBALS['WEM_HOOKS']['buildListButtons'] ?? [];
$GLOBALS['WEM_HOOKS']['renderListButtons'] = $GLOBALS['WEM_HOOKS']['renderListButtons'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItem'] = $GLOBALS['WEM_HOOKS']['renderSingleItem'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemHeader'] = $GLOBALS['WEM_HOOKS']['renderSingleItemHeader'] ?? [];
$GLOBALS['WEM_HOOKS']['renderSingleItemTitle'] = $GLOBALS['WEM_HOOKS']['renderSingleItemTitle'] ?? [];
$GLOBALS['WEM_HOOKS']['buildSingleItemButtons'] = $GLOBALS['WEM_HOOKS']['buildSingleItemButtons'] ?? [];
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
$GLOBALS['WEM_HOOKS']['buildSingleItemBodyPersonalDataSingleButtons'] = $GLOBALS['WEM_HOOKS']['buildSingleItemBodyPersonalDataSingleButtons'] ?? [];
// Manager
$GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail'] = $GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail'] ?? [];
$GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'] = $GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'] ?? [];
$GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile'] = $GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile'] ?? [];
// Anonymize
$GLOBALS['WEM_HOOKS']['anonymize'] = $GLOBALS['WEM_HOOKS']['anonymize'] ?? [];
$GLOBALS['WEM_HOOKS']['anonymizeByEmail'] = $GLOBALS['WEM_HOOKS']['anonymizeByEmail'] ?? [];
$GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'] = $GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'] ?? [];
$GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField'] = $GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField'] ?? [];
// Export
$GLOBALS['WEM_HOOKS']['exportByEmail'] = $GLOBALS['WEM_HOOKS']['exportByEmail'] ?? [];
$GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'] = $GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'] ?? [];
$GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'] = $GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'] ?? [];
$GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'] = $GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'] ?? [];
