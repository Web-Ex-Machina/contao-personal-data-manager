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

namespace WEM\PersonalDataManagerBundle\Service;

use Contao\Model;
use Contao\Model\Collection;
use InvalidArgumentException;
use WEM\PersonalDataManagerBundle\Model\PersonalData as PersonalDataModel;
use WEM\PersonalDataManagerBundle\Model\Traits\PersonalDataTrait;

class PersonalDataManager
{
    /**
     * Retrieves personal data linked to the object.
     *
     * @param PersonalDataTrait $object The object
     *
     * @return Collection|null The associated personal data
     */
    public function findForObject(PersonalDataTrait $object): ?Collection
    {
        $this->validateObject($object);

        return PersonalDataModel::findByPidAndPTable(
            $object->{$object->personalDataPidField},
            $object->getPersonalDataPtable()
        );
    }

    /**
     * Retrieves personal data linked to a pid and a ptable.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     *
     * @return Collection|null The associated personal data
     */
    public function findByPidAndPtable(string $pid, string $ptable): ?Collection
    {
        return PersonalDataModel::findByPidAndPTable($pid, $ptable);
    }

    /**
     * Delete personal data linked to a pid and a ptable.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     *
     * @return array The deleted ids
     */
    public function deleteByPidAndPtable(string $pid, string $ptable): array
    {
        return PersonalDataModel::deleteByPidAndPTable($pid, $ptable);
    }

    /**
     * Retrieves personal data linked to an email.
     *
     * @param string $email The email
     *
     * @return Collection|null The associated personal data
     */
    public function findByEmail(string $email): ?Collection
    {
        return PersonalDataModel::findByEmail($email);
    }

    /**
     * Delete personal data linked to an email.
     *
     * @param string $email The email
     *
     * @return array The deleted ids
     */
    public function deleteByEmail(string $email): array
    {
        return PersonalDataModel::deleteByEmail($email);
    }

    /**
     * Anonymize personal data linked to an email.
     *
     * @param string $email The email
     */
    public function anonymizeByEmail(string $email): void
    {
        $pdms = PersonalDataModel::findByEmail($email);
        if (!$pdms) {
            return;
        }
        while ($pdms->next()) {
            $this->anonymize($pdms->current());
        }
    }

    /**
     * Export personal data linked to an email.
     *
     * @param string $email The email
     */
    public function exportByEmail(string $email): string
    {
        $pdms = PersonalDataModel::findByEmail($email);

        return $this->formatPersonalDataForCsv($pdms);
    }

    /**
     * Delete personal data linked to a pid, a ptable and an email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     *
     * @return array The deleted ids
     */
    public function deleteByPidAndPtableAndEmail(string $pid, string $ptable, string $email): array
    {
        return PersonalDataModel::deleteByPidAndPTableAndEmail($pid, $ptable, $email);
    }

    /**
     * Anonymize personal data linked to a pid, a ptable and an email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     */
    public function anonymizeByPidAndPtableAndEmail(string $pid, string $ptable, string $email): void
    {
        $pdms = PersonalDataModel::findByPidAndPTableAndEmail($pid, $ptable, $email);
        if (!$pdms) {
            return;
        }
        while ($pdms->next()) {
            $this->anonymize($pdms->current());
        }
    }

    /**
     * Export personal data linked to a pid, a ptable and an email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     */
    public function exportByPidAndPtableAndEmail(string $pid, string $ptable, string $email): string
    {
        $pdms = PersonalDataModel::findByPidAndPTableAndEmail($pid, $ptable, $email);

        return $this->formatPersonalDataForCsv($pdms);
    }

    /**
     * Retrieves a single personal data linked to a pid, a ptable, an email and a field.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     * @param string $email  The field
     *
     * @return PersonalDataModel|null The associated personal data
     */
    public function findOneByPidAndPTableAndEmailAndField(string $pid, string $ptable, string $email, string $field): ?PersonalDataModel
    {
        return PersonalDataModel::findOneByPidAndPTableAndEmailAndField($pid, $ptable, $email, $field);
    }

