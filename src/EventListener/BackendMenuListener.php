<?php

declare(strict_types=1);

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

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $node = $tree->getChild('wem_pdm')?->getChild(PersonalDataManagerAction::BACKEND_MODULE);

        if (null === $node) {
            return;
        }

        $node
            ->setUri($this->router->generate(PersonalDataManagerController::ROUTE))
            ->setCurrent(PersonalDataManagerController::ROUTE === $this->requestStack->getCurrentRequest()?->attributes->get('_route'))
        ;
    }
}
