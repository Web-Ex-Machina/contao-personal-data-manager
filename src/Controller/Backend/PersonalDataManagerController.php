<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Controller\Backend;

use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManagerAction;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManagerUi;

#[Route(
    '%contao.backend.route_prefix%/wem-personal-data-manager',
    name: self::ROUTE,
    defaults: [
        '_scope' => 'backend',
    ],
    methods: ['GET', 'POST'],
)]
#[AsController]
class PersonalDataManagerController extends AbstractBackendController
{
    public const string ROUTE = 'wem_personal_data_manager_backend';

    public function __construct(
        private readonly PersonalDataManagerUi $ui,
        private readonly PersonalDataManagerAction $action,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, PersonalDataManagerAction::BACKEND_MODULE);

        if ($this->action->supports($request)) {
            return $this->action->processAjaxRequest($request);
        }

        $url = $this->generateUrl(self::ROUTE);
        $email = trim($request->request->getString('email'));

        $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/pdm.css';
        $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/pdm-modal.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/wempersonaldatamanager/js/pdm-modal.js';

        return $this->render('@Contao/backend/wem_personal_data_manager.html.twig', [
            'title' => $this->translator->trans('MOD.wem-personal-data-manager.0', [], 'contao_modules'),
            'headline' => $this->translator->trans('MOD.wem-personal-data-manager.0', [], 'contao_modules'),
            'action' => $url,
            'email' => $email,
            'request_token' => $this->csrfTokenManager->getDefaultTokenValue(),
            'content' => $email === '' ? '' : $this->ui->setUrl($url)->listForEmail($email),
        ]);
    }
}
