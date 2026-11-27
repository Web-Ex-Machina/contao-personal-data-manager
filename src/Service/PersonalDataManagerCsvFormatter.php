<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Service;

use Contao\Model\Collection;
use Contao\System;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData as PersonalDataModel;
use WEM\UtilsBundle\Classes\Encryption;

class PersonalDataManagerCsvFormatter
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Encryption $encryption,
    ) {
    }

    public function formatPersonalDataForCsv(Collection|null $personalData): string
    {
        $csv = $this->formatHeader();
        $csv = [...$csv, ...$this->formatAll($personalData, $csv)];

        return implode("\n", $csv);
    }

    protected function formatHeader(): array
    {
        $row = [
            $this->trans('WEM.PEDAMA.CSV.columnEntity'),
            $this->trans('WEM.PEDAMA.CSV.columnMail'),
            $this->trans('WEM.PEDAMA.CSV.columnField'),
            $this->trans('WEM.PEDAMA.CSV.columnValue'),
            $this->trans('WEM.PEDAMA.CSV.columnAnonymized'),
        ];

        foreach ($GLOBALS['WEM_HOOKS']['formatHeaderForCsvExport'] ?? [] as $callback) {
            $row = System::importStatic($callback[0])->{$callback[1]}($row);
        }

        return [implode(';', $row)];
    }

    protected function formatAll(Collection|null $personalData, array $header): array
    {
        $csv = [];

        foreach ($personalData ?? [] as $singlePersonalData) {
            $csv[] = implode(';', $this->formatSingle($singlePersonalData, $header));
        }

        return $csv;
    }

    protected function formatSingle(PersonalDataModel $personalData, array $header): array
    {
        $label = $GLOBALS['TL_DCA'][$personalData->ptable]['fields'][$personalData->field]['label'] ?? $personalData->field;

        $row = [
            $personalData->ptable,
            $personalData->email,
            \is_array($label) ? ($label[0] ?? $personalData->field) : $label,
            $personalData->anonymized ? $personalData->value : '"' . $this->encryption->decrypt_b64($personalData->value) . '"',
            $this->trans($personalData->anonymized ? 'WEM.PEDAMA.CSV.columnAnonymizedValueYes' : 'WEM.PEDAMA.CSV.columnAnonymizedValueNo'),
        ];

        foreach ($GLOBALS['WEM_HOOKS']['formatSinglePersonalDataForCsvExport'] ?? [] as $callback) {
            $row = System::importStatic($callback[0])->{$callback[1]}($personalData, $header, $row);
        }

        return $row;
    }

    private function trans(string $id): string
    {
        return $this->translator->trans($id, [], 'contao_default');
    }
}
