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

namespace WEM\PersonalDataManagerBundle\Dca\Listing\Callback;

use Contao\DataContainer;
use Contao\Model;

class ListLabelLabelForList
{
    protected $personalDataManager;

    public function __construct(
        \WEM\PersonalDataManagerBundle\Service\PersonalDataManager $personalDataManager
    ) {
        $this->personalDataManager = $personalDataManager;
    }

    public function __invoke(array $row, string $labelSingle, DataContainer $dc, array $labels): array
    {
        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();

        $labelFields = $GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'];

        // $labelFields contains the field names, while $labels only contains the values
        // They are in the same order, so $index can be used
        foreach ($labelFields as $index => $label) {
            if ($model->isFieldInPersonalDataFieldsNames($label)) {
                $labels[$index] = $this->personalDataManager->getUnecryptedValueByPidAndPTableAndEmailAndField(
                    $row[$model->getPersonalDataPidField()],
                    $model->getPersonalDataPtable(),
                    $row[$model->getPersonalDataEmailField()],
                    $label
                );
            }
        }

        return $labels;
    }
}
