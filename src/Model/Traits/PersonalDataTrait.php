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

namespace WEM\PersonalDataManagerBundle\Model\Traits;

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
    protected static $personalDataFieldsValues = [];

    /**
     * Delete the current record and return the number of affected rows.
     *
     * @return int The number of affected rows
     */
    public function delete(): int
    {
        // delete associated personal data
        $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
        $manager->deleteForPidAndPtable(
            (string) $this->{self::$personalDataPidField},
            self::$personalDataPtable
        );

        return parent::delete();
    }

    /**
     * Reload the data from the database discarding all modifications.
     */
    public function refresh(): void
    {
        parent::refresh();
        $this->findAndApplyPersonalData();
    }

    public function findAndApplyPersonalData(): void
    {
        // re-find personal data
        $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');

        $personalDatas = $manager->findForPidAndPtable(
            (string) $this->{self::$personalDataPidField},
            self::$personalDataPtable
        );

        if ($personalDatas) {
            while ($personalDatas->next()) {
                $this->{$personalDatas->field} = $encryptionService->decrypt($personalDatas->value); // We should unencrypt here
            }
        }
    }

    /**
     * Create a model from a database result.
     *
     * @param Result $objResult The database result object
     *
     * @return static The model
     */
    protected static function createModelFromDbResult(Result $objResult)
    {
        $model = parent::createModelFromDbResult($objResult);
        if ($model) {
            $model->findAndApplyPersonalData();
        }

        return $model;
    }

    /**
     * Create a Collection object.
     *
     * @param array  $arrModels An array of models
     * @param string $strTable  The table name
     *
     * @return Collection The Collection object
     */
    protected static function createCollection(array $arrModels, $strTable)
    {
        $collection = parent::createCollection($arrModels, $strTable);
        while ($collection->next()) {
            $model = $collection->current();
            $model->findAndApplyPersonalData();
            $collection->setRow($model->row());
        }

        return $collection;
    }

    /**
     * Create a new collection from a database result.
     *
     * @param Result $objResult The database result object
     * @param string $strTable  The table name
     *
     * @return Collection The model collection
     */
    protected static function createCollectionFromDbResult(Result $objResult, $strTable)
    {
        $collection = parent::createCollectionFromDbResult($objResult, $strTable);
        while ($collection->next()) {
            $model = $collection->current();
            $model->findAndApplyPersonalData();
            $collection->setRow($model->row());
        }

        return $collection;
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
            if (\array_key_exists($personalDataFieldName, self::$personalDataFieldsDefaultValues)) {
                $arrSet[$personalDataFieldName] = self::$personalDataFieldsDefaultValues[$personalDataFieldName];
            } else {
                unset($arrSet[$personalDataFieldName]);
            }
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
        $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');

        $manager->insertOrUpdateForPidAndPtable(
            (string) $this->{self::$personalDataPidField},
            self::$personalDataPtable,
            self::$personalDataFieldsValues
        );
        self::$personalDataFieldsValues = [];
        parent::postSave($intType);
    }
}
