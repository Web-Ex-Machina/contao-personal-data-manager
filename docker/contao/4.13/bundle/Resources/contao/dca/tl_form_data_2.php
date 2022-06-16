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

// THIS FILE EXISTS ONLY FOR THE DOCKER VERSION
$GLOBALS['TL_DCA']['tl_form_data_2'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => [],
        'switchToEdit' => false,
        'enableVersioning' => false,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'label' => ['ID'],
            'search' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'email' => [
            'label' => ['email'],
            'sql' => "TEXT NOT NULL DEFAULT ''",
        ],
        'name' => [
            'label' => ['name'],
            'sql' => "TEXT NOT NULL DEFAULT ''",
        ],
        'sex' => [
            'label' => ['sex'],
            'sql' => "TEXT NOT NULL DEFAULT ''",
        ],
        'relationship' => [
            'label' => ['relationship'],
            'sql' => "TEXT NOT NULL DEFAULT ''",
        ],
        'phone' => [
            'label' => ['phone'],
            'sql' => "TEXT NOT NULL DEFAULT ''",
        ],
        'subject' => [
            'label' => ['subject'],
            'sql' => "TEXT NOT NULL DEFAULT ''",
        ],
        'message' => [
            'label' => ['message'],
            'sql' => "TEXT NOT NULL DEFAULT ''",
        ],
    ],
];
