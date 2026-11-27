<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Dca\Config\Callback;

use Contao\DataContainer;
use Contao\Model;

/**
 * "config.onsubmit_callback" of a table managed by the Personal Data Manager, and
 * "updatePersonalData" hook in the front end.
 */
class Submit
{
    public function __invoke(mixed ...$args): void
    {
        // In the front end ("updatePersonalData" hook), the model has already been
        // updated when the hook is called: nothing to do.
        if (($args[0] ?? null) instanceof DataContainer) {
            $this->invokeBackend($args[0]);
        }
    }

    protected function invokeBackend(DataContainer $dc): void
    {
        if (! $dc->id || null === $record = $dc->getCurrentRecord()) {
            return;
        }

        $modelClassName = Model::getClassFromTable($dc->table);
        $model = $modelClassName::findByPk($dc->id);

        if ($model === null) {
            return;
        }

        foreach ($record as $key => $value) {
            if (! $model->isFieldInPersonalDataFieldsNames($key)
                || $value === $model->getPersonalDataFieldsDefaultValueForField($key)
            ) {
                continue;
            }

            $model->{$key} = $value;
            $model->markModified($key);
        }

        $model->save();
    }
}
