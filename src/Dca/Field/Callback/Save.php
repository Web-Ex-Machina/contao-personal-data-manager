<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Dca\Field\Callback;

/**
 * "save_callback" of a field managed by the Personal Data Manager.
 *
 * The value is returned as is: the model's "postSave" method (see PersonalDataTrait)
 * stores it in the Personal Data Manager.
 */
class Save
{
    public function __construct(
        private string $frontendField = '',
        private string $table = '',
    ) {
    }

    public function __invoke(mixed $value, mixed ...$args): mixed
    {
        return $value;
    }

    public function getFrontendField(): string
    {
        return $this->frontendField;
    }

    public function setFrontendField(string $frontendField): self
    {
        $this->frontendField = $frontendField;

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }
}
