<?php

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Area\Info;

class JetpakkDownloads extends BaseAreabrick implements EditableDialogBoxInterface
{
    /**
     * @return string
     *
     * @psalm-return 'Downloads'
     */
    public function getName(): string
    {
        return 'Downloads';
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
                                    'label' => 'Downloads Darstellung',
                                    'config' => [
                                        'store' => [
                                            ['list', 'offene Auflistung'],
                                            ['accordion', 'einklappbare Sektionen'],
                                        ],
                                        'defaultValue' => 'list',
                                        'width' => 240,
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
