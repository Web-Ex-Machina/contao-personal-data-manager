<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\Controller\FrontendModule;

use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Validator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManager;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManagerAction;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManagerUi;

/**
 * Allows visitors to manage their personal data: an access link is sent by email,
 * then the personal data linked to this email are displayed.
 */
#[AsFrontendModule(self::TYPE, category: 'wem-personal-data-manager', template: 'frontend_module/wem_personal_data_manager')]
class PersonalDataManagerController extends AbstractFrontendModuleController
{
    public const string TYPE = 'wem_personaldatamanager';

    public const string FORM_ID = 'wem-personal-data-manager';

    public const string TOKEN_PARAMETER = 'pdm_token';

    public function __construct(
        private readonly PersonalDataManager $manager,
        private readonly PersonalDataManagerUi $ui,
        private readonly PersonalDataManagerAction $action,
        private readonly MailerInterface $mailer,
        private readonly ContentUrlGenerator $contentUrlGenerator,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        if ($this->action->supports($request)) {
            throw new ResponseException($this->action->processAjaxRequest($request));
        }

        $messages = [];
        $email = (string) $this->manager->getEmailInSession();
        $token = $request->query->getString(self::TOKEN_PARAMETER);

        if ($request->request->get('FORM_SUBMIT') === self::FORM_ID) {
            $email = trim($request->request->getString('email'));
            $messages[] = $this->sendAccessLink($email);
        } elseif ($token !== '') {
            if ($email !== '' && $this->manager->isEmailTokenCoupleValid($email, $token)) {
                $this->manager->putTokenInSession($token);
                $this->manager->updateTokenExpiration($token);

                $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/pdm.css';
                $GLOBALS['TL_CSS'][] = 'bundles/wempersonaldatamanager/css/pdm-modal.css';
                $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/wempersonaldatamanager/js/pdm-modal.js';

                $template->set('content', $this->ui->setUrl($request->getRequestUri())->listForEmail($email));
            } else {
                $messages[] = [
                    'type' => 'error',
                    'text' => $this->trans('WEM.PEDAMA.MODFE.emailTokenCoupleNotValid'),
                ];
            }
        }

        $template->set('messages', $messages);
        $template->set('searchable', false);
        $template->set('action', $request->getRequestUri());
        $template->set('email', $email);
        $template->set('request_token', $this->csrfTokenManager->getDefaultTokenValue());

        return $template->getResponse();
    }

    /**
     * @return array{type: string, text: string}
     */
    private function sendAccessLink(string $email): array
    {
        $page = $this->getPageModel();

        if (! $page instanceof PageModel || ! Validator::isEmail($email)) {
            return [
                'type' => 'error',
                'text' => $this->trans('WEM.PEDAMA.MODFE.emailNotSent'),
            ];
        }

        $this->manager->putEmailInSession($email);
        $this->manager->clearTokenInSession();

        $accessToken = $this->manager->insertForEmail($email);

        $url = $this->contentUrlGenerator->generate(
            $page,
            [
                self::TOKEN_PARAMETER => $accessToken->token,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        try {
            $message = new Email()
                ->to($email)
                ->subject($this->trans('WEM.PEDAMA.EMAIL.subject'))
                ->html($this->renderView('@Contao/personal_data_manager/email.html.twig', [
                    'url' => $url,
                ]))
            ;

            if (($sender = $this->getSender($page)) instanceof \Symfony\Component\Mime\Address) {
                $message->from($sender);
            }

            $this->mailer->send($message);
        } catch (\Throwable $throwable) {
            $this->logger?->error('Unable to send the personal data access link: ' . $throwable->getMessage(), [
                'exception' => $throwable,
            ]);

            return [
                'type' => 'error',
                'text' => $this->trans('WEM.PEDAMA.MODFE.emailNotSent'),
            ];
        }

        return [
            'type' => 'confirm',
            'text' => $this->trans('WEM.PEDAMA.MODFE.emailSent'),
        ];
    }

    private function getSender(PageModel $page): Address|null
    {
        $page->loadDetails();

        $adminEmail = $page->adminEmail ?: Config::get('adminEmail');

        if (! $adminEmail) {
            return null;
        }

        [$name, $address] = StringUtil::splitFriendlyEmail($adminEmail);

        return new Address($address, (string) $name);
    }

    private function trans(string $id): string
    {
        return $this->translator->trans($id, [], 'contao_default');
    }
}
