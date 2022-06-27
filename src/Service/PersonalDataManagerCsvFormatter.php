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

use Contao\Model\Collection;
use Contao\System;
use WEM\PersonalDataManagerBundle\Model\PersonalData as PersonalDataModel;

class PersonalDataManagerCsvFormatter
{
    public function formatPersonalDataForCsv(?Collection $personalData): string
    {
        $csv = $this->formatHeader();
        $csv = $csv + $this->formatAll($personalData, $csv);

        return implode("\n", $csv);
    }

    protected function formatHeader(): array
    {
        $row = [
            'Entity', 'Mail', 'Field', 'Value',
        ];

        if (isset($GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport']) && \is_array($GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'])) {
            foreach ($GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'] as $callback) {
                $row = System::importStatic($callback[0])->{$callback[1]}($row);
            }
        }

        return [implode(';', $row)];
    }

    protected function formatAll(?Collection $personalData, array $header): array
    {
        $csv = [];
        if ($personalData) {
            while ($personalData->next()) {
                $row = $this->formatSingle($personalData->current(), $header);
                $csv[] = implode(';', $row);
            }
        }

        return $csv;
    }

    protected function formatSingle(PersonalDataModel $personalData, array $header): array
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');
        $row = [
            $personalData->ptable,
            $personalData->email,
            $GLOBALS['TL_DCA'][$personalData->ptable]['fields'][$personalData->field]['label'] ?? $personalData->field,
            $encryptionService->decrypt($personalData->value),
        ];

        if (isset($GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport']) && \is_array($GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'])) {
            foreach ($GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'] as $callback) {
                $row = System::importStatic($callback[0])->{$callback[1]}($personalData, $header, $row);
            }
        }

        return $row;
    }
}
