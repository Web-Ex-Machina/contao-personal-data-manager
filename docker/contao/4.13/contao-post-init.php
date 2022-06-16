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

    $form1 = doCreateForm1($mainPage->id);
    $form2 = doCreateForm2($mainPage->id);

    $mainContent1 = \Contao\ContentModel::findById(1);
    if (!$mainContent1) {
        $mainContent1 = new \Contao\ContentModel();
    }
    $mainContent1->id = 1;
    $mainContent1->type = 'form';
    $mainContent1->headline = 'Form #1';
    $mainContent1->alias = 'form_1';
    $mainContent1->pid = $mainPageArticle->id;
    $mainContent1->ptable = 'tl_article';
    $mainContent1->form = $form1->id;
    $mainContent1->tstamp = time();
    $mainContent1->save();

    $mainContent2 = \Contao\ContentModel::findById(2);
    if (!$mainContent2) {
        $mainContent2 = new \Contao\ContentModel();
    }
    $mainContent2->id = 2;
    $mainContent2->type = 'form';
    $mainContent2->headline = 'Form #2';
    $mainContent2->alias = 'form_2';
    $mainContent2->pid = $mainPageArticle->id;
    $mainContent2->ptable = 'tl_article';
    $mainContent2->form = $form2->id;
    $mainContent2->tstamp = time();
    $mainContent2->save();
}

