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

namespace WEM\PersonalDataManagerBundle\EventListener;

use Contao\Form;
use Contao\FormFieldModel;

class StoreFormDataListener
{
    public function __invoke(array $data, Form $form): array
    {
        $personalData = [];
        $fields = FormFieldModel::findByPid($form->id);
        foreach ($fields as $field) {
            if ($field->containsPersonalData) {
                $personalData[$field->name] = $data[$field->name];
                unset($data[$field->name]);
            }
        }

        return $data;
    }
}
