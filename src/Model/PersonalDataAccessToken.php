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

class PersonalDataAccessToken extends Model
{
    public const EXPIRE_DURATION = 300;
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_wem_personal_data_access_token';

    /**
     * Check if an email + token couple is valid.
     *
     * @param string $email The email
     * @param string $token The token
     *
     * @return bool True if valid
     */
    public static function isEmailTokenCoupleValid(string $email, string $token): bool
    {
        $obj = static::findItems(['email' => $email, 'token' => $token], 1);
        if (!$obj
        || !$obj->current()->isValid()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Inserts a row for an email.
     *
     * @param string $email The email
     */
    public static function insertForEmail(string $email): self
    {
        $obj = new self();
        $obj->email = $email;
        $obj->token = sha1($email.time().random_int(1, 1000));
        $obj->expiresAt = time() + (int) self::EXPIRE_DURATION;
        $obj->tstamp = time();
        $obj->save();

        return $obj;
    }

    public function updateExpiration(): self
    {
        $this->expiresAt = time() + (int) self::EXPIRE_DURATION;
        $this->save();

        return $this;
    }

    public function isValid(): bool
    {
        return time() < (int) $this->expiresAt;
    }
}
