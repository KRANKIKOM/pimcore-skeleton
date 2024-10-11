<?php

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Area\Info;

class JetpakkPictureText extends BaseAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Picture Text';
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
                            'title' => 'Aussehen & Verhalten',
                            'items' => [
                                [
                                    'type' => 'select',
                                    'name' => 'picturePosition',
                                    'label' => 'Bildposition',
                                    'config' => [
                                        'store' => [
                                            ['picture-left', 'links'],
                                            ['picture-right', 'rechts']],
                                        'defaultValue' => '',
                                    ],
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'pictureAspectRatio',
                                    'label' => 'Seitenverhältnis Bild',
                                    'config' => [
                                        'store' => [
                                            ['fixed', 'quadratisch'],
                                            ['responsive', 'ausgerichtet am Text'],
                                        ],
                                        'defaultValue' => 'fixed',
                                    ],
                                ],
                            ],
                        ],
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