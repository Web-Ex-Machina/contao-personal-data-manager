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
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData as PersonalDataModel;
use WEM\UtilsBundle\Classes\Encryption;
use function is_array;

class PersonalDataManagerCsvFormatter
{
    private TranslatorInterface $translator;

    private Encryption $encryption;

    public function __construct(
        TranslatorInterface $translator,
        Encryption          $encryption
    ) {
        $this->encryption = $encryption;
        $this->translator = $translator;
    }

    public function formatPersonalDataForCsv(?Collection $personalData): string
    {
        $csv = $this->formatHeader();
        $csv = array_merge($csv, $this->formatAll($personalData, $csv));

        return implode("\n", $csv);
    }

    protected function formatHeader(): array
    {
        $row = [
            $this->translator->trans('WEM.PEDAMA.CSV.columnEntity', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.CSV.columnMail', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.CSV.columnField', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.CSV.columnValue', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.CSV.columnAnonymized', [], 'contao_default'),
        ];

        if (isset($GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport']) && is_array($GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'])) {
            foreach ($GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'] as $callback) {
                $row = System::importStatic($callback[0])->{$callback[1]}($row);
            }
        }

        return [implode(';', $row)];
    }

    protected function formatAll(?Collection $personalData, array $header): array
    {
        $csv = [];
        if ($personalData instanceof Collection) {
            while ($personalData->next()) {
                $row = $this->formatSingle($personalData->current(), $header);
                $csv[] = implode(';', $row);
            }
        }

        return $csv;
    }

    protected function formatSingle(PersonalDataModel $personalData, array $header): array
    {

        $row = [
            $personalData->ptable,
            $personalData->email,
            $GLOBALS['TL_DCA'][$personalData->ptable]['fields'][$personalData->field]['label'] ?? $personalData->field,
            $personalData->anonymized ? $personalData->value : '"' . $this->encryption->decrypt($personalData->value) . '"',
            $personalData->anonymized ? $this->translator->trans('WEM.PEDAMA.CSV.columnAnonymizedValueYes', [], 'contao_default') : $this->translator->trans('WEM.PEDAMA.CSV.columnAnonymizedValueNo', [], 'contao_default'),
        ];
        if (isset($GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport']) && is_array($GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'])) {
            foreach ($GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'] as $callback) {
                $row = System::importStatic($callback[0])->{$callback[1]}($personalData, $header, $row);
            }
        }

        return $row;
    }
}
