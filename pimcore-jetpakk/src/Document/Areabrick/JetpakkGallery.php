<?php

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Area\Info;

class JetpakkGallery extends BaseAreabrick implements EditableDialogBoxInterface
{
    /**
     * @return string
     *
     * @psalm-return 'Gallery'
     */
    public function getName(): string
    {
        return 'Bildergalerie';
    }

    /**
     * @return EditableDialogBoxConfiguration
     */
    public function getEditableDialogBoxConfiguration(
        Editable $area,
        ?Info $info
    ): EditableDialogBoxConfiguration {
        return (new EditableDialogBoxConfiguration())
            ->setItems([
                [
                    'type' => 'tabpanel',
                    'items' => [
                        [
                            'type' => 'panel',
                            'title' => 'Weitere Einstellungen',
                            'items' => [...static::SHARED_COMMON],
                        ],
                    ],
                ],
            ])

            ->setHeight(500)
            ->setWidth(600)
            ->setReloadOnClose(true);
    }
}
