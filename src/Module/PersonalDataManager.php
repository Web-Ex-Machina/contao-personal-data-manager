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
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\System;

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
        if (Input::get('pdm_token')) {
            $this->Template->subtemplate = 'mod_personaldatamanager_manager';
            $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/pdm.css';
            // check the token is connected to an email and is valid and equals to the email in session
            // if so, display PDMUI/** @var PersonalDataManagerUi */
            $pdmUi = System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager_ui');
            $this->Template->content = $pdmUi->listForEmail('aa@aa.fr');
        // if not, error message and display form
        } else {
            $this->Template->subtemplate = 'mod_personaldatamanager_emailform';
            if ('wem-personal-data-manager' === Input::post('FORM_SUBMIT')) {
                // create token
                // put email in session
                // send email
                // display confirmation message
                Message::addConfirmation('ouiiiii');
            }
            // display form
            $this->Template->email = Input::post('email') ?? '';
            $this->Template->request = Environment::get('request');
            $this->Template->token = RequestToken::get();
        }
    }
}
