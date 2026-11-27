<?php

declare(strict_types=1);

namespace WEM\PersonalDataManagerBundle\Dca\Config\Callback;

use Contao\DataContainer;
use Contao\Model;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;

/**
 * "config.ondelete_callback" of a table managed by the Personal Data Manager.
 */
class Delete
{
    public function __construct(
        private readonly PersonalDataManager $personalDataManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(DataContainer $dc, int $undoId): void
    {
        if (!$dc->id || null === $record = $dc->getCurrentRecord()) {
            return;
        }

        $modelClassName = Model::getClassFromTable($dc->table);
        $model = new $modelClassName();

        $this->personalDataManager->deleteByPidAndPtableAndEmail(
            (int) $record[$model->getPersonalDataPidField()],
            $model->getPersonalDataPtable(),
            (string) $record[$model->getPersonalDataEmailField()]
        );
    }
}
