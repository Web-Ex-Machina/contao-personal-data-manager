<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * Copyright (c) 2015-2024 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-smartgear
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/personal-data-manager/
 */

namespace WEM\PersonalDataManagerBundle\Dca\Listing\Callback;

use Contao\DataContainer;
use Contao\Model;

class ListLabelGroup
{
    protected $personalDataManager;

    public function __construct(
        \WEM\PersonalDataManagerBundle\Service\PersonalDataManager $personalDataManager
    ) {
        $this->personalDataManager = $personalDataManager;
    }

    public function __invoke(string $group, ?string $mode, string $field, array $data, DataContainer $dc): string
    {
        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();
        $model->setRow($data);

        $group = $this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
            // $data[$model->getPersonalDataPidField()],
            (int) $model->getPersonalDataPidFieldValue(),
            $model->getPersonalDataPtable(),
            // $data[$model->getPersonalDataEmailField()],
            $model->getPersonalDataEmailFieldValue(),
            $field
        );

        if (!\array_key_exists('group_callback_previous', $GLOBALS['TL_DCA'][$dc->table]['list']['label'])) {
            return $group;
        }

        // Call the group callback ($group, $sortingMode, $firstOrderBy, $row, $this)
        if (\is_array($GLOBALS['TL_DCA'][$dc->table]['list']['label']['group_callback_previous'] ?? null)) {
            $strClass = $GLOBALS['TL_DCA'][$dc->table]['list']['label']['group_callback_previous'][0];
            $strMethod = $GLOBALS['TL_DCA'][$dc->table]['list']['label']['group_callback_previous'][1];

            $dc->import($strClass);
            $group = $dc->$strClass->$strMethod($group, $mode, $field, $data, $dc);
        } elseif (\is_callable($GLOBALS['TL_DCA'][$dc->table]['list']['label']['group_callback_previous'] ?? null)) {
            $group = $GLOBALS['TL_DCA'][$dc->table]['list']['label']['group_callback_previous']($group, $mode, $field, $data, $dc);
        }

        return $group;
    }
}
