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

use Contao\FrontendTemplate;
use Contao\Model;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData;
use WEM\UtilsBundle\Classes\Encryption;
use function array_key_exists;
use function is_array;

class PersonalDataManagerUiFormatter
{
    /** @var TranslatorInterface */
    private TranslatorInterface $translator;

    /** @var string */
    private string $url = '#';
    private Encryption $encryption;

    public function __construct(
        TranslatorInterface $translator, Encryption $encryption
    ) {
        $this->encryption = $encryption;
        $this->translator = $translator;
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

    public function renderListButtons(string $email, int $nbRows): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_list_buttons');
        $tpl->email = $email;
        $tpl->anonymize = 0 === $nbRows ? '' : $this->renderListButtonAnonymize($email);
        $tpl->export = 0 === $nbRows ? '' : $this->renderListButtonExport($email);

        return $tpl->parse();
    }

    public function renderListButtonAnonymize(string $email): string
    {
        return sprintf('<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-list__button_anonymize">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymizeTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymizeConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonAnonymize', [], 'contao_default')
        );
    }

    public function renderListButtonExport(string $email): string
    {
        return sprintf('<a href="%s" title="%s" class="pdm-button pdm-button_export pdm-list__button_export">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.LIST.buttonExportTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonExport', [], 'contao_default')
        );
    }

    public function renderSingleItem(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->email = $email;
        $tpl->header = $this->renderSingleItemHeader($pid, $ptable, $personalDatas, $originalModel);
        $tpl->body = $this->renderSingleItemBody($pid, $ptable, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function renderSingleItemHeader(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_header');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->title = $this->renderSingleItemTitle($pid, $ptable, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemButtons($pid, $ptable, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function renderSingleItemTitle(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_title');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->data = $ptable.'('.$pid.')';

        return $tpl->parse();
    }

    public function renderSingleItemButtons(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_buttons');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->anonymize = $this->renderSingleItemButtonAnonymize($pid, $ptable, $personalDatas, $originalModel);
        $tpl->export = $this->renderSingleItemButtonExport($pid, $ptable, $personalDatas, $originalModel);
        $tpl->show = $this->renderSingleItemButtonShow($pid, $ptable, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function renderSingleItemButtonAnonymize(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-item__button_anonymize_all">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAllTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAllConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeAll', [], 'contao_default')
        );
    }

    public function renderSingleItemButtonExport(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="%s" title="%s" class="pdm-button pdm-button_export pdm-item__button_export">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonExportTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonExport', [], 'contao_default')
        );
    }

    public function renderSingleItemButtonShow(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="%s" title="%s" class="pdm-button pdm-button_show pdm-item__button_show">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonShowTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonShow', [], 'contao_default')
        );
    }

    public function renderSingleItemBody(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_body');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->originalModel = $this->renderSingleItemBodyOriginalModel($pid, $ptable, $personalDatas, $originalModel);
        $tpl->personalData = $this->renderSingleItemBodyPersonalData($pid, $ptable, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function renderSingleItemBodyOriginalModel(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
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

        return $tpl->parse();
    }

    public function renderSingleItemBodyOriginalModelSingle(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_original_model_single');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $field;
        $tpl->fieldLabel = $this->renderSingleItemBodyOriginalModelSingleFieldLabel($pid, $ptable, $field, $value, $personalDatas, $originalModel);
        $tpl->value = $this->renderSingleItemBodyOriginalModelSingleFieldValue($pid, $ptable, $field, $value, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function renderSingleItemBodyOriginalModelSingleFieldLabel(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        return $this->getFieldLabelTranslated($ptable, $field);
    }

    public function renderSingleItemBodyOriginalModelSingleFieldValue(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        return $value ?? '';
    }

    public function renderSingleItemBodyPersonalData(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $items = [];

        foreach ($personalDatas as $personalData) {
            $items[] = $this->renderSingleItemBodyPersonalDataSingle($pid, $ptable, $personalData, $personalDatas, $originalModel);
        }

        $tpl->items = $items;

        return $tpl->parse();
    }

    public function renderSingleItemBodyPersonalDataSingle(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $personalData->field;
        $tpl->fieldLabel = $this->renderSingleItemBodyPersonalDataSingleFieldLabel($pid, $ptable, $personalData, $personalDatas, $originalModel);
        $tpl->value = $this->renderSingleItemBodyPersonalDataSingleFieldValue($pid, $ptable, $personalData, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $personalData, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function renderSingleItemBodyPersonalDataSingleFieldLabel(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        return $this->getFieldLabelTranslated($ptable, $personalData->field);
    }

    public function renderSingleItemBodyPersonalDataSingleFieldValue(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        return $personalData->anonymized ? ($personalData->value ?? '') : $this->unencrypt($personalData->value);
    }

    public function renderSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single_buttons');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $personalData->field;
        $tpl->anonymize = $personalData->anonymized ? '' : $this->renderSingleItemBodyPersonalDataSingleButtonAnonymize($pid, $ptable, $personalData, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    public function renderSingleItemBodyPersonalDataSingleButtonAnonymize(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="%s" title="%s" data-confirm="%s" class="pdm-button pdm-button_anonymize pdm-item__personal_data_single__button_anonymize">%s</a>',
            $this->url,
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymizeConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonAnonymize', [], 'contao_default')
        );
    }

    public function getFieldLabelTranslated(string $ptable, string $field): string
    {
        if (array_key_exists($field, $GLOBALS['TL_LANG'][$ptable])) {
            if (is_array($GLOBALS['TL_LANG'][$ptable][$field])) {
                return $this->translator->trans(sprintf('%s.%s', $ptable, $field).'.0', [], 'contao_default') ?? $field;
            }

            return $this->translator->trans(sprintf('%s.%s', $ptable, $field), [], 'contao_default') ?? $field;
        }

        return sprintf('%s.%s', $ptable, $field).'.0';
    }

    protected function unencrypt($value)
    {

        return $this->encryption->decrypt($value);
    }
}
