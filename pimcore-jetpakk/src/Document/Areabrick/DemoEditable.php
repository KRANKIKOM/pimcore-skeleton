<?php

declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

final class DemoEditable extends BaseAreabrick
{
    public function getName(): string
    {
        return 'Demo';
    }

    public function getDescription(): string
    {
        return 'A demo brick';
    }
}
