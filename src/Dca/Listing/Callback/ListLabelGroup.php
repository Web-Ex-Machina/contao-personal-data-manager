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

namespace WEM\PersonalDataManagerBundle\Dca\Listing\Callback;

use Contao\DataContainer;
use Contao\Model;

class ListLabelGroup
{
    protected $personalDataManager;

    public function __construct(
        \WEM\PersonalDataManagerBundle\Service\PersonalDataManager $personalDataManager
    ) {
        $this->personalDataManager = $personalDataManager;
    }

    public function __invoke(string $group, ?string $mode, string $field, array $data, DataContainer $dc): string
    {
        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();

        return $this->personalDataManager->getUnecryptedValueByPidAndPTableAndEmailAndField(
            $data[$model->getPersonalDataPidField()],
            $model->getPersonalDataPtable(),
            $data[$model->getPersonalDataEmailField()],
            $field
        );
    }
}
