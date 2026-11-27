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
 * "list.label.label_callback" of a table managed by the Personal Data Manager.
 */
class ListLabelLabelForList
{
    public function __construct(
        private readonly PersonalDataManager $personalDataManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(array $row, string $labelSingle, DataContainer $dc, array $labels): array
    {
        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();
        $model->setRow($row);

        // $labelFields contains the field names, while $labels only contains the values.
        // They are in the same order, so $index can be used.
        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'] ?? [] as $index => $field) {
            if ($model->isFieldInPersonalDataFieldsNames($field)) {
                $labels[$index] = $this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
                    $model->getPersonalDataPidFieldValue(),
                    $model->getPersonalDataPtable(),
                    $model->getPersonalDataEmailFieldValue(),
                    $field
                );
            }
        }

        return $labels;
    }
}
