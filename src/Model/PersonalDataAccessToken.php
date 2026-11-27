<?php

declare(strict_types=1);

namespace WEM\PersonalDataManagerBundle\Model;

use WEM\UtilsBundle\Model\Model;

/**
 * @property int        $id
 * @property int|string $tstamp
 * @property string     $email
 * @property string     $token
 * @property int|string $expiresAt
 * @property int|string $createdAt
 */
class PersonalDataAccessToken extends Model
{
    public const int EXPIRE_DURATION = 300;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_wem_personal_data_access_token';

    /**
     * Check if an email + token couple is valid.
     *
     * @throws \Exception
     */
    public static function isEmailTokenCoupleValid(string $email, string $token): bool
    {
        $obj = static::findItems(['email' => $email, 'token' => $token], 1)?->current();

        return $obj instanceof self && $obj->isValid();
    }

    /**
     * Inserts a token for an email.
     */
    public static function insertForEmail(string $email): self
    {
        $obj = new self();
        $obj->email = $email;
        $obj->token = bin2hex(random_bytes(32));
        $obj->expiresAt = time() + self::EXPIRE_DURATION;
        $obj->createdAt = time();
        $obj->tstamp = time();
        $obj->save();

        return $obj;
    }

    public function updateExpiration(): self
    {
        $this->expiresAt = time() + self::EXPIRE_DURATION;
        $this->save();

        return $this;
    }

    public function isValid(): bool
    {
        return time() < (int) $this->expiresAt;
    }
}
