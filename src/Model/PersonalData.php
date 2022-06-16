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

namespace WEM\PersonalDataManagerBundle\Model;

use WEM\UtilsBundle\Model\Model;

class PersonalData extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_form_data';

    public static function findByPidAndPTable(string $pid, string $ptable)
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable]);
    }

    public static function findOneByPidAndPTableAndField(string $pid, string $ptable, string $field)
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable, 'field' => $field], 1);
    }

    public static function deleteByPidAndPTable(string $pid, string $ptable): void
    {
        $items = self::findByPidAndPTable($pid, $ptable);
        if ($items) {
            while ($items) {
                $items->delete();
            }
        }
    }
}
