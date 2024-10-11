<?php

declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Editable\Area\Info;

abstract class BaseAreabrick extends AbstractTemplateAreabrick
{
    public const noOverride = [0, 'Nicht überschreiben'];
    public const shared = [
        'picture_position' => [
            'type' => 'select',
            'name' => 'picture_position',
            'label' => 'Bildposition',
            'config' => [
                'store' => [['left', 'Bild links'], ['right', 'Bild rechts']],
                'defaultValue' => 'left',
            ],
        ],
        'el_spacing_bottom' => [
            'type' => 'select',
            'name' => 'el_spacing_bottom',
            'label' => 'Elementabstand nach unten',
            'config' => [
                'store' => [
                    ['mb-0', 'none'],
                    ['mb-el-sm', 'sm'],
                    ['mb-el', 'md (Standard)'],
                    ['mb-el-lg', 'lg'],
                ],
                'defaultValue' => 'mb-el',
            ],
        ],
        'columns_3_4' => [
            'type' => 'select',
            'name' => 'columns_3_4',
            'label' => 'Spaltenaufteilung',
            'config' => [
                'store' => [['3', '3-spaltig'], ['4', '4-spaltig']],
                'defaultValue' => '4',
            ],
        ],
        'el_section_id' => [
            'type' => 'input',
            'name' => 'el_section_id',
            'label' => 'Anchor ID',
            'config' => [
                'defaultValue' => '',
            ],
        ],
    ];

    public const SHARED_COMMON = [self::shared['el_spacing_bottom'], self::shared['el_section_id']];

    public function getTemplateLocation(): string
    {
        return static::TEMPLATE_LOCATION_BUNDLE;
    }
}
