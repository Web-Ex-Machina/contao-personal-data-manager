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

namespace WEM\PersonalDataManagerBundle\Dca\Field\Callback;

use Contao\DataContainer;
use Contao\Model;

class Save
{
    protected $personalDataManager;

    public function __construct(
        \WEM\PersonalDataManagerBundle\Service\PersonalDataManager $personalDataManager
    ) {
        $this->personalDataManager = $personalDataManager;
    }

    public function __invoke($value, DataContainer $dc)
    {
        if (!$dc->id) {
            return $value;
        }

        $returnValue = $value;

        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();

        $pdm = $this->personalDataManager->insertOrUpdateForPidAndPtableAndEmailAndField(
            $dc->activeRecord->{$model->getPersonalDataPidField()},
            $model->getPersonalDataPtable(),
            $dc->activeRecord->{$model->getPersonalDataEmailField()},
            $dc->inputName,
            $value
        );

        $returnValue = $pdm->value;

        return $model->getPersonalDataFieldsDefaultValueForField($dc->inputName);
    }
}
