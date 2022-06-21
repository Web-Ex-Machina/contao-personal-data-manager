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

namespace WEM\PersonalDataManagerBundle\Dca\Driver;

use Contao\Model;
use Contao\System;

trait PDMDCTableTrait
{
    /**
     * Return the formatted group header as string.
     *
     * @param string $field
     * @param int    $mode
     *
     * @return string
     */
    protected function formatCurrentValue($field, $value, $mode)
    {
        // dump($field, $value, $mode, end($this->current));

        $personalDataManager = System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');

        $modelClassName = Model::getClassFromTable($this->table);
        $model = new $modelClassName();

        if ($model->isFieldInPersonalDataFieldsNames($field)) {
            $obj = $model::findOneById(end($this->current));

            return $personalDataManager->getUnecryptedValueByPidAndPTableAndEmailAndField(
                $obj->{$model->getPersonalDataPidField()},
                $model->getPersonalDataPtable(),
                $obj->{$model->getPersonalDataEmailField()},
                $field
            );
        }

        return parent::formatCurrentValue($field, $value, $mode);
    }
}
