<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Dca\Field\Callback;

use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\Model;
use Contao\Module;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;

/**
 * "load_callback" of a field managed by the Personal Data Manager, in the back end
 * (DCA) and in the front end ("ModulePersonalData").
 */
class Load
{
    public function __construct(
        private readonly PersonalDataManager $personalDataManager,
        private string $frontendField = '',
        private string $table = '',
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(mixed $value, mixed ...$args): mixed
    {
        if (($args[0] ?? null) instanceof DataContainer) {
            return $this->invokeBackend($value, $args[0]);
        }

        return $this->invokeFrontend($value, ...$args);
    }

    /**
     * @throws \Exception
     */
    public function invokeBackend(mixed $value, DataContainer $dc): mixed
    {
        if (! $dc->id || null === $record = $dc->getCurrentRecord()) {
            return $value;
        }

        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();
        $model->setRow($record);

        return $this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
            $model->getPersonalDataPidFieldValue(),
            $model->getPersonalDataPtable(),
            $model->getPersonalDataEmailFieldValue(),
            $dc->inputName
        ) ?? $value;
    }

    /**
     * @throws \Exception
     */
    public function invokeFrontend(mixed $value, FrontendUser $user, Module $module): mixed
    {
        if ($this->frontendField === '') {
            throw new \Exception('No frontend field configured');
        }

        if ($this->table === '') {
            throw new \Exception('No table configured');
        }

        $modelClassName = Model::getClassFromTable($this->table);
        $model = new $modelClassName();

        return $this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
            (int) $user->{$model->getPersonalDataPidField()},
            $model->getPersonalDataPtable(),
            (string) $user->{$model->getPersonalDataEmailField()},
            $this->frontendField
        ) ?? $value;
    }

    public function getFrontendField(): string
    {
        return $this->frontendField;
    }

    public function setFrontendField(string $frontendField): self
    {
        $this->frontendField = $frontendField;

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }
}
