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

namespace WEM\PersonalDataManagerBundle\Controller;

use Contao\Input;
use Contao\Environment;
use Contao\BackendTemplate;
use Contao\DataContainer;
use Contao\RequestToken;
use Contao\System;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @Route("%contao.backend.route_prefix%/wem-personal-data-manager",
 *     name=BackendController::class,
 *     defaults={"_scope": "backend"}
 * )
 * @ServiceTag("controller.service_arguments")
 */
class PersonalDataManagerController extends AbstractController
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_wem_personal_data_manager';

    public function __construct(
        ?DataContainer $dc
    ){
    }

    public function generate()
    {
        $tpl = new BackendTemplate($this->strTemplate);

        $tpl->email = Input::post('email') ?? '';
        $tpl->request = Environment::get('request');
        $tpl->token = RequestToken::get();
        if(empty($tpl->email)){
            $tpl->content = $GLOBALS['TL_LANG']['WEM']['PEDAMA']['DEFAULT']['PleaseFillEmail'];
        }else{
            $tpl->content = System::getContainer()->get('wem.personal_data_manager.service.personal_data_manager_ui')->listForEmail($tpl->email);

        }

        return $tpl->parse();
    }
}
