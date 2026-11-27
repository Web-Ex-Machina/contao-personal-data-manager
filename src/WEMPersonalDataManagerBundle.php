<?php

declare(strict_types=1);

/**
 * Personal Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-personal-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\PersonalDataManagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WEMPersonalDataManagerBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
