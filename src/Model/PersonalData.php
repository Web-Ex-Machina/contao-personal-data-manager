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

use Contao\Model\Collection;
use Exception;
use WEM\UtilsBundle\Model\Model;

class PersonalData extends Model
{
    public const DELETED = 'deleted';

    /**
     * Table name.
     */
    protected static $strTable = 'tl_wem_personal_data';

    /**
     * Find records by ptable.
     *
     * @param string $ptable The ptable
     *
     * @return Collection|null
     * @throws Exception
     */
    public static function findByPtable(string $ptable): ?Collection
    {
        return static::findItems(['ptable' => $ptable]);
    }

    /**
     * Find records by pid and ptable.
     *
     * @param int $pid The pid
     * @param string $ptable The ptable
     *
     * @return Collection|null
     * @throws Exception
     */
    public static function findByPidAndPtable(int $pid, string $ptable): ?Collection
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable]);
    }

    /**
     * Find record by pid, ptable and field.
     *
     * @param int $pid The pid
     * @param string $ptable The ptable
     * @param string $field The field
     *
     * @return Collection|null
     * @throws Exception
     */
    public static function findOneByPidAndPtableAndField(int $pid, string $ptable, string $field): ?Collection
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable, 'field' => $field], 1);
    }

    /**
     * Find records by pid, ptable and email.
     *
     * @param int $pid The pid
     * @param string $ptable The ptable
     * @param string $email The email
     *
     * @return Collection|null
     * @throws Exception
     */
    public static function findByPidAndPtableAndEmail(int $pid, string $ptable, string $email): ?Collection
    {
        return static::findItems(['pid' => $pid, 'ptable' => $ptable, 'email' => $email]);
    }

    /**
     * Find one record by pid, ptable and email.
     *
     * @param int $pid The pid
     * @param string $ptable The ptable
     * @param string $email The email
     *
     * @return \Contao\Model|null
     * @throws Exception
     */
    public static function findOneByPidAndPtableAndEmail(int $pid, string $ptable, string $email): ?\Contao\Model
    {
        $collection = static::findItems(['pid' => $pid, 'ptable' => $ptable, 'email' => $email], 1);

        return $collection ? $collection->current() : null;
    }

    /**
     * Find record by pid, ptable and field.
     *
     * @param int $pid The pid
     * @param string $ptable The ptable
     * @param string $email The email
     * @param string $field The field
     *
     * @return \Contao\Model|null
     * @throws Exception
     */
    public static function findOneByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): ?\Contao\Model
    {
        $collection = static::findItems(['pid' => $pid, 'ptable' => $ptable, 'email' => $email, 'field' => $field], 1);

        return $collection ? $collection->current() : null;
    }

    /**
     * Find records by email.
     *
     * @param string $email The email
     * @param int|null $limit The limit
     * @param int|null $offset The offset
     * @param array|null $options The options
     *
     * @return Collection|null
     * @throws Exception
     */
    public static function findByEmail(string $email, ?int $limit = 0, ?int $offset = 0, ?array $options = []): ?Collection
    {
        return static::findItems(['email' => $email], $limit, $offset, $options);
    }

    /**
     * Delete rows by pid and ptable.
     *
     * @param string $ptable The ptable
     *
     * @return array The array of deleted ids
     * @throws Exception
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
     * @param int $pid The pid
     * @param string $ptable The ptable
     *
     * @return array The array of deleted ids
     * @throws Exception
     */
    public static function deleteByPidAndPtable(int $pid, string $ptable): array
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
     * @throws Exception
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
     * @param int $pid The pid
     * @param string $ptable The ptable
     * @param string $email The email
     *
     * @return array The array of deleted ids
     * @throws Exception
     */
    public static function deleteByPidAndPtableAndEmail(int $pid, string $ptable, string $email): array
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
     * @param int $pid The pid
     * @param string $ptable The ptable
     * @param string $email The email
     * @param string $field The field
     *
     * @return array The array of deleted ids
     * @throws Exception
     */
    public static function deleteByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): array
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
