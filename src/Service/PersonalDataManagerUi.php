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

use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\Model\Collection;
use Contao\RequestToken;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Model\PersonalData;

class PersonalDataManagerUi
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var PersonalDataManager */
    private $manager;

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
            $sorted[$personalDatas->ptable][$personalDatas->pid]['personalDatas'][$personalDatas->field] = PersonalData::DELETED === $personalDatas->value ? $personalDatas->value : $this->unencrypt($personalDatas->value);
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
                foreach ($singleItemData['personalDatas'] as $field => $value) {
                    if (PersonalData::DELETED === $value) {
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
        $tpl = new FrontendTemplate('wem_personal_data_manager_list_buttons');
        $tpl->email = $email;
        $tpl->delete = 0 === $nbRows ? '' : $this->renderListButtonDelete($email);
        $tpl->export = 0 === $nbRows ? '' : $this->renderListButtonExport($email);

        return $tpl->parse();
    }

    protected function renderListButtonDelete(string $email): string
    {
        return sprintf('<a href="#" title="%s" data-confirm="%s" class="pdm-button pdm-button_delete pdm-list__button_delete">%s</a>',
            $this->translator->trans('WEM.PEDAMA.LIST.buttonDeleteTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonDeleteConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.LIST.buttonDelete', [], 'contao_default')
        );
    }

    protected function renderListButtonExport(string $email): string
    {
        return sprintf('<a href="#" title="%s" class="pdm-button pdm_list__button_export">%s</a>',
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

        return $tpl->parse();
    }

    protected function renderSingleItemHeader(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_header');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->title = $this->renderSingleItemTitle($pid, $ptable, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemButtons($pid, $ptable, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    protected function renderSingleItemTitle(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_title');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->data = $ptable.'('.$pid.')';

        return $tpl->parse();
    }

    protected function renderSingleItemButtons(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_buttons');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->delete = $this->renderSingleItemButtonDelete($pid, $ptable, $personalDatas, $originalModel);
        $tpl->export = $this->renderSingleItemButtonExport($pid, $ptable, $personalDatas, $originalModel);
        $tpl->show = $this->renderSingleItemButtonShow($pid, $ptable, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    protected function renderSingleItemButtonDelete(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="#" title="%s" data-confirm="%s" class="pdm-button pdm-button_delete pdm-item__button_delete_all">%s</a>',
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonDeleteAllTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonDeleteAllConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonDeleteAll', [], 'contao_default')
        );
    }

    protected function renderSingleItemButtonExport(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return '[EXPORT]';
    }

    protected function renderSingleItemButtonShow(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        return '[SHOW]';
    }

    protected function renderSingleItemBody(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_body');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->originalModel = $this->renderSingleItemBodyOriginalModel($pid, $ptable, $personalDatas, $originalModel);
        $tpl->personalData = $this->renderSingleItemBodyPersonalData($pid, $ptable, $personalDatas, $originalModel);

        return $tpl->parse();
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

        return $tpl->parse();
    }

    protected function renderSingleItemBodyOriginalModelSingle(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_original_model_single');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $field;
        $tpl->fieldLabel = $this->translator->trans(sprintf('%s.%s.0', $ptable, $field), [], 'contao_default');
        $tpl->value = $value;

        return $tpl->parse();
    }

    protected function renderSingleItemBodyPersonalData(int $pid, string $ptable, array $personalDatas, Model $originalModel): string
    {
        // return '[PERSONAL DATA]';
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $items = [];

        foreach ($personalDatas as $field => $value) {
            $items[] = $this->renderSingleItemBodyPersonalDataSingle($pid, $ptable, $field, $value, $personalDatas, $originalModel);
        }
        $tpl->items = $items;

        return $tpl->parse();
    }

    protected function renderSingleItemBodyPersonalDataSingle(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $field;
        $tpl->fieldLabel = $this->translator->trans(sprintf('%s.%s.0', $ptable, $field), [], 'contao_default');
        $tpl->value = $value;
        $tpl->buttons = $this->renderSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $field, $value, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    protected function renderSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single_buttons');
        $tpl->pid = $pid;
        $tpl->ptable = $ptable;
        $tpl->field = $field;
        $tpl->delete = PersonalData::DELETED === $value ? '' : $this->renderSingleItemBodyPersonalDataSingleButtonDelete($pid, $ptable, $field, $value, $personalDatas, $originalModel);

        return $tpl->parse();
    }

    protected function renderSingleItemBodyPersonalDataSingleButtonDelete(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
    {
        return sprintf('<a href="#" title="%s" data-confirm="%s" class="pdm-button pdm-button_delete pdm-item__personal_data_single__button_delete">%s</a>',
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonDeleteTitle', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonDeleteConfirm', [], 'contao_default'),
            $this->translator->trans('WEM.PEDAMA.ITEM.buttonDelete', [], 'contao_default')
        );
    }

    protected function getOriginalObject(int $pid, string $ptable)
    {
        $modelClassName = Model::getClassFromTable($ptable);

        return $modelClassName::findOneById($pid);
    }

    protected function unencrypt($value)
    {
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');

        return $encryptionService->decrypt($value);
    }
}
