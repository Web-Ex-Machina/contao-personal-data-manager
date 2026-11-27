<?php

declare(strict_types=1);

namespace WEM\PersonalDataManagerBundle\Dca\Driver;

use Contao\Model;
use Contao\System;

/**
 * To be used in a custom DC_Table driver, so that grouping in back end listings
 * displays the personal data values.
 */
trait PDMDCTableTrait
{
    /**
     * Return the formatted group header as string.
     *
     * @param string $field
     * @param int    $mode
     */
    protected function formatCurrentValue($field, $value, $mode)
    {
        $modelClassName = Model::getClassFromTable($this->table);
        $model = new $modelClassName();

        if (!$model->isFieldInPersonalDataFieldsNames($field)) {
            return parent::formatCurrentValue($field, $value, $mode);
        }

        $obj = $model::findById(end($this->current));

        if (null === $obj) {
            return parent::formatCurrentValue($field, $value, $mode);
        }

        return System::getContainer()
            ->get('wem.personal_data_manager.service.personal_data_manager')
            ->getUnecryptedValueByPidAndPtableAndEmailAndField(
                $obj->getPersonalDataPidFieldValue(),
                $model->getPersonalDataPtable(),
                $obj->getPersonalDataEmailFieldValue(),
                $field
            )
        ;
    }
}