function doCreateForm1(string $pageId)
{
    $form = \Contao\FormModel::findByAlias('form_1');
    if (!$form) {
        $form = new \Contao\FormModel();
    }
    $form->title = 'Form #1';
    $form->alias = 'form_1';
    $form->jumpTo = $pageId;
    $form->storeValues = '1';
    $form->targetTable = 'tl_form_data_1';
    $form->tstamp = time();
    $form->save();

    $formMail = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'email']);
    if (!$formMail) {
        $formMail = new \Contao\FormFieldModel();
    }
    $formMail->type = 'text';
    $formMail->pid = $form->id;
    $formMail->name = 'email';
    $formMail->label = 'Your email';
    $formMail->mandatory = 1;
    $formMail->rgxp = 'email';
    $formMail->containsPersonalData = 1;
    $formMail->tstamp = time();
    $formMail->save();

    $formPhone = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'phone']);
    if (!$formPhone) {
        $formPhone = new \Contao\FormFieldModel();
    }
    $formPhone->type = 'text';
    $formPhone->pid = $form->id;
    $formPhone->name = 'phone';
    $formPhone->label = 'Your phone';
    $formPhone->mandatory = 1;
    $formPhone->rgxp = 'phone';
    $formPhone->containsPersonalData = 1;
    $formPhone->tstamp = time();
    $formPhone->save();

    $formSubject = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'subject']);
    if (!$formSubject) {
        $formSubject = new \Contao\FormFieldModel();
    }
    $formSubject->type = 'text';
    $formSubject->pid = $form->id;
    $formSubject->name = 'subject';
    $formSubject->label = 'Subject';
    $formSubject->mandatory = 1;
    $formSubject->containsPersonalData = 0;
    $formSubject->tstamp = time();
    $formSubject->save();

    $formMessage = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'message']);
    if (!$formMessage) {
        $formMessage = new \Contao\FormFieldModel();
    }
    $formMessage->type = 'textarea';
    $formMessage->pid = $form->id;
    $formMessage->name = 'message';
    $formMessage->label = 'Message';
    $formMessage->mandatory = 1;
    $formMessage->containsPersonalData = 0;
    $formMessage->tstamp = time();
    $formMessage->save();

    $formSubmit = \Contao\FormFieldModel::findBy(['pid = ?', 'type = ?'], [$form->id, 'submit']);
    if (!$formSubmit) {
        $formSubmit = new \Contao\FormFieldModel();
    }
    $formSubmit->type = 'submit';
    $formSubmit->pid = $form->id;
    $formSubmit->slabel = 'Send';
    $formSubmit->tstamp = time();
    $formSubmit->save();

    return $form;
}
function doCreateForm2(string $pageId)
{
    $form = \Contao\FormModel::findByAlias('form_2');
    if (!$form) {
        $form = new \Contao\FormModel();
    }
    $form->title = 'Form #2';
    $form->alias = 'form_2';
    $form->jumpTo = $pageId;
    $form->storeValues = '1';
    $form->targetTable = 'tl_form_data_2';
    $form->tstamp = time();
    $form->save();

    $formMail = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'email']);
    if (!$formMail) {
        $formMail = new \Contao\FormFieldModel();
    }
    $formMail->type = 'text';
    $formMail->pid = $form->id;
    $formMail->name = 'email';
    $formMail->label = 'Your email';
    $formMail->mandatory = 1;
    $formMail->rgxp = 'email';
    $formMail->containsPersonalData = 1;
    $formMail->tstamp = time();
    $formMail->save();

    $formSex = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'sex']);
    if (!$formSex) {
        $formSex = new \Contao\FormFieldModel();
    }
    $formSex->type = 'radio';
    $formSex->pid = $form->id;
    $formSex->name = 'sex';
    $formSex->label = 'Your sex';
    $formSex->options = serialize([['value' => 'M', 'label' => 'Male'], ['value' => 'F', 'label' => 'Female'], ['value' => 'O', 'label' => 'Other']]);
    $formSex->mandatory = 1;
    $formSex->containsPersonalData = 1;
    $formSex->tstamp = time();
    $formSex->save();

    $formRelationship = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'relationship']);
    if (!$formRelationship) {
        $formRelationship = new \Contao\FormFieldModel();
    }
    $formRelationship->type = 'radio';
    $formRelationship->pid = $form->id;
    $formRelationship->name = 'relationship';
    $formRelationship->label = 'Your relationship status';
    $formRelationship->options = serialize([['value' => 'S', 'label' => 'Single'], ['value' => 'M', 'label' => 'Married'], ['value' => 'D', 'label' => 'Divorced'], ['value' => 'W', 'label' => 'Widower'], ['value' => 'O', 'label' => 'Other']]);
    $formRelationship->mandatory = 1;
    $formRelationship->containsPersonalData = 1;
    $formRelationship->tstamp = time();
    $formRelationship->save();

    $formPhone = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'phone']);
    if (!$formPhone) {
        $formPhone = new \Contao\FormFieldModel();
    }
    $formPhone->type = 'text';
    $formPhone->pid = $form->id;
    $formPhone->name = 'phone';
    $formPhone->label = 'Your phone';
    $formPhone->mandatory = 1;
    $formPhone->rgxp = 'phone';
    $formPhone->containsPersonalData = 1;
    $formPhone->tstamp = time();
    $formPhone->save();

    $formName = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'name']);
    if (!$formName) {
        $formName = new \Contao\FormFieldModel();
    }
    $formName->type = 'text';
    $formName->pid = $form->id;
    $formName->name = 'name';
    $formName->label = 'Your name';
    $formName->mandatory = 1;
    $formName->containsPersonalData = 1;
    $formName->tstamp = time();
    $formName->save();

    $formSubject = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'subject']);
    if (!$formSubject) {
        $formSubject = new \Contao\FormFieldModel();
    }
    $formSubject->type = 'text';
    $formSubject->pid = $form->id;
    $formSubject->name = 'subject';
    $formSubject->label = 'Subject';
    $formSubject->mandatory = 1;
    $formSubject->containsPersonalData = 0;
    $formSubject->tstamp = time();
    $formSubject->save();

    $formMessage = \Contao\FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, 'message']);
    if (!$formMessage) {
        $formMessage = new \Contao\FormFieldModel();
    }
    $formMessage->type = 'textarea';
    $formMessage->pid = $form->id;
    $formMessage->name = 'message';
    $formMessage->label = 'Message';
    $formMessage->mandatory = 1;
    $formMessage->containsPersonalData = 0;
    $formMessage->tstamp = time();
    $formMessage->save();

    $formSubmit = \Contao\FormFieldModel::findBy(['pid = ?', 'type = ?'], [$form->id, 'submit']);
    if (!$formSubmit) {
        $formSubmit = new \Contao\FormFieldModel();
    }
    $formSubmit->type = 'submit';
    $formSubmit->pid = $form->id;
    $formSubmit->slabel = 'Send';
    $formSubmit->tstamp = time();
    $formSubmit->save();

    return $form;
}
