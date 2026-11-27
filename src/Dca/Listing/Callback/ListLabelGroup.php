<?php

declare(strict_types=1);

namespace WEM\PersonalDataManagerBundle\Dca\Listing\Callback;

use Contao\DataContainer;
use Contao\Model;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;

/**
 * "list.label.group_callback" of a table managed by the Personal Data Manager.
 */
class ListLabelGroup
{
    public function __construct(
        private readonly PersonalDataManager $personalDataManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(string $group, string|null $mode, string $field, array $data, DataContainer $dc): string
    {
        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();
        $model->setRow($data);

        return (string) ($this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
            $model->getPersonalDataPidFieldValue(),
            $model->getPersonalDataPtable(),
            $model->getPersonalDataEmailFieldValue(),
            $field
        ) ?? $group);
    }
}
