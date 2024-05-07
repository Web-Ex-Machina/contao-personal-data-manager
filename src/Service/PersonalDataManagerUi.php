<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * Copyright (c) 2015-2024 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-smartgear
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/personal-data-manager/
 */

namespace WEM\PersonalDataManagerBundle\Service;

use Contao\DcaLoader;
use Contao\Environment;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\Model\Collection;
use Contao\RequestToken;
use Contao\System;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Classes\FileUtil;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use function array_key_exists;
use function count;
use function is_array;

class PersonalDataManagerUi
{
    private TranslatorInterface $translator;

    private PersonalDataManager $manager;

    private string $url = '#';

    public function __construct(
        TranslatorInterface $translator,
        PersonalDataManager $manager
    ) {
        $this->translator = $translator;
        $this->manager = $manager;
    }

    public function listForEmail(string $email): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_list');
        $renderedItems = [];
        $data = $this->sortData($this->manager->findByEmail($email));
        $data = $this->removeAlreadyAnonymisedElements($data);

        foreach ($data as $ptable => $arrPids) {
            $dcaLoader = new DcaLoader($ptable);
            $dcaLoader->load();
            System::loadLanguageFile($ptable);
            foreach ($arrPids as $pid => $singleItemData) {
                // if originalModel has been deleted, do not manage the remaining PDM data about it
                if (null === $singleItemData['originalModel']) {
                    continue;
                }

                $renderedItem = $this->renderSingleItem((int) $pid, $ptable, $email, $singleItemData['personalDatas'], $singleItemData['originalModel']);
                if (!empty($renderedItem)) {
                    $renderedItems[] = $renderedItem;
                }
            }
        }

        $tpl->items = $renderedItems;
        $tpl->request = Environment::get('request');
        $tpl->token = RequestToken::get();
        $tpl->buttons = $this->renderListButtons($email, count($data));

        return $tpl->parse();
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function formatListButtons(string $email, int $nbRows): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_list_buttons');
        $tpl->email = $email;

        $tpl->buttons = $this->buildListButtons($email, $nbRows);

