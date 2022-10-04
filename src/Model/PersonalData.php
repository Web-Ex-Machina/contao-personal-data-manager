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
    public const DELETED = 'deleted';
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_wem_personal_data';

    /**
     * Find records by ptable.
     *
     * @param string $ptable The ptable
     *
     * @return \Contao\Collection|null
     */
    public static function findByPtable(string $ptable)
    {
        return static::findItems(['ptable' => $ptable]);
    }

    /**
     * Find records by pid and ptable.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     *
     * @return \Contao\Collection|null
     */
    public static function findByPidAndPtable(string $pid, string $ptable)
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
    public static function findOneByPidAndPtableAndField(string $pid, string $ptable, string $field)
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
     * @return \Contao\Model\Collection|null
     */
    public static function findByPidAndPtableAndEmail(string $pid, string $ptable, string $email)
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable, 'email' => $email]);
    }

    /**
     * Find one record by pid, ptable and email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     *
     * @return self|null
     */
    public static function findOneByPidAndPtableAndEmail(string $pid, string $ptable, string $email)
    {
        $collection = static::findItems(['pid' => $pid, 'ptable' => $ptable, 'email' => $email], 1);

        return !$collection ? null : $collection->current();
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
    public static function findOneByPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field)
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
     * @param string $ptable The ptable
     *
     * @return array The array of deleted ids
     */
    public static function deleteByPtable(string $ptable): array
    {
        $ids = [];
        $items = self::findByPtable($ptable);
        if ($items) {
            while ($items->next()) {
                $ids[] = $items->id;
                $items->delete();
            }
        }

        return $ids;
    }

    /**
     * Delete rows by pid and ptable.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     *
     * @return array The array of deleted ids
     */
    public static function deleteByPidAndPtable(string $pid, string $ptable): array
    {
        $ids = [];
        $items = self::findByPidAndPtable($pid, $ptable);
        if ($items) {
            while ($items->next()) {
                $ids[] = $items->id;
                $items->delete();
            }
        }

        return $ids;
    }

    /**
     * Delete rows by email.
     *
     * @param string $email The email
     *
     * @return array The array of deleted ids
     */
    public static function deleteByEmail(string $email): array
    {
        $ids = [];
        $items = self::findByEmail($email);
        if ($items) {
            while ($items->next()) {
                $ids[] = $items->id;
                $items->delete();
            }
        }

        return $ids;
    }

    /**
     * Delete rows by pid, ptable and email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     *
     * @return array The array of deleted ids
     */
    public static function deleteByPidAndPtableAndEmail(string $pid, string $ptable, string $email): array
    {
        $ids = [];
        $items = self::findByPidAndPtableAndEmail($pid, $ptable, $email);
        if ($items) {
            while ($items->next()) {
                $ids[] = $items->id;
                $items->delete();
            }
        }

        return $ids;
    }

    /**
     * Delete rows by pid, ptable and email.
     *
     * @param string $pid    The pid
     * @param string $ptable The ptable
     * @param string $email  The email
     * @param string $field  The field
     *
     * @return array The array of deleted ids
     */
    public static function deleteByPidAndPtableAndEmailAndField(string $pid, string $ptable, string $email, string $field): array
    {
        $ids = [];
        $item = self::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);
        if ($item) {
            $ids[] = $item->id;
            $item->delete();
        }

        return $ids;
    }
}
