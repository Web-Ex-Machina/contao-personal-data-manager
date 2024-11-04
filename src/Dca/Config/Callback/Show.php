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

namespace WEM\PersonalDataManagerBundle\Dca\Config\Callback;

use Contao\DataContainer;
use Contao\Model;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;

class Show
{
    protected PersonalDataManager $personalDataManager;

    public function __construct(
        PersonalDataManager $personalDataManager
    ) {
        $this->personalDataManager = $personalDataManager;
    }

    public function __invoke(array $modalData, array $data, DataContainer $dc): array
    {
        if (!$dc->id) {
            return $modalData;
        }

        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();
        foreach ($modalData as $table => $rows) {
            foreach ($rows as $index => $row) {
                foreach ($row as $label => $value) {
                    if (preg_match('/(.*)<small>(.*)<\/small>/', $label, $matches)) {
                        $fieldName = $matches[2];
                        if ($model->isFieldInPersonalDataFieldsNames($fieldName)) {
                            $modalData[$table][$index][$label] = $this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
                                (int) $data[$model->getPersonalDataPidField()],
                                $model->getPersonalDataPtable(),
                                $data[$model->getPersonalDataEmailField()],
                                $fieldName
                            );
                        }
                    }
                }
            }
        }

        return $modalData;
    }
}
