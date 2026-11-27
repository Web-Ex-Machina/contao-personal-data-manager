<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

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
