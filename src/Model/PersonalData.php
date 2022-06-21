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
    protected static $strTable = 'tl_personal_data';

    /**
     * Find records by pid and ptable.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     *
     * @return \Contao\Collection|null
     */
    public static function findByPidAndPTable(string $pid, string $ptable)
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable]);
    }

    /**
     * Find record by pid, ptable and field.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $field  The field
     *
     * @return self|null
     */
    public static function findOneByPidAndPTableAndField(string $pid, string $ptable, string $field)
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable, 'field' => $field], 1);
    }

    /**
     * Find records by pid, ptable and email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     *
     * @return \Contao\Collection|null
     */
    public static function findByPidAndPTableAndEmail(string $pid, string $ptable, string $email)
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable, 'email' => $email]);
    }

    /**
     * Find record by pid, ptable and field.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     * @param string $field  The field
     *
     * @return self|null
     */
    public static function findOneByPidAndPTableAndEmailAndField(string $pid, string $ptable, string $email, string $field)
    {
        $collection = static::findItems(['pid' => $pid, 'ptable' => $ptable, 'email' => $email, 'field' => $field], 1);

        return !$collection ? null : $collection->current();
    }

    /**
     * Find records by email.
     *
     * @param string     $email   The email
     * @param int|null   $limit   The limit
     * @param int|null   $offset  The offset
     * @param array|null $options The options
     *
     * @return \Contao\Collection|null
     */
    public static function findByEmail(string $email, ?int $limit = 0, ?int $offset = 0, ?array $options = [])
    {
        return static::findItems(['email' => $email], $limit, $offset, $options);
    }

    /**
     * Delete rows by pid and ptable.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     */
    public static function deleteByPidAndPTable(string $pid, string $ptable): void
    {
        $items = self::findByPidAndPTable($pid, $ptable);
        if ($items) {
            while ($items->next()) {
                $items->delete();
            }
        }
    }

    /**
     * Delete rows by email.
     *
     * @param string $email The email
     */
    public static function deleteByEmail(string $email): void
    {
        $items = self::findByEmail($email);
        if ($items) {
            while ($items->next()) {
                $items->delete();
            }
        }
    }

    /**
     * Delete rows by pid, ptable and email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     */
    public static function deleteByPidAndPTableAndEmail(string $pid, string $ptable, string $email): void
    {
        $items = self::findByPidAndPTableAndEmail($pid, $ptable, $email);
        if ($items) {
            while ($items->next()) {
                $items->delete();
            }
        }
    }
}