        return $tpl->parse();
    }

    public function buildListButtons(string $email, int $nbRows): array
    {
        $buttons = [];
        $buttons['anonymize'] = 0 === $nbRows ? '' : $this->renderListButtonAnonymize($email);
        $buttons['export'] = 0 === $nbRows ? '' : $this->renderListButtonExport($email);

        if (isset($GLOBALS['WEM_HOOKS']['buildListButtons']) && is_array($GLOBALS['WEM_HOOKS']['buildListButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['buildListButtons'] as $callback) {
                $buttons = System::importStatic($callback[0])->{$callback[1]}($email, $nbRows, $buttons);
            }
        }

        return $buttons;
    }

    public function formatListButtonAnonymize(string $email): string
    {
        return sprintf(
            '<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-list__button_anonymize">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymizeTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymizeConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymize', [], 'contao_default')
        );
    }

    public function formatListButtonExport(string $email): string
    {
        return sprintf(
            '<a href="%s" title="%s" class="pdm-button pdm-button_export pdm-list__button_export">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.LIST.buttonExportTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonExport', [], 'contao_default')
        );
    }

    public function formatSingleItem(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->email = $email;
        $tpl->header = $this->renderSingleItemHeader($pid, $ptable, $email, $personalDatas, $originalModel);
        $tpl->body = $this->renderSingleItemBody($pid, $ptable, $email, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function formatSingleItemHeader(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_header');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->title = $this->renderSingleItemTitle($pid, $ptable, $email, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemButtons($pid, $ptable, $email, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function formatSingleItemTitle(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_title');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->data = $ptable.'('.$pid.')';

        return $tpl->parse();
    }

    public function formatSingleItemButtons(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_buttons');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->buttons = $this->buildSingleItemButtons($pid, $ptable, $email, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function buildSingleItemButtons(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): array
    {
        $buttons = [];
        $buttons['show'] = $this->renderSingleItemButtonShow($pid, $ptable, $email, $personalDatas, $originalModel);
        $buttons['anonymize'] = $this->renderSingleItemButtonAnonymize($pid, $ptable, $email, $personalDatas, $originalModel);
        $buttons['export'] = $this->renderSingleItemButtonExport($pid, $ptable, $email, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['buildSingleItemButtons']) && is_array($GLOBALS['WEM_HOOKS']['buildSingleItemButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['buildSingleItemButtons'] as $callback) {
                $buttons = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $buttons);
            }
        }

        return $buttons;
    }

    public function formatSingleItemButtonAnonymize(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        return sprintf(
            '<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-item__button_anonymize_all">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAllTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAllConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAll', [], 'contao_default')
        );
    }

    public function formatSingleItemButtonExport(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        return sprintf(
            '<a href="%s" title="%s" class="pdm-button pdm-button_export pdm-item__button_export">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonExportTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonExport', [], 'contao_default')
        );
    }

    public function formatSingleItemButtonShow(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        return sprintf(
            '<a href="%s" title="%s" class="pdm-button pdm-button_show pdm-item__button_show">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonShowTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonShow', [], 'contao_default')
        );
    }

    public function formatSingleItemBody(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_body');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->originalModel = $this->renderSingleItemBodyOriginalModel($pid, $ptable, $email, $personalDatas, $originalModel);
        $tpl->personalData = $this->renderSingleItemBodyPersonalData($pid, $ptable, $email, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function formatSingleItemBodyOriginalModel(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_original_model');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->originalModel = $originalModel;

        $items = [];
        $row = $originalModel->row();
        foreach ($row as $field => $value) {
            $items[] = $this->renderSingleItemBodyOriginalModelSingle($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel);
        }

        $tpl->items = $items;

        return $tpl->parse();
    }

    public function formatSingleItemBodyOriginalModelSingle(int $pid, string $ptable, string $email, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_original_model_single');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $field;
        $tpl->email = $email;
        $tpl->fieldLabel = $this->renderSingleItemBodyOriginalModelSingleFieldLabel($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel);
        $tpl->value = $this->renderSingleItemBodyOriginalModelSingleFieldValue($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function formatSingleItemBodyOriginalModelSingleFieldLabel(int $pid, string $ptable, string $email, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        return $this->getFieldLabelTranslated($ptable, $field);
    }

    public function formatSingleItemBodyOriginalModelSingleFieldValue(int $pid, string $ptable, string $email, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        return (string) $value ?? '';
    }

    public function formatSingleItemBodyPersonalData(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $items = [];

        foreach ($personalDatas as $personalData) {
            $items[] = $this->renderSingleItemBodyPersonalDataSingle($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);
        }

        $tpl->items = $items;

        return $tpl->parse();
    }

    public function formatSingleItemBodyPersonalDataSingle(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $personalData->field;
        $tpl->email = $email;
        $tpl->fieldLabel = $this->renderSingleItemBodyPersonalDataSingleFieldLabel($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);
        $tpl->value = $this->renderSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function formatSingleItemBodyPersonalDataSingleFieldLabel(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        return $this->getFieldLabelTranslated($ptable, $personalData->field);
    }

    public function formatSingleItemBodyPersonalDataSingleFieldValue(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        // here we could check if the data is linked to a file and display its name
        return $personalData->anonymized ? ($personalData->value ?? '') : $this->unencrypt($personalData->value);
    }

    public function formatSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single_buttons');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $personalData->field;
        $tpl->email = $email;
        $tpl->buttons = $this->buildSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function buildSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): array
    {
        $buttons = [];
        $buttons['anonymize'] = $personalData->anonymized ? '' : $this->renderSingleItemBodyPersonalDataSingleButtonAnonymize($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);

        // here we could check if the data is linked to a file and display "show" & "download" buttons
        $objFile = null;
        if ($this->manager->isPersonalDataLinkedToFile($personalData)) {
            try {
                $objFile = $this->manager->getFileByPidAndPtableAndEmailAndField($personalData->pid, $personalData->ptable, $personalData->email, $personalData->field);
                if (FileUtil::isDisplayableInBrowser($objFile)) {
                    $buttons['show'] = $personalData->anonymized ? '' : $this->renderSingleItemBodyPersonalDataSingleButtonShowFile($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $objFile);
                }

                $buttons['download'] = $personalData->anonymized ? '' : $this->renderSingleItemBodyPersonalDataSingleButtonDownloadFile($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $objFile);
            } catch (Exception $e) {
                // do nothing
            }
        }

        if (isset($GLOBALS['WEM_HOOKS']['buildSingleItemBodyPersonalDataSingleButtons']) && is_array($GLOBALS['WEM_HOOKS']['buildSingleItemBodyPersonalDataSingleButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['buildSingleItemBodyPersonalDataSingleButtons'] as $callback) {
                $buttons = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $objFile, $buttons);
            }
        }

        return $buttons;
    }

    public function formatSingleItemBodyPersonalDataSingleButtonAnonymize(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        return sprintf(
            '<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-item__personal_data_single__button_anonymize">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymize', [], 'contao_default')
        );
    }

    public function formatSingleItemBodyPersonalDataSingleButtonShowFile(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, File $file): string
    {
        return sprintf(
            '<a href="%s" title="%s" class="pdm-button pdm-button_show_file pdm-item__personal_data_single__button_show_file">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonShowFileTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonShowFile', [], 'contao_default')
        );
    }

    public function formatSingleItemBodyPersonalDataSingleButtonDownloadFile(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, File $file): string
    {
        return sprintf(
            '<a href="%s" title="%s" class="pdm-button pdm-button_download_file pdm-item__personal_data_single__button_download_file">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonDownloadFileTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonDownloadFile', [], 'contao_default')
        );
    }

    protected function sortData(?Collection $personalDatas): array
    {
        $sorted = [];
        if ($personalDatas instanceof Collection) {
            while ($personalDatas->next()) {
                if (!array_key_exists($personalDatas->ptable, $sorted)) {
                    $sorted[$personalDatas->ptable] = [];
                }

                if (!array_key_exists($personalDatas->pid, $sorted[$personalDatas->ptable])) {
                    $sorted[$personalDatas->ptable][$personalDatas->pid] = [
                        'originalModel' => $this->getOriginalObject((int) $personalDatas->pid, $personalDatas->ptable),
                        'personalDatas' => [],
                    ];
                }

                $sorted[$personalDatas->ptable][$personalDatas->pid]['personalDatas'][] = $personalDatas->current();
            }
        }

        ksort($sorted);
        foreach ($sorted as $ptable => $pids) {
            ksort($pids);
            $sorted[$ptable] = $pids;
        }

        if (isset($GLOBALS['WEM_HOOKS']['sortData']) && is_array($GLOBALS['WEM_HOOKS']['sortData'])) {
            foreach ($GLOBALS['WEM_HOOKS']['sortData'] as $callback) {
                $sorted = System::importStatic($callback[0])->{$callback[1]}($sorted, $personalDatas);
            }
        }

        return $sorted;
    }

    protected function removeAlreadyAnonymisedElements(array $data): array
    {
        foreach ($data as $ptable => $arrPids) {
            foreach ($arrPids as $pid => $singleItemData) {
                $nbPersonalData = count($singleItemData['personalDatas']);
                $nbPersonalDataAnonymised = 0;
                foreach ($singleItemData['personalDatas'] as $personalData) {
                    if ($personalData->anonymized) {
                        ++$nbPersonalDataAnonymised;
                    }
                }

                if ($nbPersonalData === $nbPersonalDataAnonymised) {
                    unset($data[$ptable][$pid]);
                }
            }

            if (0 === count($data[$ptable])) {
                unset($data[$ptable]);
            }
        }

        return $data;
    }

    protected function renderListButtons(string $email, int $nbRows): string
    {
        $str = $this->formatListButtons($email, $nbRows);

        if (isset($GLOBALS['WEM_HOOKS']['renderListButtons']) && is_array($GLOBALS['WEM_HOOKS']['renderListButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderListButtons'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($email, $nbRows, $str);
            }
        }

        return $str;
    }

    protected function renderListButtonAnonymize(string $email): string
    {
        return $this->formatListButtonAnonymize($email);
    }

    protected function renderListButtonExport(string $email): string
    {
        return $this->formatListButtonExport($email);
    }

    protected function renderSingleItem(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItem($pid, $ptable, $email, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItem']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItem'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItem'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemHeader(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemHeader($pid, $ptable, $email, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemHeader']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemHeader'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemHeader'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemTitle(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemTitle($pid, $ptable, $email, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemTitle']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemTitle'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemTitle'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemButtons(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemButtons($pid, $ptable, $email, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemButtons']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemButtons'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemButtonAnonymize(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        return $this->formatSingleItemButtonAnonymize($pid, $ptable, $email, $personalDatas, $originalModel);
    }

    protected function renderSingleItemButtonExport(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        return $this->formatSingleItemButtonExport($pid, $ptable, $email, $personalDatas, $originalModel);
    }

    protected function renderSingleItemButtonShow(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        return $this->formatSingleItemButtonShow($pid, $ptable, $email, $personalDatas, $originalModel);
    }

    protected function renderSingleItemBody(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBody($pid, $ptable, $email, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBody']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBody'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBody'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyOriginalModel(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyOriginalModel($pid, $ptable, $email, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyOriginalModelSingle(int $pid, string $ptable, string $email, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyOriginalModelSingle($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyOriginalModelSingleFieldLabel(int $pid, string $ptable, string $email, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyOriginalModelSingleFieldLabel($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyOriginalModelSingleFieldValue(int $pid, string $ptable, string $email, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyOriginalModelSingleFieldValue($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $field, $value, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalData(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyPersonalData($pid, $ptable, $email, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingle(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyPersonalDataSingle($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingleFieldLabel(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyPersonalDataSingleFieldLabel($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);
        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingleFieldValue(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $str = $this->formatSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons']) && is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingleButtonAnonymize(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        return $this->formatSingleItemBodyPersonalDataSingleButtonAnonymize($pid, $ptable, $email, $personalData, $personalDatas, $originalModel);
    }

    protected function renderSingleItemBodyPersonalDataSingleButtonShowFile(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, File $file): string
    {
        return $this->formatSingleItemBodyPersonalDataSingleButtonShowFile($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $file);
    }

    protected function renderSingleItemBodyPersonalDataSingleButtonDownloadFile(int $pid, string $ptable, string $email, PersonalData $personalData, array $personalDatas, Model $originalModel, File $file): string
    {
        return $this->formatSingleItemBodyPersonalDataSingleButtonDownloadFile($pid, $ptable, $email, $personalData, $personalDatas, $originalModel, $file);
    }

    protected function getOriginalObject(int $pid, string $ptable)
    {
        $modelClassName = Model::getClassFromTable($ptable);

        return $modelClassName::findOneById($pid);
    }

    protected function getFieldLabelTranslated(string $ptable, string $field): string
    {
        System::loadLanguageFile($ptable);
        if (array_key_exists($field, $GLOBALS['TL_LANG'][$ptable])) {
            if (is_array($GLOBALS['TL_LANG'][$ptable][$field])) {
                return $this->translator->trans(sprintf('%s.%s.0', $ptable, $field), [], 'contao_default') ?? $field;
            }

            return $this->translator->trans(sprintf('%s.%s', $ptable, $field), [], 'contao_default') ?? $field;
        }

        return sprintf('%s.%s.0', $ptable, $field);
    }

    protected function unencrypt($value)
    {
        $encryptionService = System::getContainer()->get('plenta.encryption');

        return $encryptionService->decrypt($value);
    }
}
