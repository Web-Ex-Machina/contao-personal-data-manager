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

use Contao\File;
use Contao\FilesModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\System;
use Exception;
use InvalidArgumentException;
use WEM\PersonalDataManagerBundle\Model\PersonalData as PersonalDataModel;
use WEM\PersonalDataManagerBundle\Model\PersonalDataAccessToken as PersonalDataAccessTokenModel;
use WEM\PersonalDataManagerBundle\Model\Traits\PersonalDataTrait;
use WEM\UtilsBundle\Classes\StringUtil;
use ZipArchive;

class PersonalDataManager
{
    /** @var PersonalDataManagerCsvFormatter */
    private $csvFormatter;

    public function __construct(
        PersonalDataManagerCsvFormatter $csvFormatter
    ) {
        $this->csvFormatter = $csvFormatter;
    }

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

        return PersonalDataModel::findByPidAndPtable(
            $object->getPersonalDataPidFieldValue(),
            $object->getPersonalDataPtable()
        );
    }

    /**
     * Retrieves personal data linked to a ptable.
     *
     * @param string $ptable The ptable
     *
     * @return Collection|null The associated personal data
     */
    public function findByPtable(string $ptable): ?Collection
    {
        return PersonalDataModel::findByPtable($ptable);
    }

    /**
     * Delete personal data linked to a ptable.
     *
     * @param string $ptable The ptable
     *
     * @return array The deleted ids
     */
    public function deleteByPtable(string $ptable): array
    {
        return PersonalDataModel::deleteByPtable($ptable);
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
        return PersonalDataModel::findByPidAndPtable($pid, $ptable);
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
        return PersonalDataModel::deleteByPidAndPtable($pid, $ptable);
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
    public function anonymizeByEmail(string $email): ?array
    {
        $anonymized = [];
        $pdms = PersonalDataModel::findByEmail($email);

        if (isset($GLOBALS['WEM_HOOKS']['anonymizeByEmail']) && \is_array($GLOBALS['WEM_HOOKS']['anonymizeByEmail'])) {
            foreach ($GLOBALS['WEM_HOOKS']['anonymizeByEmail'] as $callback) {
                $pdms = System::importStatic($callback[0])->{$callback[1]}($email, $pdms);
            }
        }

        if (!$pdms) {
            return null;
        }
        while ($pdms->next()) {
            if (!\array_key_exists($pdms->ptable, $anonymized)) {
                $anonymized[$pdms->ptable] = [];
            }

            if (!\array_key_exists($pdms->pid, $anonymized[$pdms->ptable])) {
                $anonymized[$pdms->ptable][$pdms->pid] = [];
            }

            $anonymized[$pdms->ptable][$pdms->pid][$pdms->field] = $this->anonymize($pdms->current());
        }

        return $anonymized;
    }

    /**
     * Export personal data linked to an email.
     *
     * @param string $email The email
     */
    public function exportByEmail(string $email): string
    {
        $pdms = PersonalDataModel::findByEmail($email);

        if (isset($GLOBALS['WEM_HOOKS']['exportByEmail']) && \is_array($GLOBALS['WEM_HOOKS']['exportByEmail'])) {
            foreach ($GLOBALS['WEM_HOOKS']['exportByEmail'] as $callback) {
                $pdms = System::importStatic($callback[0])->{$callback[1]}($email, $pdms);
            }
        }

        $csvContent = $this->csvFormatter->formatPersonalDataForCsv($pdms);

        $zipName = $email.'.zip';
        $zip = new ZipArchive();
        $res = $zip->open($zipName, ZipArchive::CREATE);

        if (!$res) {
            throw new Exception('Unable to create zip archive');
        }

        $zip->addFromString('data.csv', mb_convert_encoding(StringUtil::decodeEntities($csvContent), 'UTF-16LE', 'UTF-8'));
        if ($pdms) {
            $zip = $this->addAllLinkedFilesToZipArchive($zip, $pdms);
        }
        $zip->close();

        return $zipName;
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
        return PersonalDataModel::deleteByPidAndPtableAndEmail($pid, $ptable, $email);
    }

    /**
     * Anonymize personal data linked to a pid, a ptable and an email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     */
    public function anonymizeByPidAndPtableAndEmail(string $pid, string $ptable, string $email): ?array
    {
        $anonymized = [];
        $pdms = PersonalDataModel::findByPidAndPtableAndEmail($pid, $ptable, $email);

        if (isset($GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail']) && \is_array($GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'])) {
            foreach ($GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'] as $callback) {
                $pdms = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $pdms);
            }
        }

        if (!$pdms) {
            return null;
        }
        while ($pdms->next()) {
            if (!\array_key_exists($pdms->ptable, $anonymized)) {
                $anonymized[$pdms->ptable] = [];
            }

            if (!\array_key_exists($pdms->pid, $anonymized[$pdms->ptable])) {
                $anonymized[$pdms->ptable][$pdms->pid] = [];
            }

            $anonymized[$pdms->ptable][$pdms->pid][$pdms->field] = $this->anonymize($pdms->current());
        }

        return $anonymized;
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
        $pdms = PersonalDataModel::findByPidAndPtableAndEmail($pid, $ptable, $email);

        if (isset($GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail']) && \is_array($GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'])) {
            foreach ($GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'] as $callback) {
                $pdms = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $pdms);
            }
        }

        $csvContent = $this->csvFormatter->formatPersonalDataForCsv($pdms);

        $zipName = $email.'-'.$ptable.'-'.$pid.'.zip';
        $zip = new ZipArchive();
        $res = $zip->open($zipName, ZipArchive::CREATE);

        if (!$res) {
            throw new Exception('Unable to create zip archive');
        }

        $zip->addFromString('data.csv', mb_convert_encoding(StringUtil::decodeEntities($csvContent), 'UTF-16LE', 'UTF-8'));
        if ($pdms) {
            $zip = $this->addAllLinkedFilesToZipArchive($zip, $pdms);
        }
        $zip->close();

        return $zipName;
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
    public function findOneByPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field): ?PersonalDataModel
    {
        return PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);
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
        return PersonalDataModel::deleteByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);
    }

    /**
     * Anonymize personal data linked to a pid, a ptable and an email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     */
    public function anonymizeByPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field): ?string
    {
        $pdm = PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);

        if (isset($GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField']) && \is_array($GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField'])) {
            foreach ($GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField'] as $callback) {
                $pdm = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $pdm);
            }
        }

        if (!$pdm) {
            return null;
        }

        return $this->anonymize($pdm->current());
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
    public function getUnecryptedValueByPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field): ?string
    {
        $personalData = PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);
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
        $pdm = PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field) ?? new PersonalDataModel();
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

    public function anonymize(PersonalDataModel $personalData): ?string
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');

        $originalModel = Model::getClassFromTable($personalData->ptable);

        $objFile = null;
        if ($this->isPersonalDataLinkedToFile($personalData)) {
            $objFile = $this->getFileByPidAndPtableAndEmailAndField($personalData->pid, $personalData->ptable, $personalData->email, $personalData->field);
        }

        $obj = new $originalModel();
        $anonymizedValue = $obj->getPersonalDataFieldsAnonymizedValueForField($personalData->field);
        $value = $encryptionService->decrypt($personalData->value);
        $personalData->value = $anonymizedValue;
        $personalData->anonymized = true;
        $personalData->anonymizedAt = time();
        $personalData->save();

        if (isset($GLOBALS['WEM_HOOKS']['anonymize']) && \is_array($GLOBALS['WEM_HOOKS']['anonymize'])) {
            foreach ($GLOBALS['WEM_HOOKS']['anonymize'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($personalData, $value, $objFile);
            }
        }

        // here we should anonymize the file if pdm linked to one
        if ($objFile) {
            $objFileDeletedTplContent = file_get_contents(TL_ROOT.'/public/bundles/wempersonaldatamanager/images/file_deleted.jpg');

            $objFile->write($objFileDeletedTplContent);
            $objFile->renameTo(str_replace($objFile->name, sprintf('file_deleted_%s.jpg', time()), $objFile->path));
            $objFile->close();
        }

        return $anonymizedValue;
    }

    public function getHrefByPidAndPtableAndEmail(string $pid, string $ptable, string $email)
    {
        $href = '';

        if (isset($GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail']) && \is_array($GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail'])) {
            foreach ($GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail'] as $callback) {
                $href = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $href);
            }
        }

        return $href;
    }

    public function getFileByPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field): ?File
    {
        $pdm = PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);
        if (!$pdm) {
            throw new Exception('Unable to find personal data');
        }
        if (!$this->isPersonalDataLinkedToFile($pdm)) {
            throw new Exception('Personal data not linked to a file');
        }

        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');

        $value = $encryptionService->decrypt($pdm->value);
        $objFileModel = null;

        if (FilesModel::getTable() === $ptable) {
            switch ($field) {
                case 'id':
                case 'name':
                case 'path':
                    $objFileModel = FilesModel::findOneBy($field, $value);
                break;
                case 'uuid':
                    $objFileModel = FilesModel::findByUuid($value);
                break;
            }
        }

        if (isset($GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField']) && \is_array($GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'])) {
            foreach ($GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'] as $callback) {
                $objFileModel = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $field, $pdm, $value, $objFileModel);
            }
        }

        if (!$objFileModel) {
            throw new Exception('Unable to find the file');
        }

        return new File($objFileModel->path);
    }

    public function isPersonalDataLinkedToFile(PersonalDataModel $pdm): bool
    {
        $isLinkedToFile = FilesModel::getTable() === $pdm->ptable;

        if (isset($GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile']) && \is_array($GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile'])) {
            foreach ($GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile'] as $callback) {
                $isLinkedToFile = System::importStatic($callback[0])->{$callback[1]}($pdm, $isLinkedToFile);
            }
        }

        return $isLinkedToFile;
    }

    /**
     * Check if an email + token couple is valid.
     *
     * @param string $email The email
     * @param string $token The token
     *
     * @return bool True if valid
     */
    public function isEmailTokenCoupleValid(string $email, string $token): bool
    {
        return PersonalDataAccessTokenModel::isEmailTokenCoupleValid($email, $token);
    }

    /**
     * Inserts a row for an email.
     *
     * @param string $email The email
     */
    public function insertForEmail(string $email): PersonalDataAccessTokenModel
    {
        $pdmats = PersonalDataAccessTokenModel::findBy('email', $email);

        if ($pdmats) {
            while ($pdmats->next()) {
                $pdmat = $pdmats->current();
                if ($pdmat->isValid()) {
                    $pdmat->expiresAt = time();
                    $pdmat->save();
                }
            }
        }

        return PersonalDataAccessTokenModel::insertForEmail($email);
    }

    /**
     * update expiration date for an existing token.
     */
    public function updateTokenExpiration(string $token): PersonalDataAccessTokenModel
    {
        $obj = PersonalDataAccessTokenModel::findOneBy('token', $token);
        if (!$obj
        || !$obj->isValid()
        ) {
            throw new Exception('The token is invalid');
        }
        $obj->updateExpiration();

        return $obj;
    }

    public function putTokenInSession(string $token): void
    {
        $session = System::getContainer()->get('session'); // Init session
        $session->set('wem_pdm_token', $token);
    }

    public function clearTokenInSession(): void
    {
        $session = System::getContainer()->get('session'); // Init session
        $session->set('wem_pdm_token', '');
    }

    public function getTokenInSession(): ?string
    {
        $session = System::getContainer()->get('session'); // Init session

        return $session->get('wem_pdm_token');
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

    protected function addAllLinkedFilesToZipArchive(ZipArchive $zip, $pdms): ZipArchive
    {
        $pdms->reset();
        while ($pdms->next()) {
            $pdm = $pdms->current();
            if ($this->isPersonalDataLinkedToFile($pdm)) {
                $objFile = $this->getFileByPidAndPtableAndEmailAndField($pdm->pid, $pdm->ptable, $pdm->email, $pdm->field);
                if ($objFile) {
                    $zip->addFromString(sprintf('%s/%s/%s', $pdm->ptable, $pdm->pid, $objFile->name), $objFile->getContent());
                }
            }
        }

        return $zip;
    }
}
