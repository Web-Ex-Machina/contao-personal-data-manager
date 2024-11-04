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
use Contao\Model;
use Contao\ModulePersonalData;
use Exception;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;
use function func_get_args;
use function func_num_args;

class Load
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

    /**
     * @throws Exception
     */
    public function __invoke()
    {
        if (2 === func_num_args()) {
            return $this->invokeBackend(...func_get_args());
        }

        return $this->invokeFrontend(...func_get_args());
    }

    /**
     * @throws Exception
     */
    public function invokeBackend($value, DataContainer $dc)
    {
        if (!$dc->id) {
            return $value;
        }

        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();
        $model->setRow($dc->activeRecord->row());

        return $this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
            (int) $model->getPersonalDataPidFieldValue(),
            $model->getPersonalDataPtable(),
            $model->getPersonalDataEmailFieldValue(),
            $dc->inputName
        ) ?? $value;
    }

    /**
     * @throws Exception
     */
    public function invokeFrontend($value, FrontendUser $user, ModulePersonalData $module): string
    {
        if (empty($this->frontendField)) {
            throw new Exception('No frontend field configured');
        }

        if (empty($this->table)) {
            throw new Exception('No table configured');
        }

        $modelClassName = Model::getClassFromTable($this->table);
        $model = new $modelClassName();

        return $this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
            (int) $user->{$model->getPersonalDataPidField()},
            $model->getPersonalDataPtable(),
            $user->{$model->getPersonalDataEmailField()},
            $this->frontendField
        ) ?? $value;
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
