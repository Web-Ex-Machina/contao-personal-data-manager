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

require_once getenv('WORKDIR_CONTAO').'/vendor/autoload.php';
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Symfony\Component\Console\Input\ArgvInput;

$kernel = ContaoKernel::fromInput(getenv('WORKDIR_CONTAO'), new ArgvInput());
$kernel->boot();
$kernel->getContainer()->get('contao.framework')->initialize();

doCreateBareContaoMinimumForTest();

function doCreateBareContaoMinimumForTest(): void
{
    $theme = \Contao\ThemeModel::findOneByName(getenv('BUNDLE_NAME'));
    if (!$theme) {
        $theme = new \Contao\ThemeModel();
    }
    $theme->tstamp = time();
    $theme->name = getenv('BUNDLE_NAME');
    $theme->alias = 'pdm';
    $theme->save();

    $mainLayout = \Contao\LayoutModel::findByName('standard');
    if (!$mainLayout) {
        $mainLayout = new \Contao\LayoutModel();
    }
    $mainLayout->pid = $theme->id;
    $mainLayout->tstamp = time();
    $mainLayout->name = 'standard';
    $mainLayout->rows = '1rw';
    $mainLayout->cols = '1cl';
    $mainLayout->sections = serialize([]);
    $mainLayout->framework = serialize([]);
    $mainLayout->modules = serialize([['mod' => '0', 'col' => 'main', 'enable' => '1']]);
    $mainLayout->save();

    $rootPage = \Contao\PageModel::findByAlias('root');
    if (!$rootPage) {
        $rootPage = new \Contao\PageModel();
    }
    $rootPage->tstamp = time();
    $rootPage->title = 'root';
    $rootPage->alias = 'root';
    $rootPage->type = 'root';
    $rootPage->language = 'en';
    $rootPage->fallback = 1;
    $rootPage->includeLayout = 1;
    $rootPage->layout = $mainLayout->id;
    $rootPage->published = 1;
    $rootPage->save();

    $mainPage = \Contao\PageModel::findByAlias('home');
    if (!$mainPage) {
        $mainPage = new \Contao\PageModel();
    }
    $mainPage->pid = $rootPage->id;
    $mainPage->tstamp = time();
    $mainPage->title = 'home';
    $mainPage->alias = 'home';
    $mainPage->type = 'regular';
    $mainPage->includeLayout = 1;
    $mainPage->layout = $mainLayout->id;
    $mainPage->published = 1;
    $mainPage->save();

    $mainPageArticle = \Contao\ArticleModel::findBy('alias', 'home_article');
    if (!$mainPageArticle) {
        $mainPageArticle = new \Contao\ArticleModel();
    }
    $mainPageArticle->tstamp = time();
    $mainPageArticle->pid = $mainPage->id;
    $mainPageArticle->title = 'home_article';
    $mainPageArticle->alias = 'home_article';
    $mainPageArticle->author = 1;
    $mainPageArticle->published = 1;
    $mainPageArticle->save();

    $form1 = \Contao\FormModel::findByAlias('form_1');
    if (!$form1) {
        $form1 = new \Contao\FormModel();
    }
    $form1->title = 'Form #1';
    $form1->alias = 'form_1';
    $form1->jumpTo = $mainPage->id;
    $form1->tstamp = time();
    $form1->save();

    $mainContent = \Contao\ContentModel::findById(1);
    if (!$mainContent) {
        $mainContent = new \Contao\ContentModel();
    }
    $mainContent->id = 1;
    $mainContent->type = 'form';
    $mainContent->headline = 'Form #1';
    $mainContent->alias = 'form_1';
    $mainContent->pid = $mainPageArticle->id;
    $mainContent->ptable = 'tl_article';
    $mainContent->form = $form1->id;
    $mainContent->tstamp = time();
    $mainContent->save();

    // add form fields
    $form1Mail = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form1->id, 'email']);
    if (!$form1Mail) {
        $form1Mail = new \Contao\FormFieldModel();
    }
    $form1Mail->type = 'text';
    $form1Mail->pid = $form1->id;
    $form1Mail->name = 'email';
    $form1Mail->label = 'Your email';
    $form1Mail->mandatory = 1;
    $form1Mail->rgxp = 'email';
    $form1Mail->containsPersonalData = 1;
    $form1Mail->tstamp = time();
    $form1Mail->save();

    $form1Submit = \Contao\FormFieldModel::findBy(['pid = ?', 'type = ?'], [$form1->id, 'submit']);
    if (!$form1Submit) {
        $form1Submit = new \Contao\FormFieldModel();
    }
    $form1Submit->type = 'submit';
    $form1Submit->pid = $form1->id;
    $form1Submit->slabel = 'Send';
    $form1Submit->tstamp = time();
    $form1Submit->save();
}
