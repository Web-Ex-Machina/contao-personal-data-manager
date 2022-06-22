<?php

namespace WEM\PersonalDataManagerBundle\Service;

use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\Model;

class PersonalDataManagerUi{
	/** @var PersonalDataManager */
	private $manager;

	public function __construct(PersonalDataManager $manager)
	{
		$this->manager = $manager;
	}

	public function listForEmail(string $email): string
	{
        $tpl = new FrontendTemplate('wem_personal_data_manager_list');
        $renderedItems = [];
        $data = $this->sortData($this->manager->findByEmail($email));
        
        foreach($data as $ptable => $arrPids){
	        foreach($arrPids as $pid => $singleItemData){
	        	$renderedItems[] = $this->renderSingleItem($ptable, $pid, $singleItemData['personalDatas'], $singleItemData['originalModel']);
	        }
        }
        $tpl->items = $renderedItems;
		return $tpl->parse();
	}

	protected function sortData(?Collection $personalDatas): array
	{
		$sorted = [];
		if(!$personalDatas){
			return [];
		}

		while($personalDatas->next()){
			if(!array_key_exists($personalDatas->ptable, $sorted)){
				$sorted[$personalDatas->ptable] = [];
			}
			if(!array_key_exists($personalDatas->pid, $sorted[$personalDatas->ptable])){
				$sorted[$personalDatas->ptable][$personalDatas->pid] = [
					'originalModel'=>$this->getOriginalObject($personalDatas->pid,$personalDatas->ptable),
					'personalDatas'=>[]
				];
			}
			$sorted[$personalDatas->ptable][$personalDatas->pid]['personalDatas'][$personalDatas->field] = $this->unencrypt($personalDatas->value);
		}
		ksort($sorted);
		foreach($sorted as $ptable => $pids){
			ksort($pids);
			$sorted[$ptable] = $pids;
		}

		return $sorted;
	}

	protected function renderSingleItem(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
        $tpl = new FrontendTemplate('wem_personal_data_manager_item');
        $tpl->header = $this->renderSingleItemHeader($pid, $ptable, $personalDatas, $originalModel);
        $tpl->body = $this->renderSingleItemHeader($pid, $ptable, $personalDatas, $originalModel);
		return $tpl->parse();
	}

	protected function renderSingleItemHeader(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_header');
        $tpl->title = $this->renderSingleItemTitle($pid, $ptable, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemButtons($pid, $ptable, $personalDatas, $originalModel);
		return $tpl->parse();
	}

	protected function renderSingleItemTitle(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_title');
        $tpl->data = $ptable . '('.$pid.')';
		return $tpl->parse();
	}

	protected function renderSingleItemButtons(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_buttons');
        $tpl->delete = $this->renderSingleItemButtonDelete($pid, $ptable, $personalDatas, $originalModel);
        $tpl->export = $this->renderSingleItemButtonExport($pid, $ptable, $personalDatas, $originalModel);
        $tpl->show = $this->renderSingleItemButtonShow($pid, $ptable, $personalDatas, $originalModel);
		return $tpl->parse();
	}

	protected function renderSingleItemButtonDelete(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
		return '';
	}

	protected function renderSingleItemButtonExport(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
		return '';
	}

	protected function renderSingleItemButtonShow(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
		return '';
	}

	protected function renderSingleItemBody(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_body');
        $tpl->originalModel = $this->renderSingleItemBodyOriginalModel($pid, $ptable, $personalDatas, $originalModel);
        $tpl->personalData = $this->renderSingleItemBodyPersonalData($pid, $ptable, $personalDatas, $originalModel);
		return $tpl->parse();
	}

	protected function renderSingleItemBodyOriginalModel(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
		return '';
	}

	protected function renderSingleItemBodyPersonalData(string $pid, string $ptable, array $personalDatas, Model $originalModel): string
	{
		return '';
	}

	protected function renderSingleItemBodyPersonalDataSingle(string $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
	{
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single_buttons');
        $tpl->field = $this->renderSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $field, $value, $personalDatas, $originalModel);
        $tpl->value = $this->renderSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $field, $value, $personalDatas, $originalModel);
        $tpl->buttons = $this->renderSingleItemBodyPersonalDataSingleButtons($pid, $ptable, $field, $value, $personalDatas, $originalModel);
		return $tpl->parse();
	}

	protected function renderSingleItemBodyPersonalDataSingleButtons(string $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
	{
        $tpl = new FrontendTemplate('wem_personal_data_manager_item_personal_data_single_buttons');
        $tpl->delete = $this->renderSingleItemBodyPersonalDataSingleButtonDelete($pid, $ptable, $field, $value, $personalDatas, $originalModel);
		return $tpl->parse();
	}

	protected function renderSingleItemBodyPersonalDataSingleButtonDelete(string $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel): string
	{
		return '';
	}

	protected function getOriginalObject(string $pid, string $ptable){

        $modelClassName = Model::getClassFromTable($ptable);
        dump($modelClassName);
        return $modelClassName::findOneById($pid);
	}

	protected function unencrypt($value){
        $encryptionService = \Contao\System::getContainer()->get('plenta.encryption');
		return $encryptionService->decrypt($value);
	}
}