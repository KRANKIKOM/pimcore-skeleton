<?php

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Area\Info;

class JetpakkUsps extends BaseAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Unique Selling Points';
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
                                static::shared['columns_3_4'],
                                [
                                    'type' => 'select',
                                    'name' => 'variant',
                                    'label' => 'Variante',
                                    'config' => [
                                        'store' => [['icon', 'Icon'], ['count', 'Hochzählung']],
                                        'defaultValue' => 'icon',
                                    ],
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'alignment',
                                    'label' => 'Ausrichtung',
                                    'config' => [
                                        'store' => [['left', 'linksbündig'], ['center', 'mittig']],
                                        'defaultValue' => 'left',
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
