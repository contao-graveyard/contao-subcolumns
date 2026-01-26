<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_settings']['fields']['subcolumns'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['subcolumns'],
    'inputType' => 'select',
    'options_callback' => ['HeimrichHannot\SubcolumnsBundle\tl_subcolumnsCallback', 'getSets'],
    'eval' => [
        'tl_class' => 'w50',
    ],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['subcolumns_gapdefault'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['subcolumns_gapdefault'],
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'w50',
    ],
];

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{subcolumns_legend:collapsed},subcolumns,subcolumns_gapdefault;';
