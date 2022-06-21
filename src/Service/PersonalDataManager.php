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
use InvalidArgumentException;
use WEM\PersonalDataManagerBundle\Model\PersonalData as PersonalDataModel;

class PersonalDataManager
{
    public function findForObject(Model $object)
    {
        $this->validateObject($object);

        return PersonalDataModel::findByPidAndPTable(
            $object->{$object->personalDataPidField},
            $object->getPersonalDataPtable()
        );
    }

    public function findForPidAndPtable(string $pid, string $ptable)
    {
        return PersonalDataModel::findByPidAndPTable($pid, $ptable);
    }

    public function deleteForPidAndPtable(string $pid, string $ptable)
    {
        return PersonalDataModel::deleteByPidAndPTable($pid, $ptable);
    }

    public function findForEmail(string $email)
    {
        return PersonalDataModel::findByEmail($email);
    }

    public function deleteForEmail(string $email)
    {
        return PersonalDataModel::deleteByEmail($email);
    }

    public function deleteForPidAndPtableAndEmail(string $pid, string $ptable, string $email)
    {
        return PersonalDataModel::deleteByPidAndPTableAndEmail($pid, $ptable, $email);
    }

    public function findOneByPidAndPTableAndEmailAndField(string $pid, string $ptable, string $email, string $field)
    {
        return PersonalDataModel::findOneByPidAndPTableAndEmailAndField($pid, $ptable, $email, $field);
    }

    public function getUnecryptedValueByPidAndPTableAndEmailAndField(string $pid, string $ptable, string $email, string $field)
    {
        $personalData = PersonalDataModel::findOneByPidAndPTableAndEmailAndField($pid, $ptable, $email, $field);
        if (!$personalData) {
            return null;
        }
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');

        return $encryptionService->decrypt($personalData->value);
    }

    public function findAndApplyForObject(Model $object): Model
    {
        $this->validateObject($object);
        $personalDatas = $this->findForObject($object);
        if ($personalDatas) {
            $object = $this->applyPersonalDataTo($object, $personalDatas);
        }

        return $object;
    }

    public function applyPersonalDataTo($object, $personalDatas)
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');
        while ($personalDatas->next()) {
            $object->{$personalDatas->field} = $encryptionService->decrypt($personalDatas->value); // We should unencrypt here
        }

        return $object;
    }

    public function insertOrUpdateForPidAndPtableAndEmail(string $pid, string $ptable, string $email, array $datas): array
    {
        $pdms = [];
        foreach ($datas as $field => $value) {
            $pdms[] = $this->insertOrUpdateForPidAndPtableAndEmailAndField($pid, $ptable, $email, $field, $value);
        }

        return $pdms;
    }

    public function insertOrUpdateForPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field, $value): PersonalDataModel
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');
        $pdm = PersonalDataModel::findOneByPidAndPTableAndEmailAndField($pid, $ptable, $email, $field) ?? new PersonalDataModel();
        $pdm->pid = $pid;
        $pdm->ptable = $ptable;
        $pdm->email = $email;
        $pdm->field = $field;
        $pdm->value = $encryptionService->encrypt($value); // we should crypt here
        $pdm->createdAt = $pdm->createdAt ?? time();
        $pdm->tstamp = time();
        $pdm->save();

        return $pdm;
    }

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
