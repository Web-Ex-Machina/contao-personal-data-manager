<?php

declare(strict_types=1);

namespace WEM\PersonalDataManagerBundle\Service;

use Contao\File;
use Contao\FilesModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\System;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\RequestStack;
use WEM\PersonalDataManagerBundle\Model\PersonalData as PersonalDataModel;
use WEM\PersonalDataManagerBundle\Model\PersonalDataAccessToken as PersonalDataAccessTokenModel;
use WEM\PersonalDataManagerBundle\Model\Traits\PersonalDataTrait;
use WEM\UtilsBundle\Classes\Encryption;
use WEM\UtilsBundle\Classes\StringUtil;

class PersonalDataManager
{
    public const string SESSION_EMAIL = 'wem_pdm_email';

    public const string SESSION_TOKEN = 'wem_pdm_token';

    public function __construct(
        private readonly PersonalDataManagerCsvFormatter $csvFormatter,
        private readonly Encryption $encryption,
        private readonly RequestStack $requestStack,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * Retrieves personal data linked to the object.
     *
     * @throws \Exception
     */
    public function findForObject(Model $object): Collection|null
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
     * @throws \Exception
     */
    public function findByPtable(string $ptable): Collection|null
    {
        return PersonalDataModel::findByPtable($ptable);
    }

    /**
     * Delete personal data linked to a ptable.
     *
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public function deleteByPtable(string $ptable): array
    {
        return PersonalDataModel::deleteByPtable($ptable);
    }

    /**
     * Retrieves personal data linked to a pid and a ptable.
     *
     * @throws \Exception
     */
    public function findByPidAndPtable(int $pid, string $ptable): Collection|null
    {
        return PersonalDataModel::findByPidAndPtable($pid, $ptable);
    }

    /**
     * Delete personal data linked to a pid and a ptable.
     *
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public function deleteByPidAndPtable(int $pid, string $ptable): array
    {
        return PersonalDataModel::deleteByPidAndPtable($pid, $ptable);
    }

    /**
     * Retrieves personal data linked to an email.
     *
     * @throws \Exception
     */
    public function findByEmail(string $email): Collection|null
    {
        return PersonalDataModel::findByEmail($email);
    }

    /**
     * Delete personal data linked to an email.
     *
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public function deleteByEmail(string $email): array
    {
        return PersonalDataModel::deleteByEmail($email);
    }

    /**
     * Anonymize personal data linked to an email.
     *
     * @throws \Exception
     */
    public function anonymizeByEmail(string $email): array|null
    {
        $pdms = PersonalDataModel::findByEmail($email);

        foreach ($GLOBALS['WEM_HOOKS']['anonymizeByEmail'] ?? [] as $callback) {
            $pdms = System::importStatic($callback[0])->{$callback[1]}($email, $pdms);
        }

        return $this->anonymizeCollection($pdms);
    }

    /**
     * Export personal data linked to an email.
     *
     * @return string The path to the generated zip archive
     *
     * @throws \Exception
     */
    public function exportByEmail(string $email): string
    {
        $pdms = PersonalDataModel::findByEmail($email);

        foreach ($GLOBALS['WEM_HOOKS']['exportByEmail'] ?? [] as $callback) {
            $pdms = System::importStatic($callback[0])->{$callback[1]}($email, $pdms);
        }

        return $this->createZipArchive($pdms);
    }

    /**
     * Delete personal data linked to a pid, a ptable and an email.
     *
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public function deleteByPidAndPtableAndEmail(int $pid, string $ptable, string $email): array
    {
        return PersonalDataModel::deleteByPidAndPtableAndEmail($pid, $ptable, $email);
    }

    /**
     * Anonymize personal data linked to a pid, a ptable and an email.
     *
     * @throws \Exception
     */
    public function anonymizeByPidAndPtableAndEmail(int $pid, string $ptable, string $email): array|null
    {
        $pdms = PersonalDataModel::findByPidAndPtableAndEmail($pid, $ptable, $email);

        foreach ($GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmail'] ?? [] as $callback) {
            $pdms = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $pdms);
        }

        return $this->anonymizeCollection($pdms);
    }

