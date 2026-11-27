<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_wem_personal_data_access_token'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'email,token' => 'index',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'flag' => DataContainer::SORT_MONTH_DESC,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'email' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'token' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'expiresAt' => [
            'flag' => DataContainer::SORT_MONTH_DESC,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'createdAt' => [
            'flag' => DataContainer::SORT_MONTH_DESC,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
