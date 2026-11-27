<?php

declare(strict_types=1);

use Contao\System;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use WEM\PersonalDataManagerBundle\Model\PersonalDataAccessToken;

/*
 * Back end modules
 *
 * The module is served by the PersonalDataManagerController route, the menu item
 * is pointed to it by the BackendMenuListener.
 */
$GLOBALS['BE_MOD']['wem_pdm']['wem-personal-data-manager'] = [];

// Load the category icon in the back end
if (System::getContainer()->get('wem.scope_matcher')->isBackend()) {
    $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/backend.css';
}

/*
 * Models
 */
$GLOBALS['TL_MODELS'][PersonalData::getTable()] = PersonalData::class;
$GLOBALS['TL_MODELS'][PersonalDataAccessToken::getTable()] = PersonalDataAccessToken::class;

/*
 * Personal Data Manager hooks
 */
$GLOBALS['WEM_HOOKS'] ??= [];
// UI
$GLOBALS['WEM_HOOKS']['buildListButtons'] ??= [];
$GLOBALS['WEM_HOOKS']['renderListButtons'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItem'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemHeader'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemTitle'] ??= [];
$GLOBALS['WEM_HOOKS']['buildSingleItemButtons'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemButtons'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBody'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'] ??= [];
$GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons'] ??= [];
$GLOBALS['WEM_HOOKS']['buildSingleItemBodyPersonalDataSingleButtons'] ??= [];
$GLOBALS['WEM_HOOKS']['sortData'] ??= [];
// Manager
$GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail'] ??= [];
$GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'] ??= [];
$GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile'] ??= [];
// Anonymize
$GLOBALS['WEM_HOOKS']['anonymize'] ??= [];
$GLOBALS['WEM_HOOKS']['anonymizeByEmail'] ??= [];
$GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'] ??= [];
$GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField'] ??= [];
// Export
$GLOBALS['WEM_HOOKS']['exportByEmail'] ??= [];
$GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'] ??= [];
$GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'] ??= [];
$GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'] ??= [];
