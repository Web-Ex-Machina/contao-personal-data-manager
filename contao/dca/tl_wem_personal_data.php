<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_wem_personal_data'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,ptable' => 'index',
                'email' => 'index',
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
        'pid' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'ptable' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'email' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'field' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        // Stored encrypted (see PersonalDataManager), unless anonymized
        'value' => [
            'sql' => 'text NULL',
        ],
        'altered' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'createdAt' => [
            'flag' => DataContainer::SORT_MONTH_DESC,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'anonymized' => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'anonymizedAt' => [
            'flag' => DataContainer::SORT_MONTH_DESC,
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
