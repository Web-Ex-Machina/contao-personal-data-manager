<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle\EventListener;

use Contao\CoreBundle\Event\MenuEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use WEM\PersonalDataManagerBundle\Controller\Backend\PersonalDataManagerController;
use WEM\PersonalDataManagerBundle\Service\PersonalDataManagerAction;

/**
 * Points the back end module menu item, registered in $GLOBALS['BE_MOD'], to the
 * back end controller route.
 */
#[AsEventListener]
class BackendMenuListener
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(MenuEvent $event): void
    {
        $tree = $event->getTree();

        if ($tree->getName() !== 'mainMenu') {
            return;
        }

        $node = $tree->getChild('wem_pdm')?->getChild(PersonalDataManagerAction::BACKEND_MODULE);

        if (! $node instanceof \Knp\Menu\ItemInterface) {
            return;
        }

        $node
            ->setUri($this->router->generate(PersonalDataManagerController::ROUTE))
            ->setCurrent($this->requestStack->getCurrentRequest()?->attributes->get('_route') === PersonalDataManagerController::ROUTE)
        ;
    }
}
