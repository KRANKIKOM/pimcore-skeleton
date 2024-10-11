<?php

declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class PimcoreJetpakkBundle extends AbstractPimcoreBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
