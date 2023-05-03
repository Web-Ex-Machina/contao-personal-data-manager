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

namespace WEM\PersonalDataManagerBundle\Dca\Config\Callback;

use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\Model;
use Contao\ModulePersonalData;

class Submit
{
    protected $personalDataManager;

    public function __construct(
        \WEM\PersonalDataManagerBundle\Service\PersonalDataManager $personalDataManager
    ) {
        $this->personalDataManager = $personalDataManager;
    }

    public function __invoke()
    {
        if (2 === \func_num_args()) {
            return $this->invokeFrontend(...\func_get_args());
        }

        return $this->invokeBackend(...\func_get_args());
    }

    protected function invokeFrontend(FrontendUser $user, ModulePersonalData $module): void
    {
        // nothing to do here
    }

    protected function invokeBackend(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }
        $modelClassName = Model::getClassFromTable($dc->table);
        $model = $modelClassName::findByPk($dc->id);

        // $model->setRow($dc->activeRecord->row());
        foreach ($dc->activeRecord->row() as $key => $value) {
            if (!$model->isFieldInPersonalDataFieldsNames($key)
            || $value === $model->getPersonalDataFieldsDefaultValueForField($key)
            ) {
                continue;
            }
            $model->{$key} = $value;
            $model->markModified($key);
        }

        $model->save();
    }
}
