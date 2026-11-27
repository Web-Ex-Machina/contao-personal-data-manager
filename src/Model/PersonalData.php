<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Model;

use Contao\Model\Collection;
use WEM\UtilsBundle\Model\Model;

/**
 * @property int        $id
 * @property int        $tstamp
 * @property int        $pid
 * @property string     $ptable
 * @property string     $email
 * @property string     $field
 * @property string     $value
 * @property string     $altered
 * @property int|string $createdAt
 * @property bool|string $anonymized
 * @property int|string $anonymizedAt
 */
class PersonalData extends Model
{
    public const string DELETED = 'deleted';

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_wem_personal_data';

    /**
     * @throws \Exception
     */
    public static function findByPtable(string $ptable): Collection|null
    {
        return static::findItems([
            'ptable' => $ptable,
        ]);
    }

    /**
     * @throws \Exception
     */
    public static function findByPidAndPtable(int $pid, string $ptable): Collection|null
    {
        return static::findItems([
            'pid' => $pid,
            'ptable' => $ptable,
        ]);
    }

    /**
     * @throws \Exception
     */
    public static function findOneByPidAndPtableAndField(int $pid, string $ptable, string $field): self|null
    {
        return self::findOne(static::findItems([
            'pid' => $pid,
            'ptable' => $ptable,
            'field' => $field,
        ], 1));
    }

    /**
     * @throws \Exception
     */
    public static function findByPidAndPtableAndEmail(int $pid, string $ptable, string $email): Collection|null
    {
        return static::findItems([
            'pid' => $pid,
            'ptable' => $ptable,
            'email' => $email,
        ]);
    }

    /**
     * @throws \Exception
     */
    public static function findOneByPidAndPtableAndEmail(int $pid, string $ptable, string $email): self|null
    {
        return self::findOne(static::findItems([
            'pid' => $pid,
            'ptable' => $ptable,
            'email' => $email,
        ], 1));
    }

    /**
     * @throws \Exception
     */
    public static function findOneByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): self|null
    {
        return self::findOne(static::findItems([
            'pid' => $pid,
            'ptable' => $ptable,
            'email' => $email,
            'field' => $field,
        ], 1));
    }

    /**
     * @throws \Exception
     */
    public static function findByEmail(string $email, int $limit = 0, int $offset = 0, array $options = []): Collection|null
    {
        return static::findItems([
            'email' => $email,
        ], $limit, $offset, $options);
    }

    /**
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public static function deleteByPtable(string $ptable): array
    {
        return self::deleteCollection(self::findByPtable($ptable));
    }

    /**
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public static function deleteByPidAndPtable(int $pid, string $ptable): array
    {
        return self::deleteCollection(self::findByPidAndPtable($pid, $ptable));
    }

    /**
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public static function deleteByEmail(string $email): array
    {
        return self::deleteCollection(self::findByEmail($email));
    }

    /**
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public static function deleteByPidAndPtableAndEmail(int $pid, string $ptable, string $email): array
    {
        return self::deleteCollection(self::findByPidAndPtableAndEmail($pid, $ptable, $email));
    }

    /**
     * @return array<int> The deleted ids
     *
     * @throws \Exception
     */
    public static function deleteByPidAndPtableAndEmailAndField(int $pid, string $ptable, string $email, string $field): array
    {
        $item = self::findOneByPidAndPtableAndEmailAndField($pid, $ptable, $email, $field);

        if (! $item instanceof self) {
            return [];
        }

        $id = (int) $item->id;
        $item->delete();

        return [$id];
    }

    /**
     * @return array<int> The deleted ids
     */
    private static function deleteCollection(Collection|null $items): array
    {
        $ids = [];

        foreach ($items ?? [] as $item) {
            $ids[] = (int) $item->id;
            $item->delete();
        }

        return $ids;
    }

    private static function findOne(Collection|null $items): self|null
    {
        $item = $items?->current();

        return $item instanceof self ? $item : null;
    }
}
