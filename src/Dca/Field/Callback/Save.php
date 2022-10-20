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
use Exception;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;

class Save
{
    /** @var PersonalDataManager */
    protected $personalDataManager;
    /** @var string */
    protected $frontendField;
    /** @var string */
    protected $table;

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
        if (1 === \func_num_args()
        || (2 === \func_num_args() && null === func_get_arg(1))
        ) {
            return $this->invokeFrontendRegistration(...\func_get_args());
        }
        if (2 === \func_num_args()) {
            return $this->invokeBackend(...\func_get_args());
        }

        return $this->invokeFrontend(...\func_get_args());
    }

    public function invokeBackend($value, DataContainer $dc)
    {
        if (!$dc->id) {
            return $value;
        }

        $returnValue = $value;

        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();
        $model->setRow($dc->activeRecord->row());

        if (empty($model->getPersonalDataEmailFieldValue())) {
            return $value;
        }

        $pdm = $this->personalDataManager->insertOrUpdateForPidAndPtableAndEmailAndField(
            $model->getPersonalDataPidFieldValue(),
            $model->getPersonalDataPtable(),
            $model->getPersonalDataEmailFieldValue(),
            $dc->inputName,
            $value
        );

        $returnValue = $pdm->value;

        // \WEM\SmartgearBundle\Classes\Util::log('=====');
        // \WEM\SmartgearBundle\Classes\Util::log('invokeBackend');
        // \WEM\SmartgearBundle\Classes\Util::log(print_r($dc->activeRecord->row(), true));

        // // \WEM\SmartgearBundle\Classes\Util::log($model->getPersonalDataPidFieldValue());
        // // \WEM\SmartgearBundle\Classes\Util::log($model->getPersonalDataPtable());
        // \WEM\SmartgearBundle\Classes\Util::log($model->getPersonalDataEmailField());
        // \WEM\SmartgearBundle\Classes\Util::log($model->getPersonalDataEmailFieldValue());

        // \WEM\SmartgearBundle\Classes\Util::log($dc->inputName);
        // \WEM\SmartgearBundle\Classes\Util::log($value);
        // \WEM\SmartgearBundle\Classes\Util::log($pdm->value);
        // \WEM\SmartgearBundle\Classes\Util::log($model->getPersonalDataFieldsDefaultValueForField($dc->inputName));

        return $model->getPersonalDataFieldsDefaultValueForField($dc->inputName);
    }

    public function invokeFrontend($value, \Contao\FrontendUser $user, \Contao\ModulePersonalData $module)
    {
        // if (empty($this->frontendField)) {
        //     throw new Exception('No frontend field configured');
        // }
        // if (empty($this->table)) {
        //     throw new Exception('No table configured');
        // }

        // $returnValue = $value;

        // $modelClassName = Model::getClassFromTable($this->table);
        // $model = $modelClassName::findByPk($user->id);

        // $pdm = $this->personalDataManager->insertOrUpdateForPidAndPtableAndEmailAndField(
        //     $model->getPersonalDataPidFieldValue(),
        //     $model->getPersonalDataPtable(),
        //     $model->getPersonalDataEmailFieldValue(),
        //     $this->frontendField,
        //     $value
        // );

        // $returnValue = $pdm->value;

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
