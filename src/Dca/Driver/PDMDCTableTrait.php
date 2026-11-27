<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

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

        if (! $model->isFieldInPersonalDataFieldsNames($field)) {
            return parent::formatCurrentValue($field, $value, $mode);
        }

        $obj = $model::findById(end($this->current));

        if ($obj === null) {
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