    /**
     * Export personal data linked to a pid, a ptable and an email.
     *
     * @return string The path to the generated zip archive
     *
     * @throws \Exception
     */
    public function exportByPidAndPtableAndEmail(int $pid, string $ptable, string $email): string
    {
        $pdms = PersonalDataModel::findByPidAndPtableAndEmail($pid, $ptable, $email);

        foreach ($GLOBALS['WEM_HOOKS']['exportByPidAndPtableAndEmail'] ?? [] as $callback) {
            $pdms = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $pdms);
        }

        return $this->createZipArchive($pdms);
    }

    /**
     * Retrieves a single personal data linked to a pid, a ptable, an email and a field.
     *
     * @throws \Exception
     */
    public function findOneByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): PersonalDataModel|null
    {
        return PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);
    }

    /**
     * Delete personal data linked to a pid, a ptable, an email and a field.
     *
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public function deleteByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): array
    {
        return PersonalDataModel::deleteByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);
    }

    /**
     * Anonymize personal data linked to a pid, a ptable, an email and a field.
     *
     * @throws \Exception
     */
    public function anonymizeByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): string|null
    {
        $pdm = PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);

        foreach ($GLOBALS['WEM_HOOKS']['anonymizeByPidAndPtableAndEmailAndField'] ?? [] as $callback) {
            $pdm = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $pdm);
        }

        if (!$pdm instanceof PersonalDataModel) {
            return null;
        }

        return $this->anonymize($pdm);
    }

    /**
     * Retrieves a single personal data unencrypted value linked to a pid, a ptable, an email and a field.
     *
     * @throws \Exception
     */
    public function getUnecryptedValueByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): mixed
    {
        $personalData = PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);

        if (!$personalData instanceof PersonalDataModel) {
            return null;
        }

        return $this->getCleanValue($personalData);
    }

    /**
     * Retrieves personal data linked to the object and applies them.
     *
     * @throws \Exception
     */
    public function findAndApplyForObject(Model $object): Model
    {
        $personalDatas = $this->findForObject($object);

        if ($personalDatas instanceof Collection) {
            $object = $this->applyPersonalDataTo($object, $personalDatas);
        }

        return $object;
    }

    /**
     * Applies the personal data to the object.
     */
    public function applyPersonalDataTo(object $object, Collection $personalDatas): object
    {
        foreach ($personalDatas as $personalData) {
            $object->{$personalData->field} = $this->getCleanValue($personalData);
        }

        return $object;
    }

    /**
     * Inserts (or update) personal data linked to a pid, a ptable and an email.
     *
     * @param array<string, mixed> $datas Array of datas (['field'=>'value'])
     *
     * @return array<PersonalDataModel> The personal datas inserted (or updated)
     *
     * @throws \Exception
     */
    public function insertOrUpdateForPidAndPtableAndEmail(int $pid, string $ptable, string $email, array $datas): array
    {
        $pdms = [];

        foreach ($datas as $field => $value) {
            $pdms[] = $this->insertOrUpdateForPidAndPtableAndEmailAndField($pid, $ptable, $email, $field, $value);
        }

        return $pdms;
    }

    /**
     * Inserts (or update) personal data linked to a pid, a ptable, an email and a field.
     *
     * @throws \Exception
     */
    public function insertOrUpdateForPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field, mixed $value): PersonalDataModel
    {
        $pdm = PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field) ?? new PersonalDataModel();
        $pdm->pid = $pid;
        $pdm->ptable = $ptable;
        $pdm->email = $email;
        $pdm->field = $field;

        // If the data is anonymized and the value did not change, keep it anonymized
        if (!$pdm->anonymized || (string) $value !== (string) $pdm->value) {
            if (\is_array($value)) {
                $pdm->altered = 'serialized';
                $value = serialize($value);
            }

            $pdm->value = (string) $this->encryption->encrypt_b64($value);
            $pdm->anonymized = '';
            $pdm->anonymizedAt = '';
        }

        $pdm->createdAt = $pdm->createdAt ?: time();
        $pdm->tstamp = time();
        $pdm->save();

        return $pdm;
    }

    /**
     * @throws \Exception
     */
    public function anonymize(PersonalDataModel $personalData): string|null
    {
        $originalModel = Model::getClassFromTable($personalData->ptable);

        $objFile = null;

        if ($this->isPersonalDataLinkedToFile($personalData)) {
            $objFile = $this->getFileByPidAndPtableAndEmailAndField((int) $personalData->pid, $personalData->ptable, $personalData->email, $personalData->field);
        }

        $anonymizedValue = (new $originalModel())->getPersonalDataFieldsAnonymizedValueForField($personalData->field);
        $value = $this->getCleanValue($personalData);

        $personalData->value = (string) $anonymizedValue;
        $personalData->anonymized = true;
        $personalData->anonymizedAt = time();
        $personalData->save();

        foreach ($GLOBALS['WEM_HOOKS']['anonymize'] ?? [] as $callback) {
            System::importStatic($callback[0])->{$callback[1]}($personalData, $value, $objFile);
        }

        // Replace the file content if the personal data is linked to one
        if ($objFile instanceof File) {
            $objFile->write((string) file_get_contents(Path::join($this->projectDir, 'public/bundles/wempersonaldatamanager/images/file_deleted.jpg')));
            $objFile->renameTo(str_replace($objFile->name, \sprintf('file_deleted_%s.jpg', time()), $objFile->path));
            $objFile->close();
        }

        return $anonymizedValue;
    }

    public function getHrefByPidAndPtableAndEmail(int $pid, string $ptable, string $email): string
    {
        $href = '';

        foreach ($GLOBALS['WEM_HOOKS']['getHrefByPidAndPtableAndEmail'] ?? [] as $callback) {
            $href = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $href);
        }

        return (string) $href;
    }

    /**
     * @throws \Exception
     */
    public function getFileByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): File
    {
        $pdm = PersonalDataModel::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);

        if (!$pdm instanceof PersonalDataModel) {
            throw new \Exception('Unable to find personal data');
        }

        if (!$this->isPersonalDataLinkedToFile($pdm)) {
            throw new \Exception('Personal data not linked to a file');
        }

        $value = $this->getCleanValue($pdm);
        $objFileModel = null;

        if (FilesModel::getTable() === $ptable) {
            $objFileModel = match ($field) {
                'id', 'name', 'path' => FilesModel::findOneBy($field, $value),
                'uuid' => FilesModel::findByUuid($value),
                default => null,
            };
        }

        foreach ($GLOBALS['WEM_HOOKS']['getFileByPidAndPtableAndEmailAndField'] ?? [] as $callback) {
            $objFileModel = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $field, $pdm, $value, $objFileModel);
        }

        if (!$objFileModel instanceof FilesModel) {
            throw new \Exception('Unable to find the file');
        }

        return new File($objFileModel->path);
    }

    public function isPersonalDataLinkedToFile(PersonalDataModel $pdm): bool
    {
        $isLinkedToFile = FilesModel::getTable() === $pdm->ptable;

        foreach ($GLOBALS['WEM_HOOKS']['isPersonalDataLinkedToFile'] ?? [] as $callback) {
            $isLinkedToFile = System::importStatic($callback[0])->{$callback[1]}($pdm, $isLinkedToFile);
        }

        return (bool) $isLinkedToFile;
    }

    /**
     * Check if an email + token couple is valid.
     *
     * @throws \Exception
     */
    public function isEmailTokenCoupleValid(string $email, string $token): bool
    {
        return PersonalDataAccessTokenModel::isEmailTokenCoupleValid($email, $token);
    }

    /**
     * Inserts an access token for an email, invalidating the previous ones.
     */
    public function insertForEmail(string $email): PersonalDataAccessTokenModel
    {
        foreach (PersonalDataAccessTokenModel::findBy('email', $email) ?? [] as $pdmat) {
            if ($pdmat->isValid()) {
                $pdmat->expiresAt = time();
                $pdmat->save();
            }
        }

        return PersonalDataAccessTokenModel::insertForEmail($email);
    }

    /**
     * Update the expiration date of an existing token.
     *
     * @throws \Exception
     */
    public function updateTokenExpiration(string $token): PersonalDataAccessTokenModel
    {
        $obj = PersonalDataAccessTokenModel::findOneBy('token', $token);

        if (!$obj instanceof PersonalDataAccessTokenModel || !$obj->isValid()) {
            throw new \Exception('The token is invalid');
        }

        return $obj->updateExpiration();
    }

    public function putEmailInSession(string $email): void
    {
        $this->requestStack->getSession()->set(self::SESSION_EMAIL, $email);
    }

    public function getEmailInSession(): string|null
    {
        return $this->requestStack->getSession()->get(self::SESSION_EMAIL);
    }

    public function putTokenInSession(string $token): void
    {
        $this->requestStack->getSession()->set(self::SESSION_TOKEN, $token);
    }

    public function clearTokenInSession(): void
    {
        $this->requestStack->getSession()->set(self::SESSION_TOKEN, '');
    }

    public function getTokenInSession(): string|null
    {
        return $this->requestStack->getSession()->get(self::SESSION_TOKEN);
    }

    /**
     * Validate that an object can be manipulated by this service.
     */
    public function validateObject(mixed $object): void
    {
        if (!$object instanceof Model) {
            throw new \InvalidArgumentException('The object is not a Contao Model.');
        }

        if (!\in_array(PersonalDataTrait::class, class_uses($object), true)) {
            throw new \InvalidArgumentException('The object does not use the "PersonalDataTrait".');
        }
    }

    /**
     * Return the data decrypted and formatted depending on the "altered" column.
     */
    public function getCleanValue(PersonalDataModel $data): mixed
    {
        if ($data->anonymized) {
            return $data->value;
        }

        $value = $this->encryption->decrypt_b64($data->value);

        if ('serialized' === $data->altered) {
            return unserialize($value, ['allowed_classes' => false]);
        }

        return $value;
    }

    /**
     * @return array<string, array<int, array<string, string|null>>>|null
     *
     * @throws \Exception
     */
    private function anonymizeCollection(Collection|null $pdms): array|null
    {
        if (!$pdms instanceof Collection) {
            return null;
        }

        $anonymized = [];

        foreach ($pdms as $pdm) {
            $anonymized[$pdm->ptable][$pdm->pid][$pdm->field] = $this->anonymize($pdm);
        }

        return $anonymized;
    }

    /**
     * Create a zip archive in the system temporary directory, containing a CSV
     * export of the personal data and the files linked to them.
     *
     * @throws \Exception
     */
    private function createZipArchive(Collection|null $pdms): string
    {
        $zipName = tempnam(sys_get_temp_dir(), 'wem_pdm_');

        if (false === $zipName) {
            throw new \Exception('Unable to create zip archive');
        }

        $zip = new \ZipArchive();

        if (true !== $zip->open($zipName, \ZipArchive::OVERWRITE)) {
            throw new \Exception('Unable to create zip archive');
        }

        $csvContent = $this->csvFormatter->formatPersonalDataForCsv($pdms);
        $zip->addFromString('data.csv', mb_convert_encoding(StringUtil::decodeEntities($csvContent), 'UTF-16LE', 'UTF-8'));

        if ($pdms instanceof Collection) {
            $this->addAllLinkedFilesToZipArchive($zip, $pdms);
        }

        $zip->close();

        return $zipName;
    }

    /**
     * @throws \Exception
     */
    private function addAllLinkedFilesToZipArchive(\ZipArchive $zip, Collection $pdms): void
    {
        foreach ($pdms as $pdm) {
            if (!$this->isPersonalDataLinkedToFile($pdm)) {
                continue;
            }

            $objFile = $this->getFileByPidAndPtableAndEmailAndField((int) $pdm->pid, $pdm->ptable, $pdm->email, $pdm->field);
            $zip->addFromString(\sprintf('%s/%s/%s', $pdm->ptable, $pdm->pid, $objFile->name), $objFile->getContent());
        }
    }
}
