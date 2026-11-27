<?php

declare(strict_types=1);

namespace WEM\PersonalDataManagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WEMPersonalDataManagerBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
