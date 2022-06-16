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

/*
 * Personal Data Manager for Contao Open Source CMS
 * Copyright (c) 2015-2022 Web ex Machina.
 *
 * @category ContaoBundle
 *
 * @author   Web ex Machina <contact@webexmachina.fr>
 *
 * @see     https://github.com/Web-Ex-Machina/personal-data-manager/
 */
use Contao\Database\Result;
use Contao\Model;

trait PersonalDataTrait
{
    public static $personalDataFieldsNames = [];
    public static $personalDataFieldsValues = [];
    public static $personalDataPidField = '';
    public static $personalDataPtable = '';

    public function getPersonalDataPtable()
    {
        return $this->personalDataPtable;
    }

    /**
     * Delete the current record and return the number of affected rows.
     *
     * @return int The number of affected rows
     */
    public function delete(): int
    {
        $nbRows = parent::delete();
        // delete associated personal data
        $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
        $manager->deleteForPidAndPtable(
            $this->{self::$personalDataPidField},
            $this->getPersonalDataPtable()
        );

        return $nbRows;
    }

    /**
     * Reload the data from the database discarding all modifications.
     */
    public function refresh(): void
    {
        parent::refresh();
        // re-find personal data
        $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');

        $personalDatas = $manager->findForPidAndPtable(
            $this->{self::$personalDataPidField},
            $this->getPersonalDataPtable()
        );

        if ($personalDatas) {
            while ($personalDatas) {
                $this->{$personalDatas->fieldId} = $personalDatas->value; // We should unencrypt here
            }
        }
    }

    /**
     * Modify the database result before the model is created.
     *
     * @param Result $objResult The database result object
     *
     * @return Result The database result object
     */
    protected static function postFind(Result $objResult)
    {
        $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');

        $personalDatas = $manager->findForPidAndPtable(
            $objResult->{self::$personalDataPidField},
            self::$personalDataPtable
        );

        if ($personalDatas) {
            $objResult = $manager->applyTo($objResult, $personalDatas);
        }

        return $objResult;
    }

    /**
     * Modify the current row before it is stored in the database.
     *
     * @param array $arrSet The data array
     *
     * @return array The modified data array
     */
    protected function preSave(array $arrSet): array
    {
        $arrSet = parent::preSave($arrSet);
        foreach (self::$personalDataFieldsNames as $personalDataFieldName) {
            self::$personalDataFieldsValues[$personalDataFieldName] = $arrSet[$personalDataFieldName];
            unset($arrSet[$personalDataFieldName]);
        }

        return $arrSet;
    }

    /**
     * Modify the current row after it has been stored in the database.
     *
     * @param int $intType The query type (Model::INSERT or Model::UPDATE)
     */
    protected function postSave($intType): void
    {
        parent::postSave($intType);
        $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
        $manager->insertOrUpdateForPidAndPtable(
            $this->{self::$personalDataPidField},
            $this->getPersonalDataPtable(),
            self::$personalDataFieldsValues
        );
        self::$personalDataFieldsValues = [];
        $this->refresh();
    }
}
