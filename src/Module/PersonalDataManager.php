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

namespace WEM\PersonalDataManagerBundle\Module;

use Contao\BackendTemplate;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\Module;
use Contao\PageModel;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\System;
use Exception;

class PersonalDataManager extends Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_personaldatamanager';

    /**
     * Do not display the module if there are no articles.
     *
     * @return string
     */
    public function generate()
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.$GLOBALS['TL_LANG']['FMD']['wem_personaldatamanager'][0].' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = StringUtil::specialcharsUrl(System::getContainer()->get('router')->generate('contao_backend', ['do' => 'themes', 'table' => 'tl_module', 'act' => 'edit', 'id' => $this->id]));

            return $objTemplate->parse();
        }

        $strBuffer = parent::generate();

        return $strBuffer;
    }

    /**
     * Generate the module.
     */
    protected function compile(): void
    {
        /* @var PageModel */
        global $objPage;
        $session = System::getContainer()->get('session'); // Init session
        $pdm = System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager');

        if ('wem-personal-data-manager' === Input::post('FORM_SUBMIT')) {
            // create token
            $obj = $pdm->insertForEmail(Input::post('email'));
            // put email in session
            $session->set('wem_pdm_email', Input::post('email'));
            // send email
            $html = file_get_contents('bundles/wempersonaldatamanager/email/'.($objPage->language ?? 'fr').'/email.html5');
            $html = str_replace('[[pageId]]', $objPage->id, $html);
            $html = str_replace('[[urlParams]]', '?pdm_token='.$obj->token, $html);
            $email = new \Contao\Email();
            $email->subject = $GLOBALS['TL_LANG']['WEM']['PEDAMA']['EMAIL']['subject'];
            $email->html = $html;
            try {
                if ($email->sendTo(Input::post('email'))) {
                    Message::addConfirmation($GLOBALS['TL_LANG']['WEM']['PEDAMA']['MODFE']['emailSent']);
                } else {
                    Message::addError($GLOBALS['TL_LANG']['WEM']['PEDAMA']['MODFE']['emailNotSent']);
                }
            } catch (Exception $e) {
                Message::addError($GLOBALS['TL_LANG']['WEM']['PEDAMA']['MODFE']['emailNotSent']);
                Message::addError($html);
            }
        }

        if (Input::get('pdm_token')) {
            $this->Template->subtemplate = 'mod_personaldatamanager_manager';
            $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/pdm.css';
            // check the token is connected to an email and is valid and equals to the email in session
            if (null !== $session->get('wem_pdm_email')
                && null !== Input::get('pdm_token')
                && $pdm->isEmailTokenCoupleValid($session->get('wem_pdm_email'), Input::get('pdm_token'))
            ) {
                $pdm->updateTokenExpiration(Input::get('pdm_token'));
                $this->displayPersonalDataManagerUi($session->get('wem_pdm_email'));
            } else {
                Message::addError($GLOBALS['TL_LANG']['WEM']['PEDAMA']['MODFE']['emailTokenCoupleNotValid']);
                $this->displayForm($session->get('wem_pdm_email') ?? '');
            }
        } else {
            $this->displayForm(Input::post('email') ?? '');
        }
    }

    protected function displayForm(string $email): void
    {
        $this->Template->subtemplate = 'mod_personaldatamanager_emailform';

        // display form
        $this->Template->email = $email;
        $this->Template->request = Environment::get('request');
        $this->Template->token = RequestToken::get();
    }

    protected function displayPersonalDataManagerUi(string $email): void
    {
        $pdmUi = System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager_ui');
        $this->Template->content = $pdmUi->listForEmail($email);
    }
}
