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

use Contao\DcaLoader;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\Model\Collection;
use Contao\RequestToken;
use Contao\System;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData;

class PersonalDataManagerUi
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var PersonalDataManager */
    private $manager;
    /** @var string */
    private $url;

    public function __construct(
        TranslatorInterface $translator,
        PersonalDataManager $manager
    ) {
        $this->translator = $translator;
        $this->manager = $manager;
        $this->url = '#'; //;
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
                $renderedItems[] = $this->renderSingleItem((int) $pid, $ptable, $email, $singleItemData['personalDatas'], $singleItemData['originalModel']);
            }
        }
        $tpl->items = $renderedItems;
        $tpl->request = Environment::get('request');
        $tpl->token = RequestToken::get();
        $tpl->buttons = $this->renderListButtons($email, \count($data));

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

    protected function sortData(?Collection $personalDatas): array
    {
        $sorted = [];
        if (!$personalDatas) {
            return [];
        }

        while ($personalDatas->next()) {
            if (!\array_key_exists($personalDatas->ptable, $sorted)) {
                $sorted[$personalDatas->ptable] = [];
            }
            if (!\array_key_exists($personalDatas->pid, $sorted[$personalDatas->ptable])) {
                $sorted[$personalDatas->ptable][$personalDatas->pid] = [
                    'originalModel' => $this->getOriginalObject((int) $personalDatas->pid, $personalDatas->ptable),
                    'personalDatas' => [],
                ];
            }
            $sorted[$personalDatas->ptable][$personalDatas->pid]['personalDatas'][] = $personalDatas->current();
        }
        ksort($sorted);
        foreach ($sorted as $ptable => $pids) {
            ksort($pids);
            $sorted[$ptable] = $pids;
        }

        return $sorted;
    }

    protected function removeAlreadyAnonymisedElements(array $data): array
    {
        foreach ($data as $ptable => $arrPids) {
            foreach ($arrPids as $pid => $singleItemData) {
                $nbPersonalData = \count($singleItemData['personalDatas']);
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
            if (0 === \count($data[$ptable])) {
                unset($data[$ptable]);
            }
        }

        return $data;
    }

    protected function renderListButtons(string $email, int $nbRows): string
    {
        $str = '';
        $tpl = new FrontendTemplate('wem_personal_data_manager_list_buttons');
        $tpl->email = $email;
        $tpl->anonymize = 0 === $nbRows ? '' : $this->renderListButtonAnonymize($email);
        $tpl->export = 0 === $nbRows ? '' : $this->renderListButtonExport($email);
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderListButtons']) && \is_array($GLOBALS['WEM_HOOKS']['renderListButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderListButtons'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($email, $nbRows, $str);
            }
        }

        return $str;
    }

    protected function renderListButtonAnonymize(string $email): string
    {
        return sprintf('<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-list__button_anonymize">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymizeTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymizeConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymize', [], 'contao_default')
        );
    }

    protected function renderListButtonExport(string $email): string
    {
        return sprintf('<a href="%s" title="%s" class="pdm-button pdm-button_export pdm-list__button_export">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.LIST.buttonExportTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonExport', [], 'contao_default')
        );
    }

    protected function renderSingleItem(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->email = $email;
        $tpl->header = $this->renderSingleItemHeader($pid, $ptable, $personalDatas, $originalModel);
        $tpl->body = $this->renderSingleItemBody($pid, $ptable, $personalDatas, $originalModel);
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItem']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItem'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItem'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $email, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemHeader(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_header');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->title = $this->renderSingleItemTitle($pid, $ptable, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemButtons($pid, $ptable, $personalDatas, $originalModel);
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemHeader']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemHeader'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemHeader'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemTitle(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_title');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->data = $ptable.'('.$pid.')';
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemTitle']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemTitle'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemTitle'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemButtons(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_buttons');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->anonymize = $this->renderSingleItemButtonAnonymize($pid, $ptable, $personalDatas, $originalModel);
        $tpl->export = $this->renderSingleItemButtonExport($pid, $ptable, $personalDatas, $originalModel);
        $tpl->show = $this->renderSingleItemButtonShow($pid, $ptable, $personalDatas, $originalModel);
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemButtons']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemButtons'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemButtonAnonymize(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-item__button_anonymize_all">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAllTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAllConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAll', [], 'contao_default')
        );
    }

    protected function renderSingleItemButtonExport(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="%s" title="%s" class="pdm-button pdm-button_export pdm-item__button_export">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonExportTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonExport', [], 'contao_default')
        );
    }

    protected function renderSingleItemButtonShow(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="%s" title="%s" class="pdm-button pdm-button_show pdm-item__button_show">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonShowTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonShow', [], 'contao_default')
        );
    }

    protected function renderSingleItemBody(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_body');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->originalModel = $this->renderSingleItemBodyOriginalModel($pid, $ptable, $personalDatas, $originalModel);
        $tpl->personalData = $this->renderSingleItemBodyPersonalData($pid, $ptable, $personalDatas, $originalModel);
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBody']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBody'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBody'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyOriginalModel(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_original_model');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->originalModel = $originalModel;

        $items = [];
        $row = $originalModel->row();
        foreach ($row as $field => $value) {
            $items[] = $this->renderSingleItemBodyOriginalModelSingle($pid, $ptable, $field, $value, $personalDatas, $originalModel);
        }
        $tpl->items = $items;
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModel'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyOriginalModelSingle(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_original_model_single');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $field;
        $tpl->fieldLabel = $this->renderSingleItemBodyOriginalModelSingleFieldLabel($pid, $ptable, $field, $value, $personalDatas, $originalModel);
        $tpl->value = $this->renderSingleItemBodyOriginalModelSingleFieldValue($pid, $ptable, $field, $value, $personalDatas, $originalModel);
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingle'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $field, $value, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyOriginalModelSingleFieldLabel(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $str = $this->getFieldLabelTranslated($ptable, $field);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldLabel'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $field, $value, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyOriginalModelSingleFieldValue(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $str = $value ?? '';

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyOriginalModelSingleFieldValue'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $field, $value, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalData(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $items = [];

        foreach ($personalDatas as $personalData) {
            $items[] = $this->renderSingleItemBodyPersonalDataSingle($pid, $ptable, $personalData, $personalDatas, $originalModel);
        }
        $tpl->items = $items;
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalData'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingle(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $personalData->field;
        $tpl->fieldLabel = $this->renderSingleItemBodyPersonalDataSingleFieldLabel($pid, $ptable, $personalData, $personalDatas, $originalModel);
        $tpl->value = $this->renderSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $personalData, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $personalData, $personalDatas, $originalModel);
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingle'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalData, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingleFieldLabel(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $str = $this->getFieldLabelTranslated($ptable, $personalData->field);
        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldLabel'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalData, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingleFieldValue(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $str = $personalData->anonymized ? ($personalData->value ?? '') : $this->unencrypt($personalData->value);

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleFieldValue'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalData, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single_buttons');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $personalData->field;
        $tpl->anonymize = $personalData->anonymized ? '' : $this->renderSingleItemBodyPersonalDataSingleButtonAnonymize($pid, $ptable, $personalData, $personalDatas, $originalModel);
        $str = $tpl->parse();

        if (isset($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons']) && \is_array($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons'])) {
            foreach ($GLOBALS['WEM_HOOKS']['renderSingleItemBodyPersonalDataSingleButtons'] as $callback) {
                $str = System::importStatic($callback[0])->{$callback[1]}($pid, $ptable, $personalData, $personalDatas, $originalModel, $str);
            }
        }

        return $str;
    }

    protected function renderSingleItemBodyPersonalDataSingleButtonAnonymize(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-item__personal_data_single__button_anonymize">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymize', [], 'contao_default')
        );
    }

    protected function getOriginalObject(int $pid, string $ptable)
    {
        $modelClassName = Model::getClassFromTable($ptable);

        return $modelClassName::findOneById($pid);
    }

    protected function getFieldLabelTranslated(string $ptable, string $field): string
    {
        if (\array_key_exists($field, $GLOBALS['TL_LANG'][$ptable])) {
            if (\is_array($GLOBALS['TL_LANG'][$ptable][$field])) {
                return $this->translator->trans(sprintf('%s.%s', $ptable, $field).'.0', [], 'contao_default') ?? $field;
            }

            return $this->translator->trans(sprintf('%s.%s', $ptable, $field), [], 'contao_default') ?? $field;
        }

        return sprintf('%s.%s', $ptable, $field).'.0';
    }

    protected function unencrypt($value)
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');

        return $encryptionService->decrypt($value);
    }
}
