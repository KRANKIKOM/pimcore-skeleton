<?php

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Area\Info;

class JetpakkKeyvisual extends BaseAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Keyvisual';
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
                                    'name' => 'variant',
                                    'label' => 'Variante',
                                    'config' => [
                                        'store' => [['large', 'groß'], ['small', 'klein']],
                                        'defaultValue' => 'large',
                                    ],
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'textcolor',
                                    'label' => 'Textfarbe auf Media',
                                    'config' => [
                                        'store' => [['light', 'hell'], ['dark', 'dunkel']],
                                        'defaultValue' => 'light',
                                    ],
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'backdrop',
                                    'label' => 'Media dimmen?',
                                    'config' => [
                                        'store' => [[0, 'Nein'], [1, 'Ja']],
                                        'defaultValue' => 0,
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
