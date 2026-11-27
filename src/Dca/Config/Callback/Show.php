<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Dca\Config\Callback;

use Contao\DataContainer;
use Contao\Model;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;

/**
 * "config.onshow_callback" of a table managed by the Personal Data Manager.
 */
class Show
{
    public function __construct(
        private readonly PersonalDataManager $personalDataManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(array $modalData, array $data, DataContainer $dc): array
    {
        if (! $dc->id) {
            return $modalData;
        }

        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();

        foreach ($modalData as $table => $rows) {
            foreach ($rows as $index => $row) {
                foreach (array_keys($row) as $label) {
                    if (! preg_match('/(.*)<small>(.*)<\/small>/', (string) $label, $matches)) {
                        continue;
                    }

                    $fieldName = $matches[2];

                    if ($model->isFieldInPersonalDataFieldsNames($fieldName)) {
                        $modalData[$table][$index][$label] = $this->personalDataManager->getUnecryptedValueByPidAndPtableAndEmailAndField(
                            (int) $data[$model->getPersonalDataPidField()],
                            $model->getPersonalDataPtable(),
                            (string) $data[$model->getPersonalDataEmailField()],
                            $fieldName
                        );
                    }
                }
            }
        }

        return $modalData;
    }
}
