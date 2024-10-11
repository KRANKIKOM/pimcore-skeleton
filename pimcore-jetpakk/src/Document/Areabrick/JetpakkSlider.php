<?php

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Area\Info;

class JetpakkSlider extends BaseAreabrick implements EditableDialogBoxInterface
{
    public function getName(): string
    {
        return 'Slider';
    }

    /**
     * @return EditableDialogBoxConfiguration
     */
    public function getEditableDialogBoxConfiguration(
        Editable $area,
        ?Info $info
    ): EditableDialogBoxConfiguration {
        function swiper_config($mod = '')
        {
            return [
                [
                    'type' => 'fieldset',
                    'title' => 'Slide-Einstellungen',
                    'items' => [
                        [
                            'type' => 'numeric',
                            'name' => 'slidesPerGroup' . $mod,
                            'label' => 'Slides pro Gruppe',
                            'config' => [
                                'defaultValue' => '1',
                                'maxValue' => '6',
                                'minValue' => '1',
                            ],
                        ],
                        [
                            'type' => 'numeric',
                            'name' => 'slidesPerView' . $mod,
                            'label' => 'Slides pro Ansicht',
                            'config' => [
                                'defaultValue' => '1',
                                'decimalPrecision' => '2',
                                'maxValue' => '6',
                                'minValue' => '1',
                            ],
                        ],
                        [
                            'type' => 'numeric',
                            'name' => 'spaceBetween' . $mod,
                            'label' => 'Abstand zwischen Slides',
                            'config' => [
                                'defaultValue' => '24',
                                'minValue' => '0',
                            ],
                        ],
                        [
                            'type' => 'select',
                            'name' => 'centerInsufficientSlides' . $mod,
                            'label' => 'Unzureichende Menge an Slides zentrieren',
                            'config' => [
                                'store' => [[0, 'nein'], [1, 'ja']],
                                'defaultValue' => 0,
                            ],
                        ],
                    ],
                ],
            ];
        }

        $breakpoints = [
            'type' => 'tabpanel',
            'items' => [
                [
                    'type' => 'panel',
                    'title' => 'ab Mobil aufwärts',
                    'items' => [...swiper_config()],
                ],
                [
                    'type' => 'panel',
                    'title' => 'ab Tablet aufwärts',
                    'items' => [
                        [
                            'type' => 'select',
                            'name' => 'slide_settings_md',
                            'label' => 'Ab Tablet aufwärts diese Einstellungen verwenden',
                            'config' => [
                                'store' => [[0, 'nein'], [1, 'ja']],
                                'defaultValue' => 0,
                            ],
                        ],
                        ...swiper_config('_md'),
                    ],
                ],
                [
                    'type' => 'panel',
                    'title' => 'ab Desktop aufwärts',
                    'items' => [
                        [
                            'type' => 'select',
                            'name' => 'slide_settings_xl',
                            'label' => 'Ab Desktop aufwärts diese Einstellungen verwenden',
                            'config' => [
                                'store' => [[0, 'nein'], [1, 'ja']],
                                'defaultValue' => 0,
                            ],
                        ],
                        ...swiper_config('_xl'),
                    ],
                ],
            ],
        ];

        return (new EditableDialogBoxConfiguration())
            ->setItems([
                [
                    'type' => 'tabpanel',
                    'items' => [
                        [
                            'type' => 'panel',
                            'title' => 'Slider Einstellungen',
                            'items' => [
                                [
                                    'type' => 'fieldset',
                                    'title' => 'Autoplay und Geschwindigkeiten',
                                    'items' => [
                                        [
                                            'type' => 'select',
                                            'name' => 'speed',
                                            'label' => 'Bewegungsgeschwindigkeit',
                                            'config' => [
                                                'store' => [
                                                    ['150', 'schneller (0.15s)'],
                                                    ['300', 'regulär (0.3s)'],
                                                    ['450', 'langsam (0.45s)'],
                                                    ['800', 'langsamer (0.8s)'],
                                                ],
                                                'defaultValue' => '300',
                                            ],
                                        ],
                                        [
                                            'type' => 'select',
                                            'name' => 'autoplay',
                                            'label' => 'Autoplay aktivieren',
                                            'config' => [
                                                'store' => [[0, 'nein'], [1, 'ja']],
                                                'defaultValue' => 0,
                                            ],
                                        ],
                                        [
                                            'type' => 'numeric',
                                            'name' => 'autoplay_delay',
                                            'label' =>
                                                'Autoplay Slidewechsel Verzögerung (Sekunden)',
                                            'config' => [
                                                'defaultValue' => '5',
                                                'decimalPrecision' => '1',
                                                'stepValue' => '0.1',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'fieldset',
                                    'title' => 'Steuerung und Visuelle hilfen',
                                    'items' => [
                                        [
                                            'type' => 'select',
                                            'name' => 'navigation',
                                            'label' => 'Navigationselemente anzeigen',
                                            'config' => [
                                                'store' => [[0, 'nein'], [1, 'ja']],
                                                'defaultValue' => 0,
                                            ],
                                        ],
                                        [
                                            'type' => 'select',
                                            'name' => 'visual_cue',
                                            'label' => 'Visueller Slide-Fortschritt',
                                            'config' => [
                                                'store' => [
                                                    ['pagination', 'Paginierung'],
                                                    ['scrollbar', 'Scrollbar'],
                                                    [0, 'Keiner'],
                                                ],
                                                'defaultValue' => 'pagination',
                                            ],
                                        ],
                                    ],
                                ],
                                $breakpoints,
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
