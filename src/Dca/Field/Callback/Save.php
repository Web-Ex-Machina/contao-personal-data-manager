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
use Contao\FrontendUser;
use Contao\ModulePersonalData;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;
use function func_get_args;
use function func_num_args;

class Save
{
    protected PersonalDataManager $personalDataManager;

    protected string $frontendField;

    protected string $table;

    public function __construct(
        PersonalDataManager $personalDataManager,
        string $frontendField,
        string $table
    ) {
        $this->personalDataManager = $personalDataManager;
        $this->frontendField = $frontendField;
        $this->table = $table;
    }

    public function __invoke()
    {
        if (1 === func_num_args()
        || (2 === func_num_args() && null === func_get_arg(1))
        ) {
            return $this->invokeFrontendRegistration(...func_get_args());
        }

        if (2 === func_num_args()) {
            return $this->invokeBackend(...func_get_args());
        }

        return $this->invokeFrontend(...func_get_args());
    }

    public function invokeBackend($value, DataContainer $dc)
    {
        return $value;
//      commented 7 may 2024  all the other code are already commented from 2022
//        if (!$dc->id) {
//            return $value;
//        }
//
//        $returnValue = $value;
//
//        $modelClassName = Model::getClassFromTable($dc->table);
//        $model = new $modelClassName();
//        $model->setRow($dc->activeRecord->row());
//
//        if (empty($model->getPersonalDataEmailFieldValue())) {
//            return $value;
//        }
//
//        $pdm = $this->personalDataManager->insertOrUpdateForPidAndPtableAndEmailAndField(
//            $model->getPersonalDataPidFieldValue(),
//            $model->getPersonalDataPtable(),
//            $model->getPersonalDataEmailFieldValue(),
//            $dc->inputName,
//            $value
//        );
//
//        $returnValue = $pdm->value;
//        return $model->getPersonalDataFieldsDefaultValueForField($dc->inputName);
    }

    public function invokeFrontend($value, FrontendUser $user, ModulePersonalData $module)
    {
        // the model's postSave method will handle this
        return $value;
    }

    public function invokeFrontendRegistration($value)
    {
        // the model's postSave method will handle this
        return $value;
    }

    public function getFrontendField(): string
    {
        return $this->frontendField;
    }

    public function setFrontendField(string $frontendField): self
    {
        $this->frontendField = $frontendField;

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }
}
