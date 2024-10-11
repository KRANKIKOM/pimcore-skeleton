<?php

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Area\Info;

class JetpakkFullscreenMedia extends BaseAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Fullscreen Media';
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
                                    'name' => 'textposition',
                                    'label' => 'Textposition',
                                    'config' => [
                                        'store' => [
                                            ['justify-content-start', 'links'],
                                            ['justify-content-end', 'rechts'],
                                        ],
                                        'defaultValue' => 'justify-content-start',
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
