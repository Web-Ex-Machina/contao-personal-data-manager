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

use Contao\BackendUser;
use Contao\File;
use Contao\FrontendUser;
use Contao\Input;
use Contao\System;
use Contao\User;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Exception\AccessDeniedException;

class PersonalDataManagerAction
{

    private TranslatorInterface $translator;

    private PersonalDataManager $manager;

    private User $user;

    public function __construct(
        TranslatorInterface $translator,
        PersonalDataManager $manager
    ) {
        $this->translator = $translator;
        $this->manager = $manager;
    }

    /**
     * Process AJAX actions.
     *
     * @return void - Ajax response, as String or JSON
     */
    public function processAjaxRequest(): void
    {
        $returnHttpCode = Response::HTTP_OK;
        // Catch AJAX Requests
        if (Input::post('TL_WEM_AJAX') && 'be_pdm' === Input::post('wem_module')) {
            try {
                switch (Input::post('action')) {
                    case 'anonymize_single_personal_data':
                        $arrResponse = $this->anonymizeSinglePersonalData();
                    break;
                    case 'anonymize_personal_data_item':
                        $arrResponse = $this->anonymizeSingleItem();
                    break;
                    case 'anonymize_all_personal_data':
                        $arrResponse = $this->anonymizeAllPersonalData();
                    break;
                    case 'export_single':
                        $this->exportSingleItem();
                    break;
                    case 'export_all':
                        $this->exportAllPersonalData();
                    break;
                    case 'show_personal_data_item':
                        $arrResponse = $this->showSingleItem();
                    break;
                    case 'show_file_single_personal_data':
                        $arrResponse = $this->showFileSinglePersonalData();
                    break;
                    case 'download_file_single_personal_data':
                        $arrResponse = $this->downloadFileSinglePersonalData();
                    break;
                    default:
                        throw new Exception('Unknown route');
                }
            } catch (AccessDeniedException $e) {
                $returnHttpCode = Response::HTTP_UNAUTHORIZED;
                $arrResponse = ['status' => 'error', 'msg' => $e->getMessage(), 'trace' => $e->getTrace()];
            } catch (Exception $e) {
                $returnHttpCode = Response::HTTP_BAD_REQUEST;
                $arrResponse = ['status' => 'error', 'msg' => $e->getMessage(), 'trace' => $e->getTrace()];
            }

            // Add Request Token to JSON answer and return
            $arrResponse['rt'] = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
            $response = new Response(json_encode($arrResponse), $returnHttpCode);
            $response->send();

            exit;
        }
    }

