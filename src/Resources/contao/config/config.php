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

// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/backend.css';
}

array_insert($GLOBALS['BE_MOD']['wem_pdm'], 0, [
    'wem-personal-data-manager' => [
        'callback' => \WEM\PersonalDataManagerBundle\Controller\PersonalDataManagerController::class,
    ],
]);

$GLOBALS['TL_MODELS'][\WEM\PersonalDataManagerBundle\Model\PersonalData::getTable()] = \WEM\PersonalDataManagerBundle\Model\PersonalData::class;
$GLOBALS['TL_MODELS'][\WEM\PersonalDataManagerBundle\Model\PersonalDataAccessToken::getTable()] = \WEM\PersonalDataManagerBundle\Model\PersonalDataAccessToken::class;

/*
 * Frontend modules
 */
array_insert($GLOBALS['FE_MOD'], 2, [
    'wem-personal-data-manager' => [
        'wem_personaldatamanager' => \WEM\PersonalDataManagerBundle\Module\PersonalDataManager::class,
    ],
]);

$GLOBALS['WEM_HOOKS'] = $GLOBALS['WEM_HOOKS'] ?? [];
// UI
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
$GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'] = $GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'] ?? [];
$GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'] = $GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'] ?? [];
// Anonymize
$GLOBALS['WEM_HOOKS']['anonymize'] = $GLOBALS['WEM_HOOKS']['anonymize'] ?? [];
$GLOBALS['WEM_HOOKS']['anonymizeByEmail'] = $GLOBALS['WEM_HOOKS']['anonymizeByEmail'] ?? [];
$GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'] = $GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'] ?? [];
$GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField'] = $GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField'] ?? [];
// Export
$GLOBALS['WEM_HOOKS']['exportByEmail'] = $GLOBALS['WEM_HOOKS']['exportByEmail'] ?? [];
$GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'] = $GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'] ?? [];