    /**
     * Delete personal data linked to a pid, a ptable and an email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     *
     * @return array The deleted ids
     */
    public function deleteByPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field): array
    {
        return PersonalDataModel::deleteByPidAndPTableAndEmailAndField($pid, $ptable, $email, $field);
    }

    /**
     * Anonymize personal data linked to a pid, a ptable and an email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     */
    public function anonymizeByPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field): void
    {
        $pdm = PersonalDataModel::findOneByPidAndPTableAndEmailAndField($pid, $ptable, $email, $field);
        if (!$pdm) {
            return;
        }
        $this->anonymize($pdm->current());
    }

    /**
     * Retrieves a single personal data unecrypted value linked to a pid, a ptable, an email and a field.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     * @param string $email  The field
     *
     * @return string|null The unencrypted associated personal data value
     */
    public function getUnecryptedValueByPidAndPTableAndEmailAndField(string $pid, string $ptable, string $email, string $field): ?string
    {
        $personalData = PersonalDataModel::findOneByPidAndPTableAndEmailAndField($pid, $ptable, $email, $field);
        if (!$personalData) {
            return null;
        }
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');

        return $personalData->anonymized ? $personalData->value : $encryptionService->decrypt($personalData->value);
    }

    /**
     * Retrieves personal data linked to the object and applies them.
     *
     * @param PersonalDataTrait $object The object
     *
     * @return PersonalDataTrait The modified object
     */
    public function findAndApplyForObject(PersonalDataTrait $object): PersonalDataTrait
    {
        $this->validateObject($object);
        $personalDatas = $this->findForObject($object);
        if ($personalDatas) {
            $object = $this->applyPersonalDataTo($object, $personalDatas);
        }

        return $object;
    }

    /**
     * Retrieves personal data linked to the object and applies them.
     *
     * @param mixed      $object        The object
     * @param Collection $personalDatas The personal data collection
     *
     * @return mixed The modified object
     */
    public function applyPersonalDataTo($object, Collection $personalDatas)
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');
        while ($personalDatas->next()) {
            $object->{$personalDatas->field} = $personalDatas->anonymized ? $personalDatas->value : $encryptionService->decrypt($personalDatas->value);
        }

        return $object;
    }

    /**
     * Inserts (or update) personal data linked to a pid, a ptable and an email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     * @param array  $datas  Array of datas (['field'=>'value'])
     *
     * @return array The personal datas inserted (or updated)
     */
    public function insertOrUpdateForPidAndPtableAndEmail(string $pid, string $ptable, string $email, array $datas): array
    {
        $pdms = [];
        foreach ($datas as $field => $value) {
            $pdms[] = $this->insertOrUpdateForPidAndPtableAndEmailAndField($pid, $ptable, $email, $field, $value);
        }

        return $pdms;
    }

    /**
     * Inserts (or update) personal data linked to a pid, a ptabl, an email and a field.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     * @param string $field  The field
     * @param mixed  $value  The value
     *
     * @return PersonalDataModel The personal data record
     */
    public function insertOrUpdateForPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field, $value): PersonalDataModel
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');
        $pdm = PersonalDataModel::findOneByPidAndPTableAndEmailAndField($pid, $ptable, $email, $field) ?? new PersonalDataModel();
        $pdm->pid = $pid;
        $pdm->ptable = $ptable;
        $pdm->email = $email;
        $pdm->field = $field;
        if ($pdm->anonymized && (string) $value === (string) $pdm->value) {
            // if pdm anonymized and values are equals, do nothing
        } else {
            // else, save the value and de-anonymize data
            $pdm->value = $encryptionService->encrypt($value);
            $pdm->anonymized = '';
            $pdm->anonymizedAt = '';
        }
        $pdm->createdAt = $pdm->createdAt ?? time();
        $pdm->tstamp = time();
        $pdm->save();

        return $pdm;
    }

    public function anonymize(PersonalDataModel $personalData): void
    {
        $originalModel = Model::getClassFromTable($personalData->ptable);
        $obj = new $originalModel();
        $personalData->value = $obj->getPersonalDataFieldsAnonymizedValueForField($personalData->field);
        $personalData->anonymized = true;
        $personalData->anonymizedAt = time();
        $personalData->save();
    }

    public function formatPersonalDataForCsv(?Collection $personalData): string
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');
        $csv = [
            'Entity;Mail;Field;Value',
        ];
        if ($personalData) {
            while ($personalData->next()) {
                $row = [
                    $personalData->ptable,
                    $personalData->email,
                    $GLOBALS['TL_DCA'][$personalData->ptable]['fields'][$personalData->field]['label'] ?? $personalData->field,
                    $encryptionService->decrypt($personalData->value),
                ];

                $csv[] = implode(';', $row);
            }
        }

        return implode("\n", $csv);
    }

    /**
     * Validate that an object can be manipulated by this service.
     *
     * @param mixed $object The object
     */
    public function validateObject($object): void
    {
        if (!is_a($object, Model::class)) {
            throw new InvalidArgumentException('The object is not a Contao Model.');
        }

        if (!\in_array('WEM\PersonalDataManagerBundle\Model\Trait\PersonalDataTrait', class_uses($object), true)) {
            throw new InvalidArgumentException('The object does not use the "PersonalDataTrait".');
        }
    }
}
