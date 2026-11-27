<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Model\Traits;

use Contao\Database\Result;
use Contao\Model;
use Contao\Model\Collection;
use Contao\System;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;

/**
 * To be used in a Contao Model. The using class must define the following
 * static properties:
 *
 * - array  $personalDataFieldsNames            Fields managed by the Personal Data Manager
 * - array  $personalDataFieldsDefaultValues    Values stored in the model table for those fields
 * - array  $personalDataFieldsAnonymizedValues Values used for those fields when anonymized
 * - string $personalDataPidField               Field used as pid
 * - string $personalDataEmailField             Field used as email
 * - string $personalDataPtable                 ptable to store the personal data with
 */
trait PersonalDataTrait
{
    protected static array $personalDataFieldsValues = [];

    public function shouldManagePersonalData(): bool
    {
        return true;
    }

    /**
     * Delete the current record and return the number of affected rows.
     */
    public function delete(): int
    {
        if ($this->shouldManagePersonalData()) {
            self::getPersonalDataManager()->deleteByPidAndPtable(
                $this->getPersonalDataPidFieldValue(),
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
        if (! $this->shouldManagePersonalData()) {
            return;
        }

        $manager = self::getPersonalDataManager();
        $personalDatas = $manager->findByPidAndPtable($this->getPersonalDataPidFieldValue(), $this->getPersonalDataPtable());

        if ($personalDatas instanceof Collection) {
            $manager->applyPersonalDataTo($this, $personalDatas);
        }
    }

    /**
     * Anonymize personal data attached to the current object and apply them.
     */
    public function anonymize(): void
    {
        if (! $this->shouldManagePersonalData()) {
            return;
        }

        self::getPersonalDataManager()->anonymizeByPidAndPtableAndEmail(
            $this->getPersonalDataPidFieldValue(),
            $this->getPersonalDataPtable(),
            $this->getPersonalDataEmailFieldValue()
        );

        $this->refresh();
    }

    public function getPersonalDataFieldsDefaultValues(): array
    {
        return static::$personalDataFieldsDefaultValues;
    }

    public function getPersonalDataFieldsDefaultValueForField(string $field): string
    {
        return (string) $this->getPersonalDataFieldsDefaultValues()[$field];
    }

    public function getPersonalDataFieldsAnonymizedValues(): array
    {
        return static::$personalDataFieldsAnonymizedValues;
    }

    public function getPersonalDataFieldsAnonymizedValueForField(string $field): string
    {
        return (string) $this->getPersonalDataFieldsAnonymizedValues()[$field];
    }

    public function getPersonalDataFieldsNames(): array
    {
        return static::$personalDataFieldsNames;
    }

    public function isFieldInPersonalDataFieldsNames(string $field): bool
    {
        return \in_array($field, $this->getPersonalDataFieldsNames(), true);
    }

    public function getPersonalDataPidField(): string
    {
        return static::$personalDataPidField;
    }

    public function getPersonalDataEmailField(): string
    {
        return static::$personalDataEmailField;
    }

    public function getPersonalDataPtable(): string
    {
        return static::$personalDataPtable;
    }

    public function getPersonalDataPidFieldValue(): int
    {
        return (int) $this->{$this->getPersonalDataPidField()};
    }

    public function getPersonalDataEmailFieldValue(): string
    {
        return (string) ($this->{$this->getPersonalDataEmailField()} ?? '');
    }

    /**
     * Find a single record by its primary key.
     *
     * Models registered by another class for the same table are detached, so that
     * the personal data are applied.
     *
     * @return static|null
     */
    public static function findByPk($varValue, array $arrOptions = [])
    {
        $obj = parent::findByPk($varValue, $arrOptions);

        if ($obj instanceof Model && ! $obj instanceof self) {
            $obj->detach(false);

            return static::findByPk($varValue, $arrOptions);
        }

        return $obj;
    }

    /**
     * Find a single record by its ID or alias.
     *
     * @return static|null
     */
    public static function findByIdOrAlias($varId, array $arrOptions = [])
    {
        $obj = parent::findByIdOrAlias($varId, $arrOptions);

        if ($obj instanceof Model && ! $obj instanceof self) {
            $obj->detach(false);

            return static::findByIdOrAlias($varId, $arrOptions);
        }

        return $obj;
    }

    /**
     * Find multiple records by their IDs.
     */
    public static function findMultipleByIds($arrIds, array $arrOptions = [])
    {
        $obj = parent::findMultipleByIds($arrIds, $arrOptions);

        if ($obj instanceof Collection && self::detachForeignModels($obj)) {
            return static::findMultipleByIds($arrIds, $arrOptions);
        }

        return $obj;
    }

    /**
     * Create a model from a database result.
     *
     * @return static
     */
    protected static function createModelFromDbResult(Result $objResult)
    {
        $model = parent::createModelFromDbResult($objResult);
        $model?->findAndApplyPersonalData();

        return $model;
    }

    /**
     * Create a Collection object.
     */
    protected static function createCollection(array $arrModels, $strTable): \Contao\Model\Collection
    {
        return self::applyPersonalDataToCollection(parent::createCollection($arrModels, $strTable));
    }

    /**
     * Create a new collection from a database result.
     */
    protected static function createCollectionFromDbResult(Result $objResult, $strTable): \Contao\Model\Collection
    {
        return self::applyPersonalDataToCollection(parent::createCollectionFromDbResult($objResult, $strTable));
    }

    /**
     * Modify the current row before it is stored in the database: the personal data
     * are kept aside, and replaced by their default values.
     */
    protected function preSave(array $arrSet): array
    {
        $arrSet = parent::preSave($arrSet);

        if (! $this->shouldManagePersonalData()) {
            return $arrSet;
        }

        foreach ($this->getPersonalDataFieldsNames() as $personalDataFieldName) {
            // Field not modified: do not apply the default behaviour, let it be
            if (! \array_key_exists($personalDataFieldName, $this->arrModified)) {
                continue;
            }

            self::$personalDataFieldsValues[$personalDataFieldName] = $arrSet[$personalDataFieldName];
            $arrSet[$personalDataFieldName] = $this->getPersonalDataFieldsDefaultValueForField($personalDataFieldName);
        }

        return $arrSet;
    }

    /**
     * Modify the current row after it has been stored in the database: the personal
     * data kept aside are stored by the Personal Data Manager.
     */
    protected function postSave($intType): void
    {
        if ($this->shouldManagePersonalData()) {
            self::getPersonalDataManager()->insertOrUpdateForPidAndPtableAndEmail(
                $this->getPersonalDataPidFieldValue(),
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
     * @return Model|Model[]|Collection|null
     */
    protected static function find(array $arrOptions)
    {
        $obj = parent::find($arrOptions);

        if ($obj instanceof Collection) {
            return self::detachForeignModels($obj) ? static::find($arrOptions) : $obj;
        }

        if ($obj instanceof Model && ! $obj instanceof self) {
            $obj->detach(false);

            return static::find($arrOptions);
        }

        return $obj;
    }

    private static function getPersonalDataManager(): PersonalDataManager
    {
        return System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');
    }

    /**
     * Detach the models of the collection registered by another class for the same
     * table.
     *
     * @return bool True if at least one model has been detached
     */
    private static function detachForeignModels(Collection $collection): bool
    {
        $detached = false;

        foreach ($collection->getModels() as $model) {
            if (! $model instanceof self) {
                $model->detach(false);
                $detached = true;
            }
        }

        return $detached;
    }

    private static function applyPersonalDataToCollection(Collection $collection): Collection
    {
        foreach ($collection->getModels() as $model) {
            $model->findAndApplyPersonalData();
        }

        return $collection;
    }
}
