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

$GLOBALS['TL_DCA']['tl_wem_personal_data'] = [
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
        'tstamp' => [
            'flag' => 8,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'pid' => [
            'label' => ['pid'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'ptable' => [
            'label' => ['ptable'],
            'sql' => "varchar(255) NOT NULL DEFAULT ''",
        ],
        'email' => [
            'label' => ['email'],
            'sql' => "varchar(255) NOT NULL DEFAULT ''",
        ],
        'field' => [
            'label' => ['field'],
            'sql' => "varchar(255) NOT NULL DEFAULT ''",
        ],
        'value' => [
            'label' => ['value'],
            'sql' => "TEXT NOT NULL DEFAULT ''",
            'load_callback' => [
                ['plenta.encryption', 'decrypt'],
            ],
            'save_callback' => [
                ['plenta.encryption', 'encrypt'],
            ],
        ],
        'createdAt' => [
            'default' => time(),
            'flag' => 8,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
