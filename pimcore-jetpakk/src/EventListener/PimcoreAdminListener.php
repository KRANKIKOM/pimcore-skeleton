<?php

declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle\EventListener;

use Pimcore\Event\BundleManager\PathsEvent;

class PimcoreAdminListener
{
    public function addJSFiles(PathsEvent $event)
    {
        $event->setPaths(
            array_merge($event->getPaths(), ['/bundles/pimcorejetpakk/kk-editables/kk-editables.js'])
        );
    }

    public function addCSSFiles(PathsEvent $event)
    {
        $event->setPaths(
            array_merge($event->getPaths(), ['/bundles/pimcorejetpakk/kk-editables/kk-editables.css'])
        );
    }
}
