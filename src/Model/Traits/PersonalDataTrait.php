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

    public function shouldManagePersonalData(): bool
    {
        return true;
    }

    /**
     * Delete the current record and return the number of affected rows.
     *
     * @return int The number of affected rows
     */
    public function delete(): int
    {
        // delete associated personal data
        if ($this->shouldManagePersonalData()) {
            $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
            $manager->deleteByPidAndPtable(
                (string) $this->getPersonalDataPidFieldValue(),
                $this->getPersonalDataPtable()
            );
        }

        return parent::delete();
    }

    /**
     * Reload the data from the database discarding all modifications.
     */
    public function refresh(): void
    {
        parent::refresh();
        if ($this->shouldManagePersonalData()) {
            $this->findAndApplyPersonalData();
        }
    }

    /**
     * Retrieves personal data attached to the current object and apply them.
     */
    public function findAndApplyPersonalData(): void
    {
        if ($this->shouldManagePersonalData()) {
            // re-find personal data
            $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
            $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');

            $personalDatas = $manager->findByPidAndPtable(
                (string) $this->getPersonalDataPidFieldValue(),
                $this->getPersonalDataPtable()
            );

            if ($personalDatas) {
                while ($personalDatas->next()) {
                    $this->{$personalDatas->field} = $personalDatas->anonymized ? $personalDatas->value : $encryptionService->decrypt($personalDatas->value);
                }
            }
        }
    }

    /**
     * Anonymize personal data attached to the current object and apply them.
     */
    public function anonymize(): void
    {
        if ($this->shouldManagePersonalData()) {
            // re-find personal data
            $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
            $manager->anonymizeByPidAndPtableAndEmail(
                (string) $this->getPersonalDataPidFieldValue(),
                $this->getPersonalDataPtable(),
                $this->getPersonalDataEmailFieldValue()
            );
            $this->refresh();
        }
    }

    public function getPersonalDataFieldsDefaultValues(): array
    {
        return self::$personalDataFieldsDefaultValues;
    }

    public function getPersonalDataFieldsDefaultValueForField(string $field): string
    {
        return $this->getPersonalDataFieldsDefaultValues()[$field];
    }

    public function getPersonalDataFieldsAnonymizedValues(): array
    {
        return self::$personalDataFieldsAnonymizedValues;
    }

    public function getPersonalDataFieldsAnonymizedValueForField(string $field): string
    {
        return $this->getPersonalDataFieldsAnonymizedValues()[$field];
    }

    public function getPersonalDataFieldsNames(): array
    {
        return self::$personalDataFieldsNames;
    }

    public function isFieldInPersonalDataFieldsNames(string $field): bool
    {
        return \in_array($field, $this->getPersonalDataFieldsNames(), true);
    }

    public function getPersonalDataPidField(): string
    {
        return self::$personalDataPidField;
    }

    public function getPersonalDataEmailField(): string
    {
        return self::$personalDataEmailField;
    }

    public function getPersonalDataPtable(): string
    {
        return self::$personalDataPtable;
    }

    public function getPersonalDataPidFieldValue(): string
    {
        return $this->{$this->getPersonalDataPidField()};
    }

    public function getPersonalDataEmailFieldValue(): string
    {
        return $this->{$this->getPersonalDataEmailField()};
    }

    /**
     * Find a single record by its primary key.
     *
     * @param mixed $varValue   The property value
     * @param array $arrOptions An optional options array
     *
     * @return static The model or null if the result is empty
     */
    public static function findByPk($varValue, array $arrOptions = [])
    {
        $obj = parent::findByPk($varValue, $arrOptions);

        if (!$obj) {
            return $obj;
        }
        if (!is_a($obj, self::class)) {
            $obj->detach(false);

            return static::findByPk($varValue, $arrOptions);
        }

        return $obj;
    }

    /**
     * Find a single record by its ID or alias.
     *
     * @param mixed $varId      The ID or alias
     * @param array $arrOptions An optional options array
     *
     * @return static The model or null if the result is empty
     */
    public static function findByIdOrAlias($varId, array $arrOptions = [])
    {
        $obj = parent::findByIdOrAlias($varId, $arrOptions);

        if (!$obj) {
            return $obj;
        }
        if (!is_a($obj, self::class)) {
            $obj->detach(false);

            return static::findByIdOrAlias($varId, $arrOptions);
        }

        return $obj;
    }

    /**
     * Find multiple records by their IDs.
     *
     * @param array $arrIds     An array of IDs
     * @param array $arrOptions An optional options array
     *
     * @return Collection|null The model collection or null if there are no records
     */
    public static function findMultipleByIds($arrIds, array $arrOptions = [])
    {
        $obj = parent::findMultipleByIds($arrIds, $arrOptions);
        if ($obj) {
            $detached = false;
            while ($obj->next()) {
                if (!is_a($obj->current(), self::class)) {
                    $obj->current()->detach(false);
                    $detached = true;
                }
            }

            if ($detached) {
                return static::findMultipleByIds($arrIds, $arrOptions);
            }
        }

        return $obj;
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

        if ($this->shouldManagePersonalData()) {
            foreach ($this->getPersonalDataFieldsNames() as $personalDataFieldName) {
                self::$personalDataFieldsValues[$personalDataFieldName] = $arrSet[$personalDataFieldName];
                if ($this->isFieldInPersonalDataFieldsNames($personalDataFieldName)) {
                    $arrSet[$personalDataFieldName] = self::$personalDataFieldsDefaultValues[$personalDataFieldName];
                } else {
                    unset($arrSet[$personalDataFieldName]);
                }
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
        if ($this->shouldManagePersonalData()) {
            $manager = \Contao\System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');

            $manager->insertOrUpdateForPidAndPtableAndEmail(
                (string) $this->getPersonalDataPidFieldValue(),
                $this->getPersonalDataPtable(),
                $this->getPersonalDataEmailFieldValue(),
                self::$personalDataFieldsValues
            );
            self::$personalDataFieldsValues = [];
        }
        parent::postSave($intType);
    }

    /**
     * Find records and return the model or model collection.
     *
     * Supported options:
     *
     * * column: the field name
     * * value:  the field value
     * * limit:  the maximum number of rows
     * * offset: the number of rows to skip
     * * order:  the sorting order
     * * eager:  load all related records eagerly
     *
     * @param array $arrOptions The options array
     *
     * @return Model|Model[]|Collection|null A model, model collection or null if the result is empty
     */
    protected static function find(array $arrOptions)
    {
        $obj = parent::find($arrOptions);
        if (!$obj) {
            return $obj;
        }
        if (!is_a($obj, self::class) && !is_a($obj, \Contao\Model\Collection::class)) {
            $obj->detach(false);

            return static::find($arrOptions);
        }
        if (is_a($obj, \Contao\Model\Collection::class)) {
            $detached = false;
            while ($obj->next()) {
                if (!is_a($obj->current(), self::class)) {
                    $obj->current()->detach(false);
                    $detached = true;
                }
            }
            if ($detached) {
                return static::find($arrOptions);
            }
        }

        // if (is_a($obj, self::class)) {
        //     $obj->attach();
        // }

        // if (is_a($obj, \Contao\Model\Collection::class)) {
        //     while ($obj->next()) {
        //         if (is_a($obj->current(), self::class)) {
        //             $obj->attach();
        //         }
        //     }
        // }

        return $obj;
    }
}