    /**
     * @throws AccessDeniedException
     * @throws Exception
     */
    protected function anonymizeSinglePersonalData(): array
    {

        if (empty(Input::post('pid'))) {
            throw new InvalidArgumentException($this->translator->trans('WEM.PEDAMA.DEFAULT.PleaseFillEmail', [], 'contao_default'));
        }
        $this->check_list(['ptable', 'email', 'field']);

        $this->checkAccess();

        $anonymizeValue = $this->manager->anonymizeByPidAndPtableAndEmailAndField((int) Input::post('pid'), Input::post('ptable'), Input::post('email'), Input::post('field'));

        return [
            'status' => 'success',
            'msg' => '',
            'value' => $anonymizeValue,
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws Exception
     */
    protected function anonymizeSingleItem(): array
    {
        $this->check_list(['pid', 'ptable', 'email']);

        $this->checkAccess();

        $anonymizeValues = $this->manager->anonymizeByPidAndPtableAndEmail((int) Input::post('pid'), Input::post('ptable'), Input::post('email'));

        return [
            'status' => 'success',
            'msg' => '',
            'values' => $anonymizeValues,
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws Exception
     */
    protected function anonymizeAllPersonalData(): array
    {
        $this->check_list(['email']);

        $this->checkAccess();

        $anonymizeValues = $this->manager->anonymizeByEmail(Input::post('email'));

        return [
            'status' => 'success',
            'msg' => '',
            'values' => $anonymizeValues,
        ];
    }

    /**
     * @throws AccessDeniedException
     */
    protected function exportSingleItem(): void
    {
        $this->check_list(['pid', 'ptable', 'email']);

        $this->checkAccess();

        $zipName = $this->manager->exportByPidAndPtableAndEmail((int) Input::post('pid'), Input::post('ptable'), Input::post('email'));
        $zipContent = file_get_contents($zipName);
        unlink($zipName);
        (new Response($zipContent, Response::HTTP_OK, [
            'Content-Type' => ' application/zip',
            'Content-Disposition' => 'attachment',
            'filename' => $this->translator->trans('WEM.PEDAMA.CSV.filenameSingleItem', [], 'contao_default').'.zip',
        ]))->send();
        exit();
    }

    /**
     * @throws AccessDeniedException
     * @throws Exception
     */
    protected function exportAllPersonalData(): void
    {
        $this->check_list(['email']);

        $this->checkAccess();

        $zipName = $this->manager->exportByEmail(Input::post('email'));
        $zipContent = file_get_contents($zipName);
        unlink($zipName);
        (new Response($zipContent, Response::HTTP_OK, [
            'Content-Type' => ' application/zip',
            'Content-Disposition' => 'attachment',
            'filename' => $this->translator->trans('WEM.PEDAMA.CSV.filenameAll', [], 'contao_default').'.zip',
        ]))->send();
        exit();
    }

    /**
     * @throws AccessDeniedException
     */
    protected function showSingleItem(): array
    {
        $this->check_list(['pid', 'ptable', 'email']);

        $this->checkAccess();

        $href = $this->manager->getHrefByPidAndPtableAndEmail((int) Input::post('pid'), Input::post('ptable'), Input::post('email'));

        if (empty($href)) {
            throw new Exception($this->translator->trans('WEM.PEDAMA.DEFAULT.noUrlProvided', [], 'contao_default'));
        }

        return [
            'status' => 'success',
            'msg' => '',
            'href' => $href,
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws Exception
     */
    protected function showFileSinglePersonalData(): array
    {
        $this->check_list(['pid', 'ptable', 'email', 'field']);

        $this->checkAccess();

        $objFile = $this->manager->getFileByPidAndPtableAndEmailAndField((int) Input::post('pid'), Input::post('ptable'), Input::post('email'), Input::post('field'));

        $content = $objFile instanceof File ? sprintf(
            'data:%s;base64,%s',
            $objFile->mime,
            base64_encode($objFile->getContent())
        ) : '';

        return [
            'status' => $objFile instanceof File ? 'success' : 'error',
            'msg' => '',
            'content' => $content,
            'name' => $objFile instanceof File ? $objFile->name : '',
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws Exception
     */
    protected function downloadFileSinglePersonalData(): void
    {
        $this->check_list(['pid', 'ptable', 'email', 'field']);

        $this->checkAccess();

        $objFile = $this->manager->getFileByPidAndPtableAndEmailAndField((int) Input::post('pid'), Input::post('ptable'), Input::post('email'), Input::post('field'));

        // $content = $objFile ? sprintf(
        //     'data:%s;base64,%s',
        //     $objFile->mime,
        //     base64_encode($objFile->getContent())
        // ) : '';

        (new Response($objFile->getContent(), Response::HTTP_OK, [
            'Content-Type' => $objFile->mime,
            'Content-Disposition' => 'attachment',
            'filename' => $objFile->name,
        ]))->send();
        exit();
    }

    /**
     * @throws AccessDeniedException
     * @throws Exception
     */
    protected function checkAccess(): void
    {
        $this->user = BackendUser::getInstance();

        if ($this->user->isAdmin) {
            return;
        }

        $this->user = FrontendUser::getInstance();

        if (!$this->manager->isEmailTokenCoupleValid(Input::post('email'), $this->manager->getTokenInSession())) {
            $this->manager->clearTokenInSession();
            throw new AccessDeniedException($this->translator->trans('WEM.PEDAMA.DEFAULT.accessDenied', [], 'contao_default'));
        }

        $this->manager->updateTokenExpiration($this->manager->getTokenInSession());
    }

    private function check_list(array $list): void
    {
        foreach ($list as $item) {
            if (empty(Input::post($item))) {
                throw new InvalidArgumentException($this->translator->trans('WEM.PEDAMA.DEFAULT.' . $item . 'Empty', [], 'contao_default'));
            }
        }
    }
}
